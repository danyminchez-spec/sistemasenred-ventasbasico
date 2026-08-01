<?php
session_start();
include '../db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$usuario = $_SESSION['usuario'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $clave_actual = trim($_POST['clave_actual']);
    $nueva_clave = trim($_POST['nueva_clave']);
    $confirmar_clave = trim($_POST['confirmar_clave']);

    if ($nueva_clave !== $confirmar_clave) {
        $mensaje = "La nueva contraseña y su confirmación no coinciden.";
    } else {
        // Obtener el hash actual
        $sql = "SELECT password FROM ventasbasica_usuario WHERE ventasbasica_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario]);
        $fila = $stmt->fetch();

        if ($fila) {
            $hash_actual = $fila['password'];

            // Si NO usas password_hash, reemplaza esta línea por comparación simple
            if (password_verify($clave_actual, $hash_actual)) {
                $nuevo_hash = password_hash($nueva_clave, PASSWORD_DEFAULT);
                $update = "UPDATE ventasbasica_usuario SET password = ? WHERE ventasbasica_usuario = ?";
                $stmt = $pdo->prepare($update);

                if ($stmt->execute([$nuevo_hash, $usuario])) {
                    $mensaje = "Contraseña actualizada correctamente.";
                } else {
                    $mensaje = "Error al actualizar la contraseña.";
                }
            } else {
                $mensaje = "La contraseña actual no es correcta.";
            }
        } else {
            $mensaje = "Usuario no encontrado.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            max-width: 400px;
            width: 100%;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .field {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .button {
            width: 100%;
            padding: 12px;
            background-color: #209CEE;
            color: white;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
        }

        .mensaje {
            margin-bottom: 15px;
            color: red;
        }

        .success {
            color: green;
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Cambiar contraseña</h2>

    <?php if ($mensaje): ?>
        <div class="mensaje <?php echo (strpos($mensaje, 'correctamente') !== false) ? 'success' : ''; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="cambiar_clave.php">
        <div class="field">
            <label>Contraseña actual:</label>
            <input type="password" name="clave_actual" required>
        </div>

        <div class="field">
            <label>Nueva contraseña:</label>
            <input type="password" name="nueva_clave" pattern="[a-zA-Z0-9$@.-]{7,100}" required>
        </div>

        <div class="field">
            <label>Confirmar nueva contraseña:</label>
            <input type="password" name="confirmar_clave" required>
        </div>

        <button type="submit" class="button">Actualizar contraseña</button>
    </form>
</div>

</body>
</html>
