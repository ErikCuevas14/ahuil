<?php
session_start();
require_once 'includes/db_connect.php';

// Mostrar mensaje si la cuenta fue eliminada
if (isset($_GET['account_deleted']) && $_GET['account_deleted'] == 1) {
    echo '<div class="alert alert-success">Tu cuenta ha sido eliminada exitosamente.</div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ahuil - Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="shortcut icon" href="RECURSOS/imagen.ico"/>
</head>
<body>
    <header class="site-header">
        <div class="navbar container">
            <a href="index.php" class="navbar-brand">
                <img src="RECURSOS/logo.png" alt="Logo Ahuil" class="logo-image">
            </a>
            <div class="auth-buttons">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                    <a href="register.php" class="btn btn-secondary">Crear Cuenta</a>
                <?php else: ?>
                    <div class="user-info" id="userInfo">
                        <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="profile.php" class="btn btn-outline btn-sm">Mi Perfil</a>
                        <button id="logoutBtn" class="btn btn-danger btn-sm">Cerrar Sesión</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="main container">
        <!-- Carrusel -->
        <section class="carousel-section">
            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                </div>
                <div class="carousel-inner rounded">
                    <div class="carousel-item active">
                        <iframe class="d-block w-100" src="https://drive.google.com/file/d/1MNmzY0Z-DJDaJVO_SHarUJc8rn16bbdq/preview" allow="autoplay" allowfullscreen></iframe>
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="RECURSOS/Cap1.png" alt="First slide">
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="RECURSOS/Cap2.png" alt="Second slide">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </section>

        <!-- Sección de bienvenida -->
        <section class="welcome-section card mt-4">
            <h2 class="section-title">Bienvenido a Ahuil</h2>
            
            <div class="table-responsive">
                <table class="table-clean">
                    <thead>
                        <tr>
                            <th>Requisito</th>
                            <th>Mínimo</th>
                            <th>Máximo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Espacio en disco</td>
                            <td>500 MB</td>
                            <td>2 GB</td>
                        </tr>
                        <tr>
                            <td>Memoria RAM</td>
                            <td>2 GB</td>
                            <td>8 GB</td>
                        </tr>
                        <tr>
                            <td>Procesador</td>
                            <td>Intel Core i3 o equivalente</td>
                            <td>Intel Core i7 o superior</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="cta-buttons mt-3">
                <a href="https://drive.google.com/drive/folders/1Rg9OsdWfsXjXWYXfEhXHC6GpXir8a7bE?usp=sharing" target="_blank" class="btn btn-accent">Descargar beta</a>
                <a href="https://www.paypal.com" target="_blank" class="btn btn-outline">Donación</a>
            </div>
        </section>

        <!-- Sección de comentarios -->
        <section class="comments-section card mt-4">
            <h2 class="section-title">Comentarios</h2>
            <div id="comments" class="comments-list">
                <!-- Los comentarios se cargarán aquí mediante JavaScript -->
            </div>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="comment-form mt-3">
                    <textarea id="commentInput" class="form-control" placeholder="Escribe un comentario..."></textarea>
                    <button id="submitComment" class="btn btn-primary mt-2">Enviar Comentario</button>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-3">
                    <a href="login.php" class="btn-link">Inicia sesión</a> para dejar un comentario.
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Ahuil. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/script.js"></script>
</body>
</html>