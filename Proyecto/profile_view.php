<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ahuil - Mi Perfil</title>
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/style.css"> <!-- Para el navbar y footer -->
</head>
<body>
    <main>
        <div class="profile-container">
            <h2>Mi Perfil</h2>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($user_data): ?>
                <?php if (!$edit_mode): // Modo de visualización ?>
                    <div class="form-group">
                        <label>Nombre de Usuario:</label>
                        <p><?php echo htmlspecialchars($user_data['apodo']); ?></p>
                    </div>
                    <div class="form-group">
                        <label>Correo Electrónico:</label>
                        <p><?php echo htmlspecialchars($user_data['email']); ?></p>
                    </div>
                    <form action="profile.php" method="POST">
                        <button type="submit" name="action" value="enter_edit_mode" class="button">Modificar Datos</button>
                        <a href="index.php" class="button">Volver</a>
                    </form>
                <?php else: // Modo de edición ?>
                    <form id="profileForm" action="profile.php" method="POST">
                        <div class="form-group">
                            <label for="username">Nombre de Usuario:</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['apodo']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Correo Electrónico:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                        </div>

                        <h3>Cambiar Contraseña (opcional)</h3>
                        <p>Deja los campos de contraseña vacíos si no deseas cambiarla.</p>
                        <div class="form-group">
                            <label for="current_password">Contraseña Actual:</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">Nueva Contraseña:</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        <div class="form-group">
                            <label for="confirm_new_password">Confirmar Nueva Contraseña:</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password">
                        </div>

                        <button type="submit" name="action" value="update_profile" class="button">Guardar Cambios</button>
                        <button type="button" onclick="window.location.href='profile.php'" class="button">Cancelar</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <p>No se pudieron cargar los datos del usuario.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Ahuil. Todos los derechos reservados.</p>
    </footer>

    <script src="js/auth.js"></script>
</body>
</html>
