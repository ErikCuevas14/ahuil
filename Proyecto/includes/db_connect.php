<?php
// Configuración de la base de datos
define('DB_SERVER', 'localhost'); // Servidor de la base de datos
define('DB_USERNAME', 'root');   // Usuario de la base de datos
define('DB_PASSWORD', '');       // Contraseña de la base de datos (vacía por defecto en XAMPP/WAMP)
define('DB_NAME', 'ahuil');      // Nombre de la base de datos

// Intentar conectar a la base de datos MySQL
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión
if ($conn->connect_error) {
    die("ERROR: No se pudo conectar a la base de datos. " . $conn->connect_error);
}

// Opcional: Establecer el conjunto de caracteres a UTF-8 para evitar problemas con caracteres especiales
$conn->set_charset("utf8mb4");
?>
