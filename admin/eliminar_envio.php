<?php
session_start();
include("../db.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: envio.php");
    exit();
}

$id = intval($_GET['id']);

// eliminar
$stmt = $pdo->prepare("DELETE FROM ventasbasica_envios WHERE id=?");
$stmt->execute([$id]);
    echo "<script>
window.location='envios.php?ok=eliminado';
</script>";
exit();
?>