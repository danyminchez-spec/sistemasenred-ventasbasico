<?php
session_start();
ob_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

if(isset($_POST['guardar'])){

    $fecha = $_POST['fecha'];
    $numero_guia = $_POST['numero_guia'];
    $cliente = $_POST['cliente'];
    $estado = $_POST['estado'];
    $departamento = $_POST['departamento'];
    $municipio = $_POST['municipio'];
    $total = $_POST['total'];
    $cantidad = $_POST['cantidad'];
    $inversion = $_POST['inversion'];
    $ganancia = $_POST['ganancia'];
    $red_social = $_POST['red_social'];
    $empresa = $_POST['empresa'];

    $sql="INSERT INTO ventasbasica_envios
    (
        fecha,
        numero_guia,
        cliente,
        estado,
        departamento,
        municipio,
        total,
        cantidad_prendas,
        inversion,
        ganancia,
        red_social,
        empresa
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
    )";

    $stmt = $pdo->prepare($sql);
    try {
        if($stmt->execute([$fecha, $numero_guia, $cliente, $estado, $departamento, $municipio, $total, $cantidad, $inversion, $ganancia, $red_social, $empresa])){
           echo "<script>
            window.location='envios.php?ok=guardado';
          </script>";
          exit;
        }
    } catch (\PDOException $e) {
        echo $e->getMessage();
    }

}
?>