<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start(); // Inicia la sesión para poder destruirla
// Guarda el mensaje de la URL si existe antes de destruir la sesión
$redirect_url = "login.php";
if (isset($_GET['msg']) && $_GET['msg'] === 'clave_cambiada') {
    $redirect_url .= "?msg=clave_cambiada";
}
session_unset(); // Elimina todas las variables de sesión
session_destroy(); // Destruye la sesión
header("Location: " . $redirect_url); // Redirige al usuario a la página de login con el parámetro si es necesari
exit(); // Asegura que el script se detenga aquí
