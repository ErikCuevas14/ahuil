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
    <title>Ahuil - Iniciar Sesión</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Iniciar Sesión</h2>
        <form id="loginForm" action="auth_process.php" method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
            <button onclick="history.back()">Volver</button>
            <div class="error-message" id="errorMessage"></div>
            <input type="hidden" name="action" value="login">
        </form>
        <div class="auth-link">
            ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
        </div>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>
