<?php
session_start();
include 'db.php';

// Búsqueda y filtro
$busqueda = $_GET['buscar'] ?? '';
$categoria = $_GET['categoria'] ?? '';

$query = "SELECT * FROM ventasbasica_productos WHERE estatus='activo'";
$params = [];
if ($busqueda) {
    $query .= " AND nombre LIKE ?";
    $params[] = "%$busqueda%";
}
if ($categoria) {
    $query .= " AND categoria_id=?";
    $params[] = $categoria;
}
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$result = $stmt;

$categorias = $pdo->query("SELECT * FROM ventasbasica_categorias where activo = '1'");

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

// =======================
// AGREGAR AL CARRITO
// =======================
// =======================
// AGREGAR AL CARRITO (sin salir del catálogo)
// =======================
if (isset($_POST['agregar'])) {
    $id = (int)$_POST['id'];
    $color_id = (int)$_POST['color'];
    $cant = (int)$_POST['cantidad'];
    $talla = $_POST['talla'];

    if (!$color_id) {
        echo "<script>alert('Por favor Color seleccionado.');</script>";
    } else {
        $stmt_p = $pdo->prepare("
            SELECT p.*, c.nombre AS categoria
            FROM ventasbasica_productos p
            LEFT JOIN ventasbasica_categorias c ON p.categoria_id = c.id
            WHERE p.id = ?
        ");
        $stmt_p->execute([$id]);
        $p = $stmt_p->fetch();

        $stmt_color = $pdo->prepare("
            SELECT c.nombre, c.hex, pc.cantidad 
            FROM ventasbasica_producto_colores pc
            JOIN ventasbasica_colores c ON pc.color_id=c.id
            WHERE pc.id=?
        ");
        $stmt_color->execute([$color_id]);
        $color = $stmt_color->fetch();

        if ($p && $color) {
            if ($cant > $color['cantidad']) $cant = $color['cantidad'];

            $_SESSION['carrito'][$color_id] = [
                'id' => $id,
                'nombre' => $p['nombre'],
                'precio' => $p['precio'],
                'cantidad' => $cant,
                'color' => $color['nombre'],
                'hex' => $color['hex'],
                'talla' => $talla,
                'categoria_id' => $p['categoria_id'],
                'categoria' => $p['categoria']
            ];

            $_SESSION['toast'] = "✅ Producto agregado al pedido";

            echo "<script>
            window.location='index.php#productos';
            </script>";

            
        } else {
            echo "<script>alert('Error al agregar producto.');</script>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wow Amy Gt</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="shortcut icon" href="img/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>



<style>
/*=========================
RESET
==========================*/

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    scroll-behavior:smooth;
}

body{
    font-family:'Poppins',sans-serif;
    background:#fff7fb;
    color:#444;
    overflow-x:hidden;
}

/*=========================
FONDO
==========================*/

body::before{
    content:"";
    position:fixed;
    inset:0;
    background:
        radial-gradient(circle at 0% 0%, #ff9fcf 0%, transparent 35%),
        radial-gradient(circle at 100% 20%, #ff7ab8 0%, transparent 28%),
        radial-gradient(circle at 90% 100%, #ffc6df 0%, transparent 35%),
        radial-gradient(circle at 10% 90%, #ffd8ea 0%, transparent 30%);
    z-index:-2;
}

/*=========================
HEADER Y MENÚ HAMBURGUESA
==========================*/

header{
    position:fixed;
    top:0;
    width:100%;
    z-index:1000;
    background:rgba(255,255,255,.85);
    backdrop-filter:blur(12px);
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

nav{
    width:90%;
    max-width:1200px;
    margin:auto;
    display:flex;
    justify-content:space-between;
    align-items:center;
	/* tenia 18px  */
    padding:5px 0;
}

.logo img{
    height:45px;
}

/* El checkbox oculto que controlará el menú */
#menu-toggle {
    display: none;
}

/* El botón de hamburguesa (oculto en escritorio) */
.burger-menu {
    display: none;
    flex-direction: column;
    gap: 5px;
    cursor: pointer;
    z-index: 1001;
}

.burger-menu span {
    width: 28px;
    height: 3px;
    background-color: #ff4f98;
    border-radius: 2px;
    transition: 0.3s;
}

nav ul{
    display:flex;
    gap:35px;
    list-style:none;
}

nav a{
    text-decoration:none;
    color:#ff4f98;
    font-weight:600;
    transition:.3s;
}

nav a:hover{
    color:#c2185b;
}

/*=========================
SECCIONES
==========================*/

section{
    /* tenia 110  */
    padding:100px 8%;
}

section h2{
    text-align:center;
    font-size:42px;
    color:#ff4f98;
    margin-bottom:15px;
}

section p{
    line-height:1.8;
}

/*=========================
HERO
==========================*/

#inicio{
    min-height:85vh;
    display:flex;
    align-items:center;
    padding-top:120px;
}

.hero-contenedor{

    width:100%;
    max-width:1200px;
    margin:auto;

    display:grid;
    grid-template-columns:1fr 1fr;

    gap:50px;
    align-items:center;

}
.hero-texto{
    padding-left:20px;
}

.hero-texto h1{
    font-size:65px;
    color:#ff4f98;
    line-height:1.1;
    margin-bottom:20px;
}

.hero-texto p{
    font-size:19px;
    margin-bottom:35px;
}

.hero-texto a{
    display:inline-block;
    background:#ff4f98;
    color:white;
    padding:16px 38px;
    border-radius:50px;
    text-decoration:none;
    transition:.3s;
    font-weight:600;
}

.hero-texto a:hover{
    transform:translateY(-4px);
    background:#e13d84;
}

.hero-imagen{
    text-align:center;
}

.hero-imagen img{
    width:100%;
    max-width:480px;
}

/*=========================
PRODUCTOS
==========================*/

#productos{
    background:white;
    border-radius:40px;
    margin:40px auto;
    width:92%;
    box-shadow:0 20px 60px rgba(0,0,0,.08);
}

.titulo{
    margin-bottom:60px;
}

.titulo p{
    text-align:center;
    color:#777;
}

.productos-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:30px;
}

.producto{
    background:#fff;
    border-radius:25px;
    overflow:hidden;
    transition:.35s;
    box-shadow:0 12px 25px rgba(0,0,0,.08);
}

.producto:hover{
    transform:translateY(-10px);
}

.producto img{
    width:100%;
    height:270px;
    object-fit:cover;
}

.producto h3{
    padding:18px 20px 10px;
    color:#ff4f98;
}

.producto p{
    padding:0 20px;
}

.producto a{
    display:block;
    margin:20px;
    text-align:center;
    padding:14px;
    border-radius:40px;
    text-decoration:none;
    background:#ff4f98;
    color:white;
    transition:.3s;
}

.producto a:hover{
    background:#d9327d;
}

/*=========================
NOSOTROS
==========================*/

#nosotros {
    padding: 60px 8%;
}

.nosotros-contenedor{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:60px;
    align-items:center;
}

.nosotros-imagen img{
    /* estaba en 100% */
    width:70%;
    border-radius:30px;
}

.nosotros-texto h2{
    text-align:left;
}

.nosotros-texto ul{
    margin-top:25px;
}

.nosotros-texto li{
    margin-bottom:15px;
    list-style:none;
}

/*=========================
CONTACTO (CON REDES SOCIALES)
==========================*/

#contacto{
    background:white;
    border-radius:40px;
    width:92%;
    margin:auto;
    margin-bottom:60px;
    box-shadow:0 20px 60px rgba(0,0,0,.08);
}

.contacto-contenedor{
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap:50px;
    align-items: center;
}

.informacion h2{
    text-align:left;
    margin-bottom:20px;
}

.informacion p{
    margin-bottom:18px;
    font-size: 16px;
}

.redes-contacto {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.redes-contacto a {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: white;
    background: #ff4f98;
    padding: 14px 28px;
    border-radius: 30px;
    font-weight: 600;
    transition: 0.3s;
    text-align: center;
    justify-content: center;
    width: fit-content;
}

.redes-contacto a:hover {
    background: #d9327d;
    transform: translateY(-3px);
}

/*=========================
FOOTER CORREGIDO
==========================*/

footer{
    background:#ff4f98;
    color:white;
    padding:60px 8% 30px;
}

.footer-contenedor {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
}

.footer-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

footer img{
    height:60px;
    margin-bottom:10px;
}

.footer-links {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

footer a{
    color:white;
    text-decoration:none;
    font-weight: 500;
    transition: 0.3s;
}

footer a:hover {
    opacity: 0.8;
}

.footer-redes {
    display: flex;
    gap: 20px;
}

.footer-redes a {
    background: rgba(255, 255, 255, 0.2);
    padding: 10px 18px;
    border-radius: 20px;
    font-size: 14px;
}

.footer-redes a:hover {
    background: white;
    color: #ff4f98;
}

footer hr{
    margin:40px 0 20px;
    border:none;
    border-top:1px solid rgba(255,255,255,.3);
}

.copyright {
    text-align: center;
    font-size: 14px;
    opacity: 0.9;
}

/*=========================
RESPONSIVE INTERMEDIO (TABLETAS)
==========================*/

@media(max-width:992px){

    .hero-contenedor,
    .nosotros-contenedor,
    .contacto-contenedor{
        grid-template-columns:1fr;
        text-align:center;
    }

    .nosotros-texto h2,
    .informacion h2{
        text-align:center;
    }
    
    .redes-contacto {
        align-items: center;
    }

    .productos-grid{
        grid-template-columns:repeat(2,1fr);
    }

    .hero-texto h1{
        font-size:48px;
    }

    .footer-contenedor {
        grid-template-columns: 1fr;
        gap: 35px;
    }
}

/*=========================
RESPONSIVE MÓVIL (MENÚ HAMBURGUESA ACTIVADO)
==========================*/

@media(max-width:768px){

    /* Activación Visual del Botón de Hamburguesa */
    .burger-menu {
        display: flex;
    }

    /* Transformación del menú tradicional a menú desplegable lateral */
    nav ul {
        position: fixed;
        top: 96px; /* Altura aproximada del header */
        right: -100%;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: -10px 15px 30px rgba(0,0,0,0.05);
        width: 75%;
        height: calc(100vh - 96px);
        flex-direction: column;
        justify-content: flex-start;
        padding: 50px 30px;
        gap: 30px;
        transition: 0.4s ease-in-out;
    }

    nav ul li a {
        font-size: 20px;
        display: block;
    }

    /* Interacción con CSS Puro: Cuando el checkbox está seleccionado, muestra el menú */
    #menu-toggle:checked ~ ul {
        right: 0;
    }

    /* Animación del icono de hamburguesa a una 'X' al abrir */
    #menu-toggle:checked ~ .burger-menu span:nth-child(1) {
        transform: rotate(45deg) translate(6px, 6px);
    }

    #menu-toggle:checked ~ .burger-menu span:nth-child(2) {
        opacity: 0;
    }

    #menu-toggle:checked ~ .burger-menu span:nth-child(3) {
        transform: rotate(-45deg) translate(5px, -5px);
    }

    .hero-texto h1{
        font-size:38px;
    }

    section h2{
        font-size:34px;
    }

    .productos-grid{
        grid-template-columns:1fr;
    }

    .hero-imagen img{
        max-width:320px;
    }

     .hero-texto h1{
        font-size:38px;
    }

    section h2{
        font-size:34px;
    }

    .productos-grid{
        grid-template-columns:1fr;
    }

    .hero-imagen img{
        max-width:320px;
    }

    .hero-texto{
    padding-left:0;
}

.hero-texto h1{
    font-size:50px;
}

.hero-texto h3{
    font-size:28px;
}

.hero-texto p{
    font-size:16px;
}

.hero-imagen img{
    max-width:150px;
}


}

/*=========================
CARRUSEL / CARRETE INFO
==========================*/

.carrete-info{
    width:92%;
    margin:30px auto 0;
    overflow:hidden;
    background: linear-gradient(90deg, #ff4f98, #ff7ab8, #ffc6df);
    border-radius:30px;
    box-shadow:0 10px 25px rgba(0,0,0,.1);
    padding:12px 0;
    position:relative;
}

.carrete-track{
    display:flex;
    width:max-content;
    animation: scrollLeft 18s linear infinite;
}

.item{
    white-space:nowrap;
    color:white;
    font-weight:600;
    font-size:15px;
    padding:0 40px;
    display:flex;
    align-items:center;
    gap:10px;
}

/* Animación movimiento */
@keyframes scrollLeft{
    0%{
        transform:translateX(0);
    }
    100%{
        transform:translateX(-50%);
    }
}

/* PAUSA AL PASAR EL MOUSE (opcional pro UX) */
.carrete-info:hover .carrete-track{
    animation-play-state:paused;
}

/* RESPONSIVE */
@media(max-width:768px){
    .item{
        font-size:13px;
        padding:0 25px;
    }
}


.redes-contacto {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.redes-contacto a {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    color: #fff;
    transition: 0.3s;
}

/* Colores por red */
.red.facebook {
    background: #1877f2;
}

.red.instagram {
    background: linear-gradient(45deg, #f9ce34, #ee2a7b, #6228d7);
}

.red.tiktok {
    background: #111;
}

.redes-contacto a:hover {
    transform: translateY(-3px);
    opacity: 0.9;
}

</style>



<style>
.color-cuadro{
    display:inline-block;
    /* 
        width:50px;
    height:25px;
    */
    width:90px;
    height:50px;
    border-radius:3px;
    margin-right:6px;
    vertical-align:middle;
    border:1px solid #ccc;
}

/*------------------------------
DISEÑO RESPONSIVE
-------------------------------*/

/* La imagen siempre ocupa bien la card */
.card .img-producto{
    width:100%;
    height:180px;
    object-fit:contain;
    cursor:pointer;
}

/* Mejor separación */
.card{
    border:none;
    border-radius:15px;
}

.card-body{
    padding:15px;
}

/* ======== CELULARES ======== */

@media (max-width:768px){

    h2{
        font-size:1.5rem;
    }

    /* El formulario pasa de horizontal a vertical */
    form.d-flex{
        flex-direction:column;
        gap:10px;
    }

    form.d-flex .form-control,
    form.d-flex .form-select,
    form.d-flex .btn{
        width:100%;
        max-width:100%!important;
        margin:0!important;
    }

    /* El botón carrito ocupa todo el ancho */
    .btn-success.mb-3{
        width:100%;
        font-size:17px;
    }

    /* Una card por fila */
    .row>.col-md-3{
        width:100%;
    }

    /* Imagen un poco más pequeña */
    .card .img-producto{
        height:170px;
    }

    .card-title{
        font-size:1.05rem;
    }

    .card b{
        font-size:1.1rem;
    }

    .form-select,
    .form-control{
        font-size:16px;
    }

    .btn{
        font-size:16px;
    }

       #inicio {
        min-height: 50vh;
    }

}

/* Tablets */

@media (min-width:769px) and (max-width:991px){

    .row>.col-md-3{
        width:50%;
    }

}

/* efecto al boton carrito  */

/*==========================
BOTÓN CARRITO CON EFECTO PULSE
===========================*/

.btn-pulse{
    animation:pulse 1.5s infinite;
}

@keyframes pulse{

    0%{
        transform:scale(1);
        box-shadow:0 0 0 0 rgba(25,135,84,.7);
    }

    70%{
        transform:scale(1.08);
        box-shadow:0 0 0 18px rgba(25,135,84,0);
    }

    100%{
        transform:scale(1);
        box-shadow:0 0 0 0 rgba(25,135,84,0);
    }

}


/* pooooopuuuuuuuuuuuuuup */
/*=========================
  POPUP RESPONSIVO Y AJUSTADO
==========================*/
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 15px; /* Margen de seguridad para pantallas pequeñas */
}

.popup-content {
    position: relative;
    width: 100%;
    max-width: 420px; /* Tamaño máximo responsivo controlado */
    max-height: 85vh; /* Evita que sobrepase la pantalla verticalmente */
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(255, 79, 152, 0.35);
    display: flex;
    justify-content: center;
    align-items: center;
}

.popup-image {
    width: 100%;
    height: auto;
    max-height: 85vh;
    object-fit: contain; /* Ajusta la imagen completa sin recortarla */
    display: block;
}

.popup-close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.95);
    color: #ff4f98;
    border: none;
    font-size: 24px;
    font-weight: bold;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    line-height: 1;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    transition: 0.2s;
    z-index: 10;
}

