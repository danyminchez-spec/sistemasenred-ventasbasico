<?php
ob_start();
include("../db.php");
// ** 💡 FIN - FIX PARA SESIONES EN AZURE APP SERVICE **
session_start(); // ¡IMPORTANTE! Inicia la sesión al principio de la página
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


// ----------------------------------------------------------------------
// FIN SECCIÓN DEPURACIÓN TEMPORAL
// ----------------------------------------------------------------------

// Inicializa las variables para los mensajes
$mensaje = '';
$error = '';

if (isset($_POST['cambiar_clave'])) {
    $usuario = $_SESSION['usuario'];

    $clave_actual = $_POST['clave_actual'] ?? '';
    $nueva_clave = $_POST['nueva_clave'] ?? '';
    $confirmar_clave = $_POST['confirmar_clave'] ?? '';

    if (empty($clave_actual) || empty($nueva_clave) || empty($confirmar_clave)) {
        $error = "Por favor, complete todos los campos.";
    } elseif ($nueva_clave !== $confirmar_clave) {
        $error = "La nueva contraseña y la confirmación no coinciden.";
    } elseif (strlen($nueva_clave) < 6) {
        $error = "La nueva contraseña debe tener al menos 6 caracteres.";
    } else {
        // Consulta la contraseña actual en la base de datos
        $stmt = $pdo->prepare("SELECT password FROM ventasbasica_usuario WHERE ventasbasica_usuario = ?");
        $stmt->execute([$usuario]);

        if ($row = $stmt->fetch()) {
            // Verifica la contraseña actual con password_verify
            if (password_verify($clave_actual, $row['password'])) {
                // Encripta la nueva contraseña
                $nueva_clave_hash = password_hash($nueva_clave, PASSWORD_DEFAULT);

                $update_stmt = $pdo->prepare("UPDATE ventasbasica_usuario SET password = ? WHERE ventasbasica_usuario = ?");
                if ($update_stmt->execute([$nueva_clave_hash, $usuario])) {
                    // Redirige con mensaje
                    header("Location: logout.php?msg=clave_cambiada");
                    exit();
                } else {
                    $error = "Error al actualizar la contraseña.";
                }
            } else {
                $error = "La contraseña actual es incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
    }
}




// FIN DE CAMBIO DE CLAVE


?>
<?php
include("../db.php");

$buscar = $_GET['buscar'] ?? '';

//para paginacion
$limite = 10; // Productos por página

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$inicio = ($pagina - 1) * $limite;

//para total de datos

$sqlTotal = "
SELECT COUNT(DISTINCT p.id) AS total
FROM ventasbasica_productos p
LEFT JOIN ventasbasica_categorias c
ON p.categoria_id = c.id
WHERE
p.nombre LIKE ?
OR c.nombre LIKE ?
";

$stmt_total = $pdo->prepare($sqlTotal);
$buscar_param = "%$buscar%";
$stmt_total->execute([$buscar_param, $buscar_param]);
$totalRegistros = $stmt_total->fetch()['total'];

$totalPaginas = ceil($totalRegistros / $limite);


// Traemos los productos con su categoría y el stock sumado de los colores activos
$sql_prod = "
SELECT 
    p.*, 
    c.nombre AS categoria, 
    COALESCE(SUM(pc.cantidad),0) AS stock_total
FROM ventasbasica_productos p
LEFT JOIN ventasbasica_categorias c 
    ON p.categoria_id = c.id
LEFT JOIN ventasbasica_producto_colores pc 
    ON p.id = pc.producto_id
    AND pc.estado='activo'
WHERE
    p.nombre LIKE ?
    OR c.nombre LIKE ?
GROUP BY p.id
ORDER BY p.id DESC
LIMIT $inicio, $limite
";
$result = $pdo->prepare($sql_prod);
$result->execute([$buscar_param, $buscar_param]);
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

/* para botones */

@media(max-width:768px){

    .d-flex .btn{

        width:100%;
        font-size:16px;
        padding:12px;

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
            <li><a id="btnCambiarClave" href="#">🔒 Cambiar Clave</a></li>
            <li><a href="logout.php">🚪 Salir</a></li>
        </ul>
    </nav>
</header>
	
	
    
 <!--
INICIO modal *******************************************************************************************************
-->

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 90%;
    max-width: 400px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    position: relative;
    text-align: center;
}

.close {
    color: #aaa;
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
}

.close:hover {
    color: #000;
}
</style>

<!-- Modal -->
<div id="modalCambiarClave" class="modal" style="display:none;">
    <div class="modal-content">
        <span id="cerrarModal" class="close">&times;</span>
        <h3>Cambiar contraseña</h3>
        <form method="POST" action="index.php">
            <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($_SESSION['usuario'] ?? ''); ?>" />
            <div>
                <label>Contraseña actual:</label><br>
                <input type="password" name="clave_actual" required>
            </div>
            <div>
                <label>Nueva contraseña:</label><br>
                <input type="password" name="nueva_clave" required>
            </div>
            <div>
                <label>Confirmar nueva contraseña:</label><br>
                <input type="password" name="confirmar_clave" required>
            </div>
            <br>
            <button style="border-radius: 6px;     font-size: 1rem;     padding: 0.8rem 1.5rem;     cursor: pointer;     border: none;     font-weight: 600;     transition: background-color 0.2s;" type="submit" name="cambiar_clave">Cambiar contraseña</button>
        </form>
    </div>
</div>


	
	

    <section id="productos">
    
    <div class="container mt-4">
  <h2 class="mb-3 text-center">🛠️ Administración de Productos</h2>
<div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-between mb-4">

    <a href="agregar_categoria.php" class="btn btn-success rounded-pill px-4">
        <i class="fa fa-tags"></i> Nueva Categoría
    </a>

    <a href="agregar_producto.php" class="btn btn-primary rounded-pill px-4">
        <i class="fa fa-plus"></i> Nuevo Producto
    </a>

    <a href="agregar_color.php" class="btn btn-danger rounded-pill px-4">
        <i class="fa fa-palette"></i> Nuevo Color
    </a>

    <a href="admin_pedido.php" class="btn btn-info rounded-pill px-4">
        <i class="fa fa-shopping-cart"></i> Ver Pedidos
    </a>

    <a href="envios.php" class="btn btn-info rounded-pill px-4">
        <i class="fas fa-shipping-fast"></i> Envíos
    </a>

</div>


<form method="GET" id="formBuscar" class="row g-2 mb-3">

    <div class="col-md-10">
        <input
            type="text"
            class="form-control"
            name="buscar"
            id="buscar"
            placeholder="Buscar producto..."
            value="<?= htmlspecialchars($buscar) ?>">
    </div>

    <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-primary rounded-pill">
            <i class="fa fa-search"></i> Buscar
        </button>
    </div>

</form>



  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Categoría</th>
        <th>Precio</th>
        <th>Stock</th>
        <th>Talla</th>
        <th>Estado</th>
        <th>Acción</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch()) { ?>
      <tr>
   <td data-label="ID"><?= $row['id'] ?></td>

    <td data-label="Nombre">
        <?= htmlspecialchars($row['nombre']) ?>
    </td>

    <td data-label="Categoría">
        <?= htmlspecialchars($row['categoria']) ?>
    </td>

    <td data-label="Precio">
        Q<?= number_format($row['precio'],2) ?>
    </td>

    <td data-label="Stock">
        <?= $row['stock_total'] ?>
    </td>

     <td data-label="Talla">
        <?= $row['talla'] ?>
    </td>

    <td data-label="Estado">
        <?= $row['estatus'] ?>
    </td>

    <td data-label="Acción">
 <a href="editar_producto.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
          <a href="eliminar_producto.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar producto?')">🗑️</a>


    </td>

       
      </tr>
      <?php } ?>
    </tbody>
  </table>



  <nav class="mt-4">
<ul class="pagination justify-content-center">

<?php if($pagina>1){ ?>

<li class="page-item">

<a class="page-link"
href="?pagina=<?=($pagina-1)?>&buscar=<?=urlencode($buscar)?>">

Anterior

</a>

</li>

<?php } ?>


<?php

for($i=1;$i<=$totalPaginas;$i++){

?>

<li class="page-item <?=($i==$pagina)?'active':''?>">

<a class="page-link"

href="?pagina=<?=$i?>&buscar=<?=urlencode($buscar)?>">

<?=$i?>

</a>

</li>

<?php } ?>


<?php if($pagina<$totalPaginas){ ?>

<li class="page-item">

<a class="page-link"

href="?pagina=<?=($pagina+1)?>&buscar=<?=urlencode($buscar)?>">

Siguiente

</a>

</li>

<?php } ?>

</ul>
</nav>








</div>
    </section>

  



   
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
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById('modalCambiarClave');
    const btnAbrir = document.getElementById('btnCambiarClave');
    const btnCerrar = document.getElementById('cerrarModal');

    btnAbrir.addEventListener('click', function (e) {
        e.preventDefault();
        modal.style.display = 'block';
    });

    btnCerrar.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    // También cerrar si se hace clic fuera del contenido
    window.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>



</body>
</html>