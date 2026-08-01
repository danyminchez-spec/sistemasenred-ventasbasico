<?php
session_start();
ob_start();
// ----------------------------------------------------------------------
// SECCIÓN DEPURACIÓN TEMPORAL (Mantengo los debugs por si son útiles para entender el flujo)
// ----------------------------------------------------------------------
if (isset($_SESSION['usuario'])) {
   // echo "<p style='background-color: yellow; padding: 5px;'>Bienvenido: Usuario de sesión: " . htmlspecialchars($_SESSION['usuario']) . "</p>";
} else {
    //echo "<p style='background-color: orange; padding: 5px;'>DEBUG: No hay usuario en la sesión. Redirigiendo a login...</p>";
    header("Location: login.php"); // Redirige si no hay sesión
    exit();
}

include("../db.php");


$busqueda = $_GET['busqueda'] ?? '';

// Cantidad de registros por página
$registros_por_pagina = 10;

// Página actual
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

if($pagina < 1){
    $pagina = 1;
}

// Desde qué registro comenzar
$inicio = ($pagina - 1) * $registros_por_pagina;

// Consulta base
$sql = "SELECT * FROM ventasbasica_envios WHERE 1=1";
$params = [];

if($busqueda != ''){
    $sql .= " AND (
        cliente LIKE ?
        OR numero_guia LIKE ?
        OR estado LIKE ?
        OR departamento LIKE ?
    )";
    $busqueda_param = "%$busqueda%";
    $params = [$busqueda_param, $busqueda_param, $busqueda_param, $busqueda_param];
}

// Contar registros
$sql_total = $sql;
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute($params);
$total_registros = $stmt_total->rowCount();

// Total de páginas
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consulta paginada
$sql .= " ORDER BY id DESC LIMIT $inicio, $registros_por_pagina";

$envios = $pdo->prepare($sql);
$envios->execute($params);
?>

<?php
if(isset($_GET['ok'])){

    if($_GET['ok'] == 'guardado'){
        echo '<div class="alert alert-success">Registro guardado correctamente.</div>';
    }

    if($_GET['ok'] == 'editado'){
        echo '<div class="alert alert-primary">Registro actualizado correctamente.</div>';
    }

    if($_GET['ok'] == 'eliminado'){
        echo '<div class="alert alert-danger">Registro eliminado correctamente.</div>';
    }
}
?>



<?php

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
    min-height:90vh;
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

/*==================================
TABLA RESPONSIVE PEDIDOS
===================================*/

@media (max-width:768px){

    .table{
        border:0;
    }

    .table thead{
        display:none;
    }

    .table,
    .table tbody,
    .table tr,
    .table td,
    .table tfoot{
        display:block;
        width:100%;
    }

    .table tr{
        margin-bottom:18px;
        border:1px solid #ddd;
        border-radius:12px;
        overflow:hidden;
        background:#fff;
        box-shadow:0 4px 10px rgba(0,0,0,.08);
    }

    .table td{
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:12px 15px;
        text-align:right;
        border:none;
        border-bottom:1px solid #eee;
        font-size:14px;
    }

    .table td:last-child{
        border-bottom:none;
    }

    .table td::before{
        content:attr(data-label);
        font-weight:700;
        color:#ff4f98;
        text-align:left;
        margin-right:15px;
    }

    .table tfoot tr{
        background:#fff0f6;
    }

    .table tfoot td{
        font-weight:bold;
    }

}

/*********************** para menu */
/* Barra superior */
header{
    width:100%;
    background:#fff;
    padding:12px 25px;
    box-shadow:0 2px 12px rgba(0,0,0,.08);
}

/* Menú */
.menu-superior{
    display:flex;
    justify-content:flex-end;
    align-items:center;
    gap:15px;
    list-style:none;
    margin:0;
    padding:0;
}

/* Botones */
.menu-superior li a{
    display:flex;
    align-items:center;
    gap:8px;
    padding:10px 18px;
    text-decoration:none;
    color:#fff;
    font-weight:600;
    border-radius:30px;
    transition:.3s;
}

/* Cambiar clave */
#btnCambiarClave{
    background:linear-gradient(135deg,#ff6fa5,#ff4f8b);
}

#btnCambiarClave:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 18px rgba(255,79,139,.35);
}

/* Salir */
.menu-superior li:last-child a{
    background:#555;
}

.menu-superior li:last-child a:hover{
    background:#333;
    transform:translateY(-2px);
}


</style>

<body>


<header>
    <nav>
        <ul class="menu-superior">
          <!--    <li><a id="btnCambiarClave" href="#">🔒 Cambiar Clave</a></li> -->
            <li><a  id="btnCambiarClave" href="index.php">⬅️ Regresar</a></li>
            <li><a  id="btnCambiarClave" href="dashboard_envios.php">📈 Dashboard</a></li>
            <li><a href="logout.php">🚪 Salir</a></li>
        </ul>
    </nav>
</header>
	
	
	


	
	

    <section id="productos">
    
    <div class="container py-4">

      <h2 class="text-center mb-4">📦 Envíos</h2>

<div class="card">

<div class="card-header">

<i class="fa-solid fa-box"></i>

Control de Envíos

</div>

<div class="card-body">

<form method="POST" action="guardar_envio.php">

<div class="row">

<div class="col-md-6 mb-3">
<label class="form-label">Fecha</label>
<input type="date" class="form-control" name="fecha">
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Número Guía</label>
<input type="text" class="form-control" name="numero_guia">
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Nombre Cliente</label>
<input type="text" class="form-control" name="cliente">
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Estado</label>

<select class="form-select" name="estado">

<option value="">Seleccione</option>

<option>Enviado</option>