.popup-close:hover {
    background: #ff4f98;
    color: #fff;
    transform: scale(1.08);
}

</style>

<body>

    <header>
        <nav>
            <div class="logo">
              <a href="index.php">
                <img src="img/logo.png" alt="Logo">
            </a>
            </div>
            
            <input type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="burger-menu">
                <span></span>
                <span></span>
                <span></span>
            </label>

            <ul>
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#productos">Productos</a></li>
				<li><a href="#nosotros">Nosotros</a></li>
                <li><a href="#contacto">Contacto</a></li>
                <li><a href="#envios">Envíos</a></li>
            </ul>
        </nav>
    </header>
	
	

<section id="inicio">

    <div class="hero-contenedor">


        <div class="hero-texto">

            <span class="marca">
                ✨ Moda & Estilo 
            </span>

            <h1>
                WowAmy Gt
            </h1>

            <h3>
                Lo que buscas está aquí
            </h3>

            <p>
                Felicidad es cuando compras tu ropa favorita.
            </p>

            <a href="#productos">
                Ver productos
            </a>

        </div>



        <div class="hero-imagen">

            <img src="img/logo2.png" alt="Lina GT">

        </div>


    </div>

</section>
	
			<!-- CARRUSEL INFO -->
<!-- CARRUSEL INFO -->
<div class="carrete-info">
    <div class="carrete-track">
        
        <div class="item"><i class="fa-solid fa-shirt"></i> Prendas a la moda</div>
        <div class="item"><i class="fa-solid fa-truck"></i> Envíos a todo Guatemala</div>
        <div class="item"><i class="fa-solid fa-wand-magic-sparkles"></i> Outfits increíbles</div>
        <div class="item"><i class="fa-solid fa-heart"></i> Combina estilos y colores</div>
        <div class="item"><i class="fa-solid fa-comments"></i> Consulta nuestros estilos</div>

        <!-- DUPLICADO (IMPORTANTE para loop infinito) -->
        <div class="item"><i class="fa-solid fa-shirt"></i> Prendas a la moda</div> 
        <div class="item"><i class="fa-solid fa-truck"></i> Envíos a todo Guatemala</div>
        <div class="item"><i class="fa-solid fa-wand-magic-sparkles"></i> Outfits increíbles</div>
        <div class="item"><i class="fa-solid fa-heart"></i> Combina estilos y colores</div>
        <div class="item"><i class="fa-solid fa-comments"></i> Consulta nuestros estilos</div>

    </div>
