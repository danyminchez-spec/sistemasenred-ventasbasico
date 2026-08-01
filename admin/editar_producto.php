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

$id = intval($_GET['id']);
$stmt_p = $pdo->prepare("SELECT * FROM ventasbasica_productos WHERE id=?");
$stmt_p->execute([$id]);
$p = $stmt_p->fetch();

$categorias = $pdo->query("SELECT * FROM ventasbasica_categorias");

// Colores del producto actual
$stmt_colores = $pdo->prepare("
    SELECT pc.*, c.nombre AS color_nombre, c.hex 
    FROM ventasbasica_producto_colores pc
    JOIN ventasbasica_colores c ON pc.color_id = c.id
    WHERE pc.producto_id = ?
");
$stmt_colores->execute([$id]);
$colores_producto = $stmt_colores;

if (isset($_POST['guardar'])) {
    // --- Guardar datos principales ---
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $categoria_id = $_POST['categoria_id'];
    $talla = $_POST['talla'];
    $estado = $_POST['estatus'];

    $imagen = $p['imagen'];
    if (!empty($_FILES['imagen']['name'])) {
        $imagen = 'img_' . time() . '_' . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], "../productos/" . $imagen);
    }

    $stmt = $pdo->prepare("UPDATE ventasbasica_productos SET 
        nombre=?, descripcion=?, precio=?,
        categoria_id=?,  talla=?, estatus=?, imagen=?
        WHERE id=?
    ");
    $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $talla, $estado, $imagen, $id]);

    // --- Actualizar colores del producto ---
    $stmt_del = $pdo->prepare("DELETE FROM ventasbasica_producto_colores WHERE producto_id=?");
    $stmt_del->execute([$id]);

    if (isset($_POST['color_id'])) {
        $stmt_ins = $pdo->prepare("INSERT INTO ventasbasica_producto_colores (producto_id, color_id, cantidad, estado) 
                          VALUES (?, ?, ?, ?)");
        foreach ($_POST['color_id'] as $i => $color_id) {
            $cantidad = intval($_POST['cantidad'][$i]);
            $estado_color = isset($_POST['activo'][$i]) ? 'activo' : 'inactivo';
            $stmt_ins->execute([$id, $color_id, $cantidad, $estado_color]);
        }
    }

   echo "<script>
window.location='index.php';
</script>";
    exit;
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
            <li><a href="logout.php">🚪 Salir</a></li>
        </ul>
    </nav>
</header>
	
	


	
	

    <section id="productos">
    <div class="container mt-4">
 
    <h3>Editar Producto</h3>

<form method="POST" enctype="multipart/form-data">
  <div class="row">
    <div class="col-md-6">
      <label>Nombre</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($p['nombre']) ?>" required>
    </div>
    <div class="col-md-6">
      <label>Categoría</label>
      <select name="categoria_id" class="form-select">
        <?php while ($cat = $categorias->fetch()) { ?>
          <option value="<?= $cat['id'] ?>" <?= ($p['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
            <?= $cat['nombre'] ?>
          </option>
        <?php } ?>
      </select>
    </div>
  </div>

  <div class="col-md-6 mt-3">
    <label>Talla</label>
    <select name="talla" class="form-select" required>
        <option value="" <?= empty($p['talla']) ? 'selected' : '' ?>>
            -- Seleccione talla --
        </option>
        <option value="UNICA" <?= $p['talla']=='UNICA' ? 'selected' : '' ?>>UNICA</option>
        <option value="XS" <?= $p['talla']=='XS' ? 'selected' : '' ?>>XS</option>
        <option value="S" <?= $p['talla']=='S' ? 'selected' : '' ?>>S</option>
        <option value="M" <?= $p['talla']=='M' ? 'selected' : '' ?>>M</option>
        <option value="L" <?= $p['talla']=='L' ? 'selected' : '' ?>>L</option>
        <option value="XL" <?= $p['talla']=='XL' ? 'selected' : '' ?>>XL</option>
        <option value="XXL" <?= $p['talla']=='XXL' ? 'selected' : '' ?>>XXL</option>
    </select>
</div>

  <div class="mb-3 mt-3">
    <label>Descripción</label>
    <textarea name="descripcion" class="form-control"><?= htmlspecialchars($p['descripcion']) ?></textarea>
  </div>

  <div class="row">
    <div class="col-md-4">
      <label>Precio</label>
      <input type="number" step="0.01" name="precio" class="form-control" value="<?= $p['precio'] ?>">
    </div>
    <div class="col-md-4">
      <label>Estado</label>
      <select name="estatus" class="form-select">
        <option value="activo" <?= $p['estatus'] == 'activo' ? 'selected' : '' ?>>Activo</option>
        <option value="inactivo" <?= $p['estatus'] == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
      </select>
    </div>
    <div class="col-md-4">
      <label>Imagen</label>
      <input type="file" name="imagen" class="form-control">
      <?php if ($p['imagen']) { ?>
        <img src="../productos/<?= $p['imagen'] ?>" width="100" class="mt-2 rounded">
      <?php } ?>
    </div>
  </div>

  <hr class="my-4">

  <h5>Colores disponibles</h5>
  <div id="colores-container">
    <?php while ($cp = $colores_producto->fetch()) { ?>
      <div class="row align-items-center mb-2 color-row">
        <div class="col-md-4">
          <select name="color_id[]" class="form-select">
            <?php 
              $colores2 = $pdo->query("SELECT * FROM ventasbasica_colores WHERE activo=1 ORDER BY nombre");
              while ($col = $colores2->fetch()) { ?>
                <option value="<?= $col['id'] ?>" <?= ($col['id'] == $cp['color_id']) ? 'selected' : '' ?>>
                  <?= $col['nombre'] ?>
                </option>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-3">
          <input type="number" name="cantidad[]" class="form-control" placeholder="Cantidad" value="<?= $cp['cantidad'] ?>">
        </div>
        <div class="col-md-2">
          <input type="checkbox" name="activo[]" <?= ($cp['estado']=='activo') ? 'checked' : '' ?>> Activo
        </div>
        <div class="col-md-2">
          <span class="color-preview" style="background: <?= $cp['hex'] ?>"></span>
        </div>
        <div class="col-md-1 text-end">
          <button type="button" class="btn btn-danger btn-sm eliminar-color">X</button>
        </div>
      </div>
    <?php } ?>
  </div>

  <button type="button" class="btn btn-success btn-sm mt-2" id="agregar-color">+ Añadir color</button>

  <button class="btn btn-primary mt-4" type="submit" name="guardar">Guardar Cambios</button>
</form>

<div class="text-center mt-3">
        <a href="index.php" class="btn btn-secondary">⬅️ Volver al catálogo</a>
    </div>
</div>

<script>
// Duplicar fila de color
document.getElementById('agregar-color').addEventListener('click', function() {
  const container = document.getElementById('colores-container');
  const row = document.createElement('div');
  row.classList.add('row', 'align-items-center', 'mb-2', 'color-row');
  row.innerHTML = `
    <div class="col-md-4">
      <select name="color_id[]" class="form-select">
        <?php
        $colores3 = $pdo->query("SELECT * FROM ventasbasica_colores WHERE activo=1 ORDER BY nombre");
        while ($c = $colores3->fetch()) {
          echo '<option value="'.$c['id'].'">'.$c['nombre'].'</option>';
        }
        ?>
      </select>
    </div>
    <div class="col-md-3">
      <input type="number" name="cantidad[]" class="form-control" placeholder="Cantidad">
    </div>
    <div class="col-md-2">
      <input type="checkbox" name="activo[]" checked> Activo
    </div>
    <div class="col-md-1 text-end">
      <button type="button" class="btn btn-danger btn-sm eliminar-color">X</button>
    </div>
  `;
  container.appendChild(row);
});

// Eliminar fila visualmente
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('eliminar-color')) {
    e.target.closest('.color-row').remove();
  }
});
</script>



    


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



</body>
</html>