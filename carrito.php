<?php
session_start();
include 'db.php';

function obtenerPrecioPromocion($categoria, $cantidadCategoria, $precioOriginal){

    $categoria = strtolower($categoria);

    if($categoria != "croptop" && $categoria != "blusitas"){
        return $precioOriginal;
    }

    if($cantidadCategoria <= 2){
        return 35;
    }elseif($cantidadCategoria <= 5){
        return 33.33;
    }elseif($cantidadCategoria <= 11){
        return 28;
    }else{
        return 25;
    }

}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Vaciar carrito y destruir sesión
if (isset($_POST['vaciar'])) {
    session_destroy();
   echo "<script>
    window.location='index.php#productos';
    </script>";
    exit;
}

// Eliminar producto del carrito
if (isset($_GET['eliminar'])) {
    $id_color = $_GET['eliminar'];
    unset($_SESSION['carrito'][$id_color]);
    header("Location: carrito.php");
    exit;
}

// Actualizar cantidades
if (isset($_POST['actualizar'])) {
    foreach ($_POST['cantidades'] as $id_color => $cant) {
        $cant = max(1, (int)$cant);
        $query = $pdo->query("SELECT cantidad FROM ventasbasica_producto_colores WHERE id=$id_color");
        if ($query && $row = $query->fetch()) {
            $stock = (int)$row['cantidad'];
            if ($cant > $stock) $cant = $stock;
            $_SESSION['carrito'][$id_color]['cantidad'] = $cant;
        }
    }
   echo "<script>
window.location='carrito.php';
</script>";
    exit;
}

// Cantidad total por categoría para promociones
$cantCategorias = [];

foreach($_SESSION['carrito'] as $item){

    $cat = strtolower($item['categoria']);

    if($cat=="croptop" || $cat=="blusitas"){

        if(!isset($cantCategorias[$cat])){
            $cantCategorias[$cat]=0;
        }

        $cantCategorias[$cat]+=$item['cantidad'];

    }

}

// Confirmar pedido
$pedido_confirmado = false;
if (isset($_POST['confirmar']) && !empty($_SESSION['carrito'])) {
    // Calcular total

    $total_pedido = 0;

foreach($_SESSION['carrito'] as $item){

    $categoria = strtolower($item['categoria']);

    $precio = obtenerPrecioPromocion(
        $categoria,
        $cantCategorias[$categoria] ?? 0,
        $item['precio']
    );

    $total_pedido += $precio * $item['cantidad'];

}

    // Insertar encabezado
    $pdo->query("INSERT INTO ventasbasica_pedidos (total) VALUES ($total_pedido)");
    $pedido_id = $pdo->lastInsertId();

    // Insertar detalles
    foreach ($_SESSION['carrito'] as $id_color => $item) {
        $nombre       = $item['nombre'];
        $categoria_id = (int)$item['categoria_id'];
        $color        = $item['color'];
        $cantidad = $item['cantidad'];
        $talla = $item['talla'];

        $precio = obtenerPrecioPromocion(
            strtolower($item['categoria']),
            $cantCategorias[strtolower($item['categoria'])] ?? 0,
            $item['precio']
        );

        $stmt = $pdo->prepare("
        INSERT INTO ventasbasica_pedido_detalles
        (
            pedido_id,
            id_color,
            nombre_producto,
            categoria_id,
            color,
            talla,
            cantidad,
            precio
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $pedido_id,
            $id_color,
            $nombre,
            $categoria_id,
            $color,
            $talla,
            $cantidad,
            $precio
        ]);
    }

    $pedido_confirmado = true;
    // NO vaciamos aún el carrito para mostrar el pedido en WhatsApp
}

// Preparar mensaje WhatsApp
// Preparar mensaje WhatsApp
$mensaje = "Hola TU EMPRESA,\n\n";
$mensaje .= "Me interesa comprar los siguientes productos:\n\n";
$total = 0;

if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $item) {

        $categoria = strtolower($item['categoria']);

        $precio = obtenerPrecioPromocion(
            $categoria,
            $cantCategorias[$categoria] ?? 0,
            $item['precio']
        );

        $subtotal = $precio * $item['cantidad'];

        // Formato con viñeta (-) y negrita (*palabra*) para WhatsApp
        $mensaje .= "- *Producto:* {$item['nombre']}\n";
        $mensaje .= "  *Categoría:* {$item['categoria']}\n";
        $mensaje .= "  *Color:* {$item['color']}\n";
        $mensaje .= "  *Talla:* {$item['talla']}\n";
        $mensaje .= "  *Cantidad:* {$item['cantidad']}\n";
        $mensaje .= "  *Precio:* Q" . number_format($precio, 2) . "\n";
        $mensaje .= "  *Subtotal:* Q" . number_format($subtotal, 2) . "\n\n";

        $total += $subtotal;
    }

    $mensaje .= "*Total:* Q" . number_format($total, 2);
    $mensaje .= "\n\nQuedo pendiente de su confirmación. ¡Muchas gracias!";
}

