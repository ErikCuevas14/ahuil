<!-- File: Proyecto/index.php -->
<?php
session_start();
require_once 'includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <title>Ahuil</title>
</head>
<body>
    <header>
        <div class="navbar">
            <a href="index.php"><h1>Ahuil</h1></a>
            <div class="auth-buttons" id="authButtons">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <button id="loginBtn">Iniciar Sesión</button>
                    <button id="registerBtn">Crear Cuenta</button>
                <?php else: ?>
                    <div class="user-info" id="userInfo" style="display: flex;">
                        <span id="usernameDisplay"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <!-- INICIO DE LA MODIFICACIÓN -->
                        <a href="profile.php" class="profile-link">Mi Perfil</a>
                        <!-- FIN DE LA MODIFICACIÓN -->
                        <button id="logoutBtn">Cerrar Sesión</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>


    <main>
        <div class="container mt-5">
            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                <ol class="carousel-indicators">
                    <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="" class="active"></li>
                    <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to=""></li>
                    <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to=""></li>
                </ol>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <video controls>
                        <source src="videos/video.mp4" type="video/mp4">
                        Tu navegador no soporta el video.
                    </video>
                </div>
                <div class="carousel-item">
                    <img class="d-block w-100" src="RECURSOS/cap1.png" alt="Second slide">
                </div>
                <div class="carousel-item">
                    <img class="d-block w-100" src="RECURSOS/cap2.png" alt="Third slide">
                </div>
                
            </div>
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


        <section class="comments-section">
            <h2>Comentarios</h2>
            <div id="comments"></div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <textarea id="commentInput" placeholder="Escribe un comentario..."></textarea>
                <button id="submitComment">Enviar Comentario</button>
            <?php else: ?>
                <p>Inicia sesión para dejar un comentario.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 Ahuil. Todos los derechos reservados.</p>
    </footer>

    <script src="js/auth.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
