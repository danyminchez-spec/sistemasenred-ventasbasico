<?php
session_start();
include("../db.php");// tu conexión a la base de datos

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $departamento = trim($_POST['departamento'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $documento = trim($_POST['documento'] ?? '');

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Validar si el usuario ya existe
    $sql_check = "SELECT * FROM ventasbasica_usuario WHERE ventasbasica_usuario = ? LIMIT 1";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$usuario]);

    if ($stmt_check->rowCount() > 0) {
        $error = "El nombre de usuario ya está en uso. Elige otro.";
    } else {
        // Insertar nuevo usuario (usa password_hash si quieres seguridad real)
      $sql_insert = "INSERT INTO ventasbasica_usuario (ventasbasica_usuario, nombre, departamento, password, dpi, correo, fecha_creacion)
               VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt_insert = $pdo->prepare($sql_insert);
        if ($stmt_insert->execute([$usuario, $nombre, $departamento, $password_hash, $documento, $correo])) {
            $success = "Usuario registrado exitosamente.";
        } else {
            $error = "Ocurrió un error al registrar. Intenta nuevamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Wow Amy</title>
    <link rel="shortcut icon" href="img/gob.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #ff9acb 0%, #ff6fa5 35%, #f857a6 70%, #ff4f8b 100%);
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

       .login-box {
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(10px);
    padding: 30px 35px;
    border-radius: 18px;
    box-shadow: 0 20px 45px rgba(0,0,0,.18);
    width: 100%;
    max-width: 430px;
    text-align: center;
}

        .login-box img {
            max-width: 50%;
            margin-bottom: 15px;
        }

        .login-box h3 {
            margin-bottom: 25px;
            font-weight: 600;
            color: #333;
            font-size: 20px;
        }

        .field {
            margin-bottom: 20px;
            text-align: left;
        }

        .label {
            margin-bottom: 6px;
            font-weight: 500;
            display: block;
            color: #444;
        }

        .input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }

        .input:focus {
            border-color: #209CEE;
            outline: none;
        }

        .button{
    width:100%;
    background:linear-gradient(135deg,#ff6fa5,#ff4f8b);
    color:#fff;
    padding:12px;
    border:none;
    border-radius:30px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:.3s;
    box-shadow:0 8px 20px rgba(255,79,139,.35);
}

.button:hover{
    transform:translateY(-2px);
    background:linear-gradient(135deg,#ff5d99,#f43f7d);
    box-shadow:0 12px 25px rgba(255,79,139,.45);
}

.button:active{
    transform:scale(.98);
}

        .error {
            color: red;
            margin-bottom: 15px;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-box {
                padding: 25px 20px;
            }

            .login-box img {
                max-width: 100%;
                height: auto;
            }

            .login-box h3 {
                font-size: 18px;
            }

            .button {
                font-size: 15px;
                padding: 10px;
            }
        }


        .select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    background-color: white;
    color: #333;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='16'%20height='16'%20fill='gray'%20class='bi%20bi-chevron-down'%20viewBox='0%200%2016%2016'%3E%3Cpath%20fill-rule='evenodd'%20d='M1.646%204.646a.5.5%200%200%201%20.708%200L8%2010.293l5.646-5.647a.5.5%200%200%201%20.708.708l-6%206a.5.5%200%200%201-.708%200l-6-6a.5.5%200%200%201%200-.708z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px 16px;
}

.select:focus {
    border-color: #209CEE;
    outline: none;
}

    </style>
</head>
<body>

<div class="login-box">
    <form action="register.php" method="POST" autocomplete="off">
        <img src="../img/logo.png" alt="WowAmy">
        <h3>Registro de nuevo usuario</h3>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <p style="color: green; font-weight: bold;"><?php echo $success; ?></p>
        <?php endif; ?>

        <div class="field">
            <label class="label">Usuario:</label>
            <input class="input" type="text" name="usuario" pattern="[a-zA-Z0-9]{4,30}" maxlength="30" required>
        </div>

        <div class="field">
            <label class="label">Nombre completo:</label>
            <input class="input" type="text" name="nombre" maxlength="100" required>
        </div>

       <div class="field">
    <label class="label" for="departamento">Departamento:</label>
    <select name="departamento" id="departamento" class="select" required>
    <option value="">-- Selecciona --</option>
    <option value="1">Guatemala</option>
    <option value="2">El Progreso</option>
    <option value="3">Sacatepéquez</option>
    <option value="4">Chimaltenango</option>
    <option value="5">Escuintla</option>
    <option value="6">Santa Rosa</option>
    <option value="7">Sololá</option>
    <option value="8">Totonicapán</option>
    <option value="9">Quetzaltenango</option>
    <option value="10">Suchitepéquez</option>
    <option value="11">Retalhuleu</option>
    <option value="12">San Marcos</option>
    <option value="13">Huehuetenango</option>
    <option value="14">Quiché</option>
    <option value="15">Baja Verapaz</option>
    <option value="16">Alta Verapaz</option>
    <option value="17">Petén</option>
    <option value="18">Izabal</option>
    <option value="19">Zacapa</option>
    <option value="20">Chiquimula</option>
    <option value="21">Jalapa</option>
    <option value="22">Jutiapa</option>
</select>
</div>

        <div class="field">
            <label class="label">Correo electrónico:</label>
            <input class="input" type="email" name="correo" maxlength="100" required>
        </div>

        <div class="field">
            <label class="label">Documento de Identificación:</label>
            <input class="input" type="text" name="documento" pattern="[0-9]{6,20}" maxlength="20" required>
        </div>

        <div class="field">
            <label class="label">Contraseña:</label>
            <input class="input" type="password" name="password" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required>
        </div>

        <button type="submit" class="button">Registrarse</button>
        <p style="margin-top: 10px;"><a href="login.php">¿Ya tienes cuenta? Inicia sesión</a></p>
    </form>
</div>

</body>
</html>
