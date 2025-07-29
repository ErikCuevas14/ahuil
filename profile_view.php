<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ahuil - Mi Perfil</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="shortcut icon" href="RECURSOS/imagen.ico"/>
</head>
<body>
    <main class="profile-main-container">
        <div class="profile-card">
            <h2 class="profile-title">Mi Perfil</h2>

            <?php if ($message): ?>
                <div class="alert-message alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($user_data): ?>
                <?php if (!$edit_mode && !$delete_mode): ?>
                    <!-- Modo visualización normal -->
                    <div class="profile-info-section">
                        <div class="profile-info-group">
                            <span class="profile-info-label">Nombre de Usuario:</span>
                            <p class="profile-info-value"><?php echo htmlspecialchars($user_data['apodo']); ?></p>
                        </div>
                        <div class="profile-info-group">
                            <span class="profile-info-label">Correo Electrónico:</span>
                            <p class="profile-info-value"><?php echo htmlspecialchars($user_data['email']); ?></p>
                        </div>
                    </div>
                    
                    <div class="profile-actions">
                        <form action="profile.php" method="POST" class="profile-action-form">
                            <button type="submit" name="action" value="enter_edit_mode" class="btn btn-primary">Modificar Datos</button>
                            <button type="submit" name="action" value="enter_delete_mode" class="btn btn-danger">Eliminar Cuenta</button>
                            <a href="index.php" class="btn btn-outline">Volver</a>
                        </form>
                    </div>

                <?php elseif ($delete_mode): ?>
                    <!-- Sección de confirmación de eliminación -->
                    <div class="delete-confirmation-section">
                        <h3 class="delete-confirmation-title">Confirmar Eliminación de Cuenta</h3>
                        <div class="delete-warning-message">
                            <p>¡ADVERTENCIA! Esta acción no se puede deshacer.</p>
                            <p>Todos tus datos y comentarios serán eliminados permanentemente.</p>
                        </div>
                        
                        <form action="profile.php" method="POST" class="delete-confirmation-form">
                            <div class="form-group">
                                <label for="delete_password" class="form-label">Ingresa tu contraseña para confirmar:</label>
                                <input type="password" id="delete_password" name="delete_password" class="form-input">
                            </div>
                            
                            <div class="delete-confirmation-actions">
                                <button type="submit" name="action" value="confirm_delete" class="btn btn-danger">Confirmar Eliminación</button>
                                <a href="profile.php" class="btn btn-cancel">Cancelar</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Modo edición de perfil -->
                    <form action="profile.php" method="POST" class="profile-edit-form">
                        <div class="form-group">
                            <label for="username" class="form-label">Nombre de Usuario:</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['apodo']); ?>" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">Correo Electrónico:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" class="form-input" required>
                        </div>

                        <h3 class="section-title">Cambiar Contraseña</h3>
                        <p class="section-subtitle">Deja los campos vacíos si no deseas cambiarla</p>
                        
                        <div class="form-group">
                            <label for="current_password" class="form-label">Contraseña Actual:</label>
                            <input type="password" id="current_password" name="current_password" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="new_password" class="form-label">Nueva Contraseña:</label>
                            <input type="password" id="new_password" name="new_password" class="form-input">
                        </div>
                        <div class="form-group">
                            <label for="confirm_new_password" class="form-label">Confirmar Nueva Contraseña:</label>
                            <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-input">
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="action" value="update_profile" class="btn btn-primary">Guardar Cambios</button>
                            <a href="profile.php" class="btn btn-secondary-action">Cancelar</a>
                        </div>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <p class="error-message">No se pudieron cargar los datos del usuario.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="js/auth.js"></script>
</body>
</html>