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
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Crear Cuenta</h2>
        <form id="registerForm" action="auth_process.php" method="POST">
            <div class="form-group">
                <label for="username">Nombre de Usuario</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirmar Contraseña</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>
            </div>
            <button type="submit">Registrarse</button>
            <button onclick="history.back()">Volver</button>
            <div class="error-message" id="errorMessage"></div>
            <input type="hidden" name="action" value="register">
        </form>
        <div class="auth-link">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
        </div>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>

