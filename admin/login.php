<?php
// 🔧 RUTA PERSISTENTE DE SESIONES EN AZURE
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
//include 'dbcon.php'; // Aquí se importa la conexión MySQLi desde dbcon.php
include("../db.php");




$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $sql = "SELECT * FROM ventasbasica_usuario WHERE ventasbasica_usuario = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['usuario'] = $user['ventasbasica_usuario'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['departamento'] = $user['departamento'];

            $anio = date("Y");
            $depto = $user['departamento'];

            echo "<script>
            window.location='index.php';
            </script>";
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>


<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Wow Amy</title>
    <link rel="shortcut icon" type="image/x-icon" href="img/gob.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

          body{
    background:
    /* Flores grandes */
    radial-gradient(circle at 8% 18%, rgba(255,255,255,.35) 0 18px, transparent 19px),
    radial-gradient(circle at 10% 15%, rgba(255,255,255,.25) 0 4px, transparent 5px),

    radial-gradient(circle at 88% 12%, rgba(255,255,255,.35) 0 22px, transparent 23px),
    radial-gradient(circle at 84% 78%, rgba(255,255,255,.30) 0 24px, transparent 25px),

    radial-gradient(circle at 15% 82%, rgba(255,255,255,.28) 0 20px, transparent 21px),

    /* Flores medianas */
    radial-gradient(circle at 25% 25%, rgba(255,255,255,.18) 0 12px, transparent 13px),
    radial-gradient(circle at 70% 28%, rgba(255,255,255,.18) 0 14px, transparent 15px),
    radial-gradient(circle at 58% 88%, rgba(255,255,255,.18) 0 12px, transparent 13px),
    radial-gradient(circle at 92% 35%, rgba(255,255,255,.18) 0 10px, transparent 11px),

    /* Puntitos */
    radial-gradient(circle at 30% 70%, rgba(255,255,255,.45) 2px, transparent 3px),
    radial-gradient(circle at 82% 55%, rgba(255,255,255,.35) 2px, transparent 3px),
    radial-gradient(circle at 55% 15%, rgba(255,255,255,.30) 2px, transparent 3px),

    linear-gradient(135deg,#ff9acb 0%,#ff6fa5 35%,#f857a6 70%,#ff4f8b 100%);

    background-attachment: fixed;

    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    padding:20px;
}

        .login-box {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            padding: 30px 35px;
            border-radius: 18px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.18);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-box img {
            max-width: 80%;
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

       .button {
    width: 100%;
    background: linear-gradient(135deg, #ff6fa5, #ff4f8b);
    color: white;
    padding: 12px;
    border: none;
    border-radius: 30px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all .3s ease;
    box-shadow: 0 8px 20px rgba(255, 79, 139, .35);
}

.button:hover {
    transform: translateY(-2px);
    background: linear-gradient(135deg, #ff5d99, #f43f7d);
    box-shadow: 0 12px 25px rgba(255, 79, 139, .45);
}

.button:active {
    transform: scale(.98);
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
    </style>
</head>
<body>

<div class="login-box">
    <form action="login.php" method="POST" autocomplete="off">
        <img src="../img/logo.png" alt="Logo CNC">
        <h3>Administracion - WowAmy</h3>
 <?php
        $mensaje_login = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'clave_cambiada') {
    $mensaje_login = '<p style="color: green; font-weight: bold; text-align: center; background-color: #e6ffe6; padding: 10px; border-radius: 5px;">¡Contraseña cambiada exitosamente! Por favor, inicia sesión con tu nueva contraseña.</p>';
    echo $mensaje_login;
}
 ?>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="field">
            <label class="label">Usuario:</label>
            <input class="input" type="text" name="usuario" pattern="[a-zA-Z0-9]{4,30}" maxlength="30" required>
        </div>

        <div class="field">
            <label class="label">Contraseña:</label>
            <input class="input" type="password" name="password" pattern="[a-zA-Z0-9$@.-]{7,100}" maxlength="100" required>
        </div>

        <button type="submit" class="button">Iniciar sesión</button>
         <!--       <p style="margin-top: 10px;"><a href="register.php">Registrate</a></p> -->
    </form>
</div>

</body>
</html>