$telefono = "50257184268";
$mensaje_utf8 = mb_convert_encoding($mensaje, 'UTF-8', 'auto');
$url_whatsapp = "https://wa.me/$telefono?text=" . rawurlencode($mensaje_utf8);
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
    min-height:50vh;
    display:flex;
    align-items:center;
}

.hero-contenedor{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:60px;
    align-items:center;
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
.color-cuadro {
    display: inline-block;
  /*  width: 16px; */
  width: 50px;
      height: 25px;
 /*   height: 16px;*/
    border-radius: 3px;
    margin-right: 6px;
    vertical-align: middle;
    border: 1px solid #ccc;
}
</style>

<style>
.color-cuadro{
    display:inline-block;
    width:50px;
    height:25px;
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

}

/* Tablets */

@media (min-width:769px) and (max-width:991px){

    .row>.col-md-3{
        width:50%;
    }


}


@media (max-width:768px){

.table thead{
    display:none;
}

.table,
.table tbody,
.table tr,
.table td{
    display:block;
    width:100%;
}

.table tr{
    margin-bottom:18px;
    border:1px solid #ddd;
    border-radius:12px;
    overflow:hidden;
    background:#fff;
    box-shadow:0 3px 8px rgba(0,0,0,.08);
}

.table td{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:14px 18px;
    border:none;
    border-bottom:1px solid #eee;
    text-align:right;
    gap:15px;
}

.table td:last-child{
    border-bottom:none;
}

.table td::before{
    content:attr(data-label);
    font-weight:700;
    color:#555;
    text-align:left;
    white-space:nowrap;
}

/* Input cantidad */
.table td input.form-control{
    width:90px;
    margin:0 !important;
    text-align:center;
}

/* Botón eliminar */
.table td .btn{
    margin-left:auto;
}

}


/* arreglar el input de cantidad */

.cantidad-input{
    width:90px;
}

@media(max-width:768px){

.cantidad-input{
    width:140px;
    margin-left:0;
}

}

/*  para boton whatsapp */
/* =========================================
   BOTÓN DE WHATSAPP CON EFECTO PULSO
   ========================================= */
.btn-whatsapp {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background-color: #25d366 !important;
    color: #ffffff !important;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 50px;
    text-decoration: none;
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
    transition: transform 0.3s ease;
    animation: pulsoWhatsapp 2s infinite;
}

.btn-whatsapp:hover {
    transform: scale(1.05);
    background-color: #20ba5a !important;
    color: #fff !important;
}

/* Animación de Ondas */
@keyframes pulsoWhatsapp {
    0% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
    }
    70% {
        box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
    }
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
                <li><a href="index.php#inicio">Inicio</a></li>
                <li><a href="index.php#productos">Productos</a></li>
				<li><a href="index.php#nosotros">Nosotros</a></li>
                <li><a href="index.php#contacto">Contacto</a></li>
                <li><a href="index.php#envios">Envíos</a></li>
            </ul>
        </nav>
    </header>
	
	

    <section id="contacto">
       <h2 class="text-center mb-4">🛒 Mi Carrito</h2>

       
<style>
.fila-promocion{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    flex-wrap:wrap;
    padding:14px;
}

@media (max-width:768px){

    .fila-promocion{
        flex-direction:column;
        text-align:center;
        gap:8px;
    }

    .fila-promocion .badge{
        white-space:normal;
        max-width:100%;
    }

}
</style>

<form method="POST">
<table class="table table-bordered table-striped text-center align-middle">
  <thead class="table-dark">
    <tr>
      <th>Producto</th>
      <th>Categoria</th>
      <th>Color</th>
      <th>Talla</th>
      <th>Cantidad</th>
      <th>Precio</th>
      <th>Total</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
<?php if (!empty($_SESSION['carrito'])): ?>

    <?php
// Sumar cantidades por categoría
$cantCategorias = [];

foreach ($_SESSION['carrito'] as $it) {

    $cat = strtolower($it['categoria']);

    if ($cat == 'croptop' || $cat == 'blusitas') {

        if (!isset($cantCategorias[$cat])) {
            $cantCategorias[$cat] = 0;
        }

        $cantCategorias[$cat] += $it['cantidad'];
    }
}


    $total_general = 0; 
    foreach ($_SESSION['carrito'] as $id_color => $item): 
      $cantidad = $item['cantidad'];
$precio = obtenerPrecioPromocion(
    strtolower($item['categoria']),
    $cantCategorias[strtolower($item['categoria'])] ?? 0,
    $item['precio']
);
$subtotal = 0;

// Promoción para Croptop y Blusitas
$cat = strtolower($item['categoria']);

if ($cat == 'croptop' || $cat == 'blusitas') {

    // Cantidad TOTAL de esa categoría
    $cantidadCategoria = $cantCategorias[$cat];

    if ($cantidadCategoria <= 2) {

        $precioPromo = 35;

    } elseif ($cantidadCategoria <= 5) {

        $precioPromo = 33.33;

    } elseif ($cantidadCategoria <= 11) {

        $precioPromo = 28;

    } else {

        $precioPromo = 25;

    }

    $precio = $precioPromo;
    $subtotal = $cantidad * $precioPromo;

} else {

    $subtotal = $cantidad * $precio;
}

$total_general += $subtotal;
?>
    <tr>
    <td data-label="🛍️ Producto">
        <?= htmlspecialchars($item['nombre']) ?>
    </td>

    <td data-label="📂 Categoría">
    <?= htmlspecialchars($item['categoria']) ?>
    </td>

    <td data-label="🎨 Color">
        <span style="display:inline-block;width:30px;height:25px;background:<?= htmlspecialchars($item['hex'] ?? '#ccc') ?>;border-radius:3px;margin-right:4px;"></span>
        <?= htmlspecialchars($item['color']) ?>
    </td>

    <td><?= htmlspecialchars($item['talla']) ?></td>

    <td data-label="🔢 Cantidad">
        <input type="number"
               name="cantidades[<?= $id_color ?>]"
               value="<?= $item['cantidad'] ?>"
               min="1"
              class="form-control cantidad-input"
               <?= $pedido_confirmado ? 'disabled' : '' ?>>
    </td>

    

    <td data-label="💰 Precio">
       Q<?= number_format($precio, 2) ?>
    </td>

    <td data-label="🧾 Total">
        Q<?= number_format($subtotal, 2) ?>
    </td>

    <td data-label="🗑️ Acción">
        <a href="?eliminar=<?= $id_color ?>" class="btn btn-danger btn-sm <?= $pedido_confirmado ? 'disabled' : '' ?>">
            🗑️
        </a>
    </td>
</tr>
    <?php endforeach; ?>
  
    <tr class="table-light fw-bold">
    <td colspan="4" class="text-end">Total:</td>
    <td colspan="2">Q<?= number_format($total_general, 2) ?></td>
</tr>

<?php
foreach($cantCategorias as $categoria => $cant):

    if($cant <= 2){

        $mensajePromo = "Precio normal (Q35 c/u)";

    }elseif($cant <= 5){

        $mensajePromo = "Promoción aplicada: 3 x Q100";

    }elseif($cant <= 11){

        $mensajePromo = "Promoción aplicada: 6 x Q168";

    }else{

        $mensajePromo = "Promoción aplicada: 12 x Q300 + adicionales a Q25";

    }
?>

<tr style="background:#eafaf1;">
    <td colspan="7" class="fila-promocion">

    <span class="promo-categoria">
        <i class="fas fa-tags text-success"></i>
        <strong><?= ucfirst($categoria) ?></strong>
    </span>

    <span class="promo-cantidad">
        <?= $cant ?> prendas
    </span>

    <span class="badge bg-success">
        <?= $mensajePromo ?>
    </span>

</td>
</tr>

<?php endforeach; ?>

    <tr>
  <td colspan="6" class="text-center">
    <?php if (!$pedido_confirmado): ?>
        <button class="btn btn-primary" name="actualizar">🔄 Actualizar</button>
        <button class="btn btn-warning" name="confirmar">✅ Confirmar pedido</button>
    <?php else: ?>
        <!--
        <a href="<?= $url_whatsapp ?>" target="_blank" class="btn-whatsapp">
            <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i> Enviar pedido por WhatsApp
        </a>

        -->


        <a href="#" onclick="window.open('<?= $url_whatsapp ?>', '_blank'); return false;" class="btn-whatsapp">
    <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i> Enviar pedido por WhatsApp
    </a>
    <?php endif; ?>
  </td>
</tr>
<?php else: ?>
    <tr><td colspan="7"><?= $pedido_confirmado ? "✅ Pedido enviado, pendiente de aprobación." : "🕸️ Tu carrito está vacío" ?></td></tr>
<?php endif; ?>
  </tbody>
</table>
</form>

<div class="text-center mt-3">
<a href="index.php#productos" class="btn btn-secondary">⬅️ Seguir comprando</a>
  <form method="POST" style="display:inline;">
      <button type="submit" name="vaciar" class="btn btn-danger">🗑️ Vaciar carrito</button>
  </form>
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
            </div>
            <div class="footer-col footer-redes">
                <a href="https://www.facebook.com/share/1JWMNcGteQ/" target="_blank">FB</a>
                <a href="https://www.instagram.com/wow_amy.gt?igsh=MTlteXlwYm4ydTZjaw==" target="_blank">IG</a>
                <a href="https://www.tiktok.com/@wowamygt?_r=1&_t=ZS-97bRlFlvyEv target="_blank">TK</a>
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

</body>
</html>