<?php
include("../db.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Seguridad básica para evitar inyección SQL

    // Primero eliminar colores asociados al producto (por si no tienes ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM ventasbasica_producto_colores WHERE producto_id = ?");
    $stmt->execute([$id]);

    // Luego eliminar el producto
    $stmt2 = $pdo->prepare("DELETE FROM ventasbasica_productos WHERE id = ?");
    $stmt2->execute([$id]);

   // header("Location: index.php");
    
       echo "<script>
        window.location='index.php?ok=eliminado';
        </script>";

    exit;
} else {
    echo "Error: ID de producto no especificado.";
}
?>