<option>Entregado</option>

<option>Devolución</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">Departamento</label>

<select class="form-select" name="departamento">

<option value="">Seleccione</option>

<option>Alta Verapaz</option>
<option>Baja Verapaz</option>
<option>Chimaltenango</option>
<option>Chiquimula</option>
<option>El Progreso</option>
<option>Escuintla</option>
<option>Guatemala</option>
<option>Huehuetenango</option>
<option>Izabal</option>
<option>Jalapa</option>
<option>Jutiapa</option>
<option>Petén</option>
<option>Quetzaltenango</option>
<option>Quiché</option>
<option>Retalhuleu</option>
<option>Sacatepéquez</option>
<option>San Marcos</option>
<option>Santa Rosa</option>
<option>Sololá</option>
<option>Suchitepéquez</option>
<option>Totonicapán</option>
<option>Zacapa</option>

</select>

</div>

<div class="col-md-6 mb-3">
<label class="form-label">Municipio</label>
<input type="text" class="form-control" name="municipio">
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Total (Q.)</label>
<input type="number" step="0.01" class="form-control" name="total">
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Cantidad Prendas</label>
<input type="number" class="form-control" name="cantidad">
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Inversión</label>
<input type="number" step="0.01" class="form-control" name="inversion">
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Ganancia</label>
<input type="number" step="0.01" class="form-control" name="ganancia">
</div>

<div class="col-md-3 mb-3">

<label class="form-label">Red Social</label>

<select class="form-select" name="red_social">

<option value="">Seleccione</option>

<option>Facebook</option>
<option>Instagram</option>
<option>Twitter</option>
<option>Pagina Web</option>
<option>WhatsApp</option>

</select>

</div>

<div class="col-md-3 mb-3">

<label class="form-label">Empresa</label>

<select class="form-select" name="empresa">

<option value="">Seleccione</option>

<option>Cargo Express</option>
<option>Box Delivery</option>

</select>

</div>

</div>

<div class="text-center">

<button
type="submit"
name="guardar"
class="btn btn-guardar">

<i class="fa-solid fa-floppy-disk"></i>

Guardar

</button>



</div>

</form>

<hr class="my-4">

<h4 class="mb-3">

<i class="fa-solid fa-list"></i>

Listado de Envíos

</h4>

<form method="GET" class="row g-2 mb-3">

    <div class="col-md-10">
        <input
            type="text"
            name="busqueda"
            class="form-control"
            placeholder="Buscar por cliente, guía, estado o departamento..."
            value="<?= htmlspecialchars($busqueda) ?>">
    </div>

    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-search"></i> Buscar
        </button>
    </div>

</form>

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle text-center">

<thead>

<tr>

<th>Fecha</th>

<th>Guía</th>

<th>Cliente</th>

<th>Estado</th>

<th>Departamento</th>

<th>Total</th>

<th>Acciones</th>

</tr>

</thead>

<tbody>

<?php

if($envios->rowCount()>0){

while($row=$envios->fetch()){

?>

<tr>

<td data-label="Fecha"><?=date("d/m/Y",strtotime($row['fecha']))?></td>

<td data-label="Guía"><?=$row['numero_guia']?></td>

<td data-label="Cliente"><?=$row['cliente']?></td>

<td data-label="Estado"><?=$row['estado']?></td>

<td data-label="Departamento"><?=$row['departamento']?></td>

<td data-label="Total">Q <?=number_format($row['total'],2)?></td>

<td data-label="Acciones">

<a href="editar_envio.php?id=<?=$row['id']?>" class="btn btn-primary btn-sm">
    <i class="fa fa-edit"></i>
</a>

<a href="eliminar_envio.php?id=<?=$row['id']?>"
   class="btn btn-danger btn-sm"
   onclick="return confirm('¿Seguro que deseas eliminar este envío?');">
    <i class="fa fa-trash"></i>
</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="7">

No hay registros.

</td>

</tr>

<?php } ?>

</tbody>

</table>



</table>

<nav class="mt-3">
    <ul class="pagination justify-content-center">

        <!-- Botón Anterior -->
        <?php if($pagina > 1){ ?>
            <li class="page-item">
                <a class="page-link"
                   href="?pagina=<?= $pagina-1 ?>&busqueda=<?= urlencode($busqueda) ?>">
                    &laquo; Anterior
                </a>
            </li>
        <?php } ?>

        <!-- Números de página -->
        <?php for($i=1; $i<=$total_paginas; $i++){ ?>

            <li class="page-item <?= ($i==$pagina)?'active':''; ?>">

                <a class="page-link"
                   href="?pagina=<?= $i ?>&busqueda=<?= urlencode($busqueda) ?>">
                    <?= $i ?>
                </a>

            </li>

        <?php } ?>

        <!-- Botón Siguiente -->
        <?php if($pagina < $total_paginas){ ?>
            <li class="page-item">
                <a class="page-link"
                   href="?pagina=<?= $pagina+1 ?>&busqueda=<?= urlencode($busqueda) ?>">
                    Siguiente &raquo;
                </a>
            </li>
        <?php } ?>

    </ul>
</nav>

</div>



</div>

</div>

</div>

</div>

  <div class="text-center mt-3">
        <a href="index.php" class="btn btn-secondary">⬅️ Volver a administracion</a>
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


   
    <footer>
        <div class="footer-contenedor">
       
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
setTimeout(() => {
    let alerts = document.querySelectorAll('.alert');

    alerts.forEach(alert => {
        alert.style.transition = "0.5s";
        alert.style.opacity = "0";

        setTimeout(() => {
            alert.remove();
        }, 500);
    });

}, 3000); // 3 segundos
</script>


</body>
</html>