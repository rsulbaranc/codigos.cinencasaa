<?php
session_start(); // Inicia la sesión para manejar el login

// Importa los valores de configuración (usuario y contraseña)
include 'config-panel.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validar si el nombre de usuario es correcto
    if ($username === USERNAME) {
        // Validar la contraseña usando password_verify
        if (password_verify($password, PASSWORD_HASH)) {
            // Autenticación exitosa, guardamos la sesión
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;

            // Redirige a una página protegida (ejemplo: dashboard.php)
            header("Location: dashboard.php");
            exit();
        } else {
            // Contraseña incorrecta
            echo "Contraseña incorrecta.";
        }
    } else {
        // Usuario incorrecto
        echo "Usuario incorrecto.";
    }
} else {
    // Si el usuario intenta acceder sin usar POST (acceso directo)
    echo "Método no permitido.";
}
?>