</div>



<style>
/*=====================================
   TARJETAS EXCLUSIVAS DEL CATÁLOGO
======================================*/

.card-producto{
    background:linear-gradient(180deg,#fffafb 0%,#fff1f7 100%);
    border:1px solid #ffd6e7;
    border-radius:22px;
    overflow:hidden;
    position:relative;
    transition:.35s;
    box-shadow:0 10px 25px rgba(255,79,152,.12);
}

.card-producto:hover{
    transform:translateY(-8px);
    box-shadow:0 20px 40px rgba(255,79,152,.22);
}

.card-producto::before{
    content:"";
    position:absolute;
    top:0;
    left:-120%;
    width:70%;
    height:100%;
    background:linear-gradient(
        120deg,
        transparent,
        rgba(255,255,255,.55),
        transparent
    );
    transition:.7s;
}

.card-producto:hover::before{
    left:150%;
}

.imagen-producto{
    height:180px;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(#fff8fb,#ffeef6);
    overflow:hidden;
    cursor:pointer;
}

.card-producto img{
    transition:.35s;
}

.card-producto:hover img{
    transform:scale(1.06);
}

.card-producto .card-title{
    color:#e91e63;
    font-weight:700;
}

.btn-producto{
    background:linear-gradient(45deg,#ff4f98,#ff76b7);
    border:none;
    border-radius:40px;
    font-weight:600;
    transition:.3s;
}

.btn-producto:hover{
    background:linear-gradient(45deg,#ff2f86,#ff5da8);
    transform:scale(1.03);
}

</style>	
	

    <section id="productos">
    
    <div class="container mt-4">
<h2 class="text-center mb-4">🛍️ Catálogo de productos</h2>

<form id="formFiltro" class="row g-2 mb-3" method="GET">

    <div class="col-md-6">
        <input
            type="text"
            name="buscar"
            id="buscar"
            placeholder="Buscar..."
            class="form-control"
            value="<?= htmlspecialchars($busqueda) ?>">
    </div>

    <div class="col-md-4">
        <select
            name="categoria"
            id="categoria"
            class="form-select">

            <option value="">Todas las categorías</option>

            <?php while ($cat = $categorias->fetch()): ?>
                <option value="<?= $cat['id'] ?>"
                    <?= ($categoria == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </option>
            <?php endwhile; ?>

        </select>
    </div>

    <div class="col-md-2 d-grid">
        <button class="btn btn-primary rounded-pill" type="submit">
            <i class="fa fa-search"></i> Buscar
        </button>
    </div>

</form>



<style>
.btn-carrito{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;

    background: linear-gradient(135deg, #ff4f98, #ff7ab8);
    color:#fff;
    font-weight:600;

    padding:12px 22px;
    border-radius:50px; /* bien redondito tipo pill */
    text-decoration:none;

    box-shadow:0 8px 20px rgba(255,79,152,.35);
    transition:0.3s ease;

    border:none;
}

.btn-carrito:hover{
    transform:translateY(-3px);
    background: linear-gradient(135deg, #ff2f86, #ff5da8);
    box-shadow:0 12px 25px rgba(255,79,152,.45);
    color:#fff;
}

/* opcional: efecto glow suave */
.btn-carrito i{
    font-size:18px;
}
</style>

<a href="carrito.php"
   class="btn btn-carrito mb-3 <?= count($_SESSION['carrito'])>0 ? 'btn-pulse' : '' ?>">
   🛒 Ver carrito (<?= count($_SESSION['carrito']) ?>)
</a>

<div class="row">
<?php while ($p = $result->fetch()): ?>
  <div class="col-md-3 mb-4">
    <div class="card card-producto shadow-sm h-100">

      <!-- Imagen del producto -->
      <div class="imagen-producto">
        <img src="productos/<?= htmlspecialchars($p['imagen'] ?: 'sin_imagen.jpg') ?>" 
             alt="<?= htmlspecialchars($p['nombre']) ?>" 
             style="max-height:100%; max-width:100%; object-fit:contain;"
             onclick="abrirModal(this.src)">
      </div>

      <div class="card-body text-center d-flex flex-column">
        <h5 class="card-title mb-1"><?= htmlspecialchars($p['nombre']) ?></h5>

        <b class="mb-2" style="font-size:1.2rem;color:#e91e63;">
            Q<?= number_format($p['precio'], 2) ?>
        </b>

        <?php if(!empty($p['talla'])): ?>
        <div class="mb-2">
            <span class="badge bg-secondary">
                Talla: <?= htmlspecialchars($p['talla']) ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- Cuadro de color seleccionado -->
        <div class="mb-2" id="colorCuadro<?= $p['id'] ?>">
          <span class="color-cuadro" style="background:#fff;"></span>
          Color seleccionado
        </div>

        <form method="POST" class="mt-auto">
          <input type="hidden" name="id" value="<?= $p['id'] ?>">

          <input type="hidden" name="talla" value="<?= htmlspecialchars($p['talla']) ?>">

          <!-- Selector de colores -->
          <select name="color" class="form-select mb-2 colorSelect" required data-producto="<?= $p['id'] ?>">
            <option value="">Selecciona color</option>

            <?php
            $colores = $pdo->query("
                SELECT pc.id, c.nombre, c.hex, pc.cantidad 
                FROM ventasbasica_producto_colores pc
                JOIN ventasbasica_colores c ON pc.color_id=c.id
                WHERE pc.producto_id={$p['id']} 
                AND pc.estado='activo' 
                AND pc.cantidad>0
            ");

            while ($col = $colores->fetch()):
            ?>
                <option value="<?= $col['id'] ?>"
                        data-stock="<?= $col['cantidad'] ?>"
                        data-hex="<?= $col['hex'] ?>">
                    <?= $col['nombre'] ?> (<?= $col['cantidad'] ?> disponibles)
                </option>
            <?php endwhile; ?>

          </select>

          <!-- Cantidad -->
          <input type="number"
                 name="cantidad"
                 min="1"
                 value="1"
                 class="form-control mb-2 cantidadInput"
                 required>

          <button class="btn btn-sm btn-primary btn-producto w-100" name="agregar">
              <i class="fa-solid fa-cart-plus"></i> Agregar al carrito
          </button>

        </form>
      </div>

    </div>
  </div>
<?php endwhile; ?>
</div>

</div>

<div class="modal fade" id="imagenModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body p-0">
        <img src="" id="imagenModalSrc" class="img-fluid w-100" alt="Imagen ampliada">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Modal imagen
function abrirModal(src) {
    const modalImg = document.getElementById('imagenModalSrc');
    modalImg.src = src;
    const modal = new bootstrap.Modal(document.getElementById('imagenModal'));
    modal.show();
}

// Actualizar color y cantidad
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.card-body');

    cards.forEach(card => {
        const colorSelect = card.querySelector('.colorSelect');
        const cantidadInput = card.querySelector('.cantidadInput');
        const productoId = colorSelect.dataset.producto;
        const colorCuadro = document.getElementById('colorCuadro' + productoId).querySelector('.color-cuadro');

        colorSelect.addEventListener('change', () => {
            const selectedOption = colorSelect.selectedOptions[0];
            if (selectedOption && selectedOption.dataset.stock) {
                cantidadInput.max = selectedOption.dataset.stock;
                if (parseInt(cantidadInput.value) > selectedOption.dataset.stock) {
                    cantidadInput.value = selectedOption.dataset.stock;
                }
                // Actualizar color del cuadrito
                colorCuadro.style.background = selectedOption.dataset.hex || '#fff';
                colorCuadro.nextSibling.textContent = ' ' + selectedOption.text.split('(')[0].trim();
            } else {
                cantidadInput.removeAttribute('max');
                colorCuadro.style.background = '#fff';
                colorCuadro.nextSibling.textContent = ' Color Seleccionado';
            }
        });
    });
});
</script>
    


    </section>

    <section id="nosotros">
        <div class="nosotros-contenedor">
            <div class="nosotros-imagen">
                <img src="img/nosotros.png" alt="Nosotros">
            </div>
            <div class="nosotros-texto">
                <h2>¿Quiénes Somos?</h2>
                <p>Somos una tienda en línea dedicada a ofrecer prendas a la moda, conoce mas sobre nuestros productos.</p>
                <ul>
                    <li>✔ Hacemos envíos a todo el país</li>
                    <li>✔ Envíos a Departamentos por Cargo Express, Ciudad capital por medio de mensajería</li>
                    <li>✔ Puedes combinar estilos y colores</li>
                </ul>
            </div>
        </div>
    </section>



    <style>
/* --- SECCIÓN CONTACTO --- */
#contacto {
    background-color: #ffffff; 
    padding: 80px 20px; /* Más espaciado para que se vea más grande e imponente */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
}

.contacto-contenedor {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 50px;
}

/* Cambio a dos columnas en Escritorio */
@media (min-width: 768px) {
    .contacto-contenedor {
        flex-direction: row;
        align-items: center;
    }
    .contacto-info-bloque {
        flex: 1;
        padding-right: 40px;
        border-right: 1px solid #e2e8f0;
    }
    .contacto-redes-bloque {
        flex: 1;
        padding-left: 40px;
    }
}

/* Estilos de la Información Izquierda */
.contacto-info-bloque h2 {
    color: #1a365d;
    font-size: 2.2rem;
    margin-bottom: 15px;
    font-weight: 700;
}

.contacto-info-bloque h2 i {
    color: #ff4757; /* Color de acento para el avioncito */
}

.contacto-subtitulo {
    color: #666;
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 30px;
}

.info-detalles {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 35px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.info-item i {
    font-size: 1.5rem;
    color: #007bff;
    background: #f0f7ff;
    padding: 12px;
    border-radius: 50%;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-item strong {
    display: block;
    color: #2c3e50;
    font-size: 1rem;
}

.info-item p {
    margin: 2px 0 0 0;
    color: #666;
}

/* Botón de WhatsApp */
.btn-whatsapp {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background-color: #25d366;
    color: #fff;
    text-decoration: none;
    padding: 15px 30px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.btn-whatsapp:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
}

/* Estilos del Bloque de Redes (Derecha) */
.contacto-redes-bloque h3 {
    color: #2c3e50;
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.contacto-redes-bloque p {
    color: #666;
    margin-bottom: 25px;
}

/* Grid de Redes Sociales (Tarjetas) */
.redes-grid {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.card-red {
    display: flex;
    align-items: center;
    padding: 18px 25px;
    border-radius: 12px;
    text-decoration: none;
    color: #fff;
    transition: transform 0.25s ease, filter 0.25s ease;
}

.card-red:hover {
    transform: translateX(8px);
    filter: brightness(1.1);
}

.red-icono {
    font-size: 1.8rem;
    margin-right: 20px;
    width: 40px;
    display: flex;
    justify-content: center;
}

.red-texto span {
    display: block;
    font-size: 1.2rem;
    font-weight: 600;
}

.red-texto small {
    display: block;
    font-size: 0.85rem;
    opacity: 0.85;
}

/* Colores de las Redes de la Tienda */
.card-red.facebook {
    background: linear-gradient(135deg, #3b5998, #4e71ba);
    box-shadow: 0 4px 15px rgba(59, 89, 152, 0.2);
}

.card-red.instagram {
    background: linear-gradient(135deg, #833ab4, #fd1d1d, #fcb045);
    box-shadow: 0 4px 15px rgba(253, 29, 29, 0.2);
}

.card-red.tiktok {
    background: linear-gradient(135deg, #000000, #25f4ee, #fe2c55);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}
</style>


   <section id="contacto">
    <div class="contacto-contenedor">
        
        <!-- Columna Izquierda: Información de la Tienda y Formulario/Horarios -->
        <div class="contacto-info-bloque">
            <h2><i class="fas fa-paper-plane"></i> ¿Tienes alguna duda?</h2>
            <p class="contacto-subtitulo">Escríbenos directamente o visítanos en nuestras redes sociales. ¡Estamos listos para ayudarte!</p>
            
            <div class="info-detalles">
                <div class="info-item">
                    <i class="fas fa-store"></i>
                    <div>
                        <strong>Tipo de Tienda</strong>
                        <p>Tienda 100% en Línea con envíos seguros</p>
                    </div>
                </div>
                
                <!--
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Horario de Atención</strong>
                        <p>Lunes a Sábado: 8:00 a.m. a 6:00 p.m.</p>
                    </div>
                </div>
                -->
            </div>

            

            <!-- Botón de WhatsApp Destacado (Ideal para e-commerce en GT) 
            <a href="https://wa.me/50257184268" target="_blank" class="btn-whatsapp">
                <i class="fab fa-whatsapp"></i> Escríbenos por WhatsApp
            </a>
            -->
            <a href="https://wa.me/50257184268?text=Hola%NOMBEW%20EMPRESA,%20quisiera%20recibir%20m%C3%A1s%20informaci%C3%B3n%20acerca%20de%20sus%20productos.%20%C2%A1Gracias!"
                target="_blank"
                class="btn-whatsapp">
                    <i class="fab fa-whatsapp"></i> Escríbenos por WhatsApp
                </a>

        </div>
        
        <!-- Columna Derecha: Redes Sociales Robustas -->
        <div class="contacto-redes-bloque">
            <h3>Conéctate con Nosotros</h3>
            <p>Síguenos para conocer nuevos ingresos, ofertas y dinámicas:</p>
            
            <div class="redes-grid">
                <a href="https://www.facebook.com/share/1JWMNcGteQ/" target="_blank" class="card-red facebook">
                    <div class="red-icono"><i class="fab fa-facebook-f"></i></div>
                    <div class="red-texto">
                        <span>Facebook</span>
                        <small>¡Dale Me Gusta!</small>
                    </div>
                </a>

                <a href="https://www.instagram.com/wow_amy.gt?igsh=MTlteXlwYm4ydTZjaw==" target="_blank" class="card-red instagram">
                    <div class="red-icono"><i class="fab fa-instagram"></i></div>
                    <div class="red-texto">
                        <span>Instagram</span>
                        <small>@wow_amy.gt</small>
                    </div>
                </a>

                <a href="https://www.tiktok.com/@wowamygt?_r=1&_t=ZS-97bRlFlvyEv" target="_blank" class="card-red tiktok">
                    <div class="red-icono"><i class="fab fa-tiktok"></i></div>
                    <div class="red-texto">
                        <span>TikTok</span>
                        <small>@wowamygt</small>
                    </div>
                </a>
            </div>
        </div>

    </div>
</section>

<style>
/* --- SECCIÓN ENVIOS --- */
#envios {
    background-color: #f8f9fa; /* Fondo gris claro muy limpio */
    padding: 60px 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
}

.envios-contenedor {
    max-width: 1100px;
    margin: 0 auto;
}

.envios-header {
    text-align: center;
    margin-bottom: 40px;
}

.envios-header h2 {
    color: #1a365d; /* Azul institucional o el de tu marca */
    font-size: 2rem;
    margin-bottom: 10px;
    font-weight: 700;
}

.envios-header .subtitle {
    color: #666;
    font-size: 1.1rem;
}

/* Grid Responsivo: En escritorio se ve lado a lado, en móvil uno abajo del otro */
.envios-grid {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

@media (min-width: 768px) {
    .envios-grid {
        flex-direction: row;
        align-items: flex-start;
    }
    .envios-info {
        flex: 1.2; /* Le da un poco más de espacio al texto */
    }
}

/* Títulos de las secciones internas */
.envios-info h3 {
    color: #2c3e50;
    font-size: 1.3rem;
    margin-top: 30px;
    margin-bottom: 15px;
    border-left: 4px solid #007bff; /* Detalle visual azul */
    padding-left: 10px;
}

/* Tarjetas de Precios */
.precios-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 15px;
    margin-bottom: 25px;
}

@media (min-width: 480px) {
    .precios-cards {
        grid-template-columns: 1fr 1fr;
    }
}

.card-precio {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    text-align: center;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Se elimina el estilo específico para .destacada para que las cards sean iguales */
/*.card-precio.destacada {
    border-color: #007bff;
    background-color: #f0f7ff;
}*/

.card-precio .zona {
    font-size: 0.95rem;
    font-weight: 600;
    color: #4a5568;
    margin-bottom: 5px;
}

.card-precio .precio {
    font-size: 1.8rem;
    font-weight: 700;
    color: #007bff;
    margin-bottom: 5px;
}

.card-precio .detalle {
    font-size: 0.85rem;
    color: #718096;
}

/* Tiempos de Entrega */
.tiempos-entrega {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.tiempo-item {
    margin-bottom: 15px;
}

.tiempo-item:last-child {
    margin-bottom: 0;
}

.tiempo-item p {
    margin: 5px 0 0 0;
    color: #555;
}

/* Nota de Alerta */
.alerta-nota {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
    color: #856404;
    padding: 15px;
    border-radius: 4px;
    margin-top: 30px;
}

.alerta-nota p {
    margin: 0;
}

/* Contenedor de la Imagen */
.envios-imagen {
    display: flex;
    justify-content: center;
    align-items: center;
    /* Ajuste para hacer la imagen más pequeña */
    max-width: 80%; /* Ajusta este valor según prefieras */
    margin: 0 auto;
}

@media (min-width: 768px) {
    .envios-imagen {
        max-width: 30%; /* Ajuste para escritorio */
    }
}

.img-responsiva {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    /* Opcional: añade una sutil sombra si la imagen tiene fondo blanco */
    filter: drop-shadow(0px 8px 16px rgba(0,0,0,0.1)); 
}
</style>

<section id="envios">
    <div class="envios-contenedor">
        
        <div class="envios-header">
            <h2>Hacemos envíos a todo el país</h2>
            <p class="subtitle">Entregas rápidas y seguras hasta la puerta de tu casa u oficina.</p>
        </div>

        <div class="envios-imagen">
            <img src="img/envio.png" alt="Información de Envíos en Guatemala" class="img-responsiva">
        </div>

        <div class="envios-grid">
            
         <div class="envios-info">
                
            <h3><i class="fas fa-truck"></i> Costo de envío</h3>
            <div class="precios-cards">
                <div class="card-precio">
                    <span class="zona">Ciudad Capital</span>
                    <span class="precio">Q. 25</span>
                    <span class="detalle">Dentro del perímetro</span>
                </div>
                <div class="card-precio"> 
                    <span class="zona">Departamentos</span>
                    <span class="precio">Q. 36</span>
                    <span class="detalle">Y lugares aledaños a la ciudad capital</span>
                </div>
            </div>

            <h3><i class="fas fa-map-marked-alt"></i> Cobertura</h3>
            <p>Si el envío es a departamentos, será entregado de forma segura por medio de <strong>Cargo Express</strong>.</p>

            <h3><i class="fas fa-clock"></i> Tiempos de entrega</h3>
            <div class="tiempos-entrega">
                <div class="tiempo-item">
                    <strong>Ciudad de Guatemala:</strong> 
                    <p>Entre 1 a 2 días hábiles, en horario de 8:00 a.m. a 6:00 p.m.</p>
                </div>
                <div class="tiempo-item">
                    <strong>Departamentos:</strong> 
                    <p>Entre 2 a 6 días hábiles, según la ubicación de tu municipio.</p>
                </div>
            </div>

            <div class="alerta-nota">
                <p>🔔 <strong>Importante:</strong> Por favor, mantente pendiente de tu teléfono para coordinar la recepción de tu pedido.</p>
            </div>
        </div>

    </div>
</section>

    <footer>
        <div class="footer-contenedor">
            <div class="footer-col">
                <img src="img/footer.png" alt="Logo">
                <p>Wow Amy Gt</p>
            </div>
            <div class="footer-col footer-links">
                <a href="#inicio">Inicio</a>
                <a href="#productos">Productos</a>
                <a href="#contacto">Contacto</a>
				<a href="#nosotros">Nosotros</a>
                <a href="#envios">Envíos</a>
            </div>
            <div class="footer-col footer-redes">
                <a href="https://www.facebook.com/share/1JWMNcGteQ/" target="_blank">FB</a>
                <a href="https://www.instagram.com/wow_amy.gt?igsh=MTlteXlwYm4ydTZjaw==" target="_blank">IG</a>
                <a href="https://www.tiktok.com/@wowamygt?_r=1&_t=ZS-97bRlFlvyEv" target="_blank">TK</a>
            </div>
        </div>
        <hr>
        <p class="copyright">
            © 2026 Wow Amy Gt
        </p>
    </footer>
	
	<script>
        // Seleccionamos el checkbox y todos los enlaces dentro del menú de navegación
        const menuToggle = document.getElementById('menu-toggle');
        const navLinks = document.querySelectorAll('nav ul li a');

        // Recorremos cada enlace y le añadimos el evento click
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                // Al hacer clic, desmarcamos el checkbox para cerrar el menú
                menuToggle.checked = false;
            });
        });
    </script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("formFiltro");
    const categoria = document.getElementById("categoria");

    // Filtrar automáticamente al cambiar categoría
    categoria.addEventListener("change", function () {
        form.submit();
    });

});
</script>

<style>
#toastExito{

    position:fixed;
    top:20px;
    right:20px;

    background:#198754;
    color:#fff;

    padding:14px 22px;

    border-radius:12px;

    font-weight:600;

    box-shadow:0 10px 25px rgba(0,0,0,.25);

    z-index:99999;

    display:flex;
    align-items:center;
    gap:10px;

    opacity:0;
    transform:translateX(120%);
    transition:.4s;
}

#toastExito.mostrar{

    opacity:1;
    transform:translateX(0);

}

#toastExito i{
    font-size:22px;
}

</style>

<?php if(isset($_SESSION['toast'])): ?>

<div id="toastExito">
    <i class="fa-solid fa-circle-check"></i>
    <?= $_SESSION['toast']; ?>
</div>

<script>
window.onload=function(){

    const toast=document.getElementById("toastExito");

    setTimeout(()=>{
        toast.classList.add("mostrar");
    },100);

    setTimeout(()=>{
        toast.classList.remove("mostrar");
    },2500);

};
</script>

<?php unset($_SESSION['toast']); endif; ?>

<!-- POPUP BIENVENIDA / PROMOCIONAL (SOLO 1 VEZ) -->
<div id="promoPopup" class="popup-overlay" style="display: none;">
  <div class="popup-content">
    <button type="button" class="popup-close" onclick="cerrarPopup()">&times;</button>
    <!-- Reemplaza "img/tu_imagen.jpg" con la ruta real de tu imagen -->
    <img src="popup.jpeg" alt="Promoción Especial" class="popup-image">
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Verificar si ya se mostró en esta PESTAÑA actual
    const popupVistoEnPestana = sessionStorage.getItem('popupPromoVisto');

    if (!popupVistoEnPestana) {
        setTimeout(() => {
            const popup = document.getElementById('promoPopup');
            if (popup) popup.style.display = 'flex';
        }, 500);
    }
});

function cerrarPopup() {
    const popup = document.getElementById('promoPopup');
    if (popup) popup.style.display = 'none';
    
    // Guardar solo para esta pestaña activa
    sessionStorage.setItem('popupPromoVisto', 'true');
}

// Cerrar si hace clic fuera de la imagen
document.addEventListener('click', (e) => {
    const popup = document.getElementById('promoPopup');
    if (e.target === popup) {
        cerrarPopup();
    }
});
</script>
</body>
</html>