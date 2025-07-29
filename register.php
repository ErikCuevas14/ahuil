<?php
session_start();
require_once 'includes/db_connect.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ahuil - Registro</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="shortcut icon" href="RECURSOS/imagen.ico"/>
</head>
<body>
    <div class="auth-container card">
        <h2 class="auth-title">Crear Cuenta</h2>
        <form id="registerForm" action="auth_process.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="username" class="form-label">Nombre de Usuario</label>
                <input type="text" id="username" name="username" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword" class="form-label">Confirmar Contraseña</label>
                <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
            <button type="button" onclick="history.back()" class="btn btn-outline btn-block">Volver</button>
            <div class="error-message" id="errorMessage"></div>
            <input type="hidden" name="action" value="register">
        </form>
        <div class="auth-link">
            ¿Ya tienes cuenta? <a href="login.php" class="auth-link-text">Inicia sesión aquí</a>
        </div>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>