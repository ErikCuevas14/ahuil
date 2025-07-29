<?php
session_start();
require_once 'includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'register':
            // 'username' del formulario se mapea a 'apodo' en la DB
            $apodo = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            // 'password' del formulario se mapea a 'contraseña' en la DB
            $contrasena = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';

            if (empty($apodo) || empty($email) || empty($contrasena) || empty($confirmPassword)) {
                $response['message'] = 'Todos los campos son obligatorios.';
                break;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = 'El formato del correo electrónico no es válido.';
                break;
            }

            if ($contrasena !== $confirmPassword) {
                $response['message'] = 'Las contraseñas no coinciden.';
                break;
            }

            if (strlen($contrasena) < 6) {
                $response['message'] = 'La contraseña debe tener al menos 6 caracteres.';
                break;
            }

            // Verificar si el email ya existe en la tabla 'usuarios'
            $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            if (!$stmt) {
                $response['message'] = 'Error de preparación de la consulta: ' . $conn->error;
                break;
            }
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $response['message'] = 'Este correo ya está registrado.';
                $stmt->close();
                break;
            }
            $stmt->close();

            // Hashear la contraseña para la columna 'contraseña'
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

            // Insertar nuevo usuario en la tabla 'usuarios'
            // Columnas: apodo, email, contraseña
            $stmt = $conn->prepare("INSERT INTO usuarios (apodo, email, contraseña) VALUES (?, ?, ?)");
            if (!$stmt) {
                $response['message'] = 'Error de preparación de la consulta: ' . $conn->error;
                break;
            }
            $stmt->bind_param("sss", $apodo, $email, $contrasena_hash);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Registro exitoso.';
                // Iniciar sesión automáticamente al registrarse
                $_SESSION['user_id'] = $conn->insert_id; // id_usuario
                $_SESSION['username'] = $apodo; // apodo
                $_SESSION['email'] = $email;
            } else {
                $response['message'] = 'Error al registrar el usuario: ' . $stmt->error;
            }
            $stmt->close();
            break;

        case 'login':
            $email = trim($_POST['email'] ?? '');
            // 'password' del formulario se mapea a 'contraseña' en la DB
            $contrasena = $_POST['password'] ?? '';

            if (empty($email) || empty($contrasena)) {
                $response['message'] = 'Todos los campos son obligatorios.';
                break;
            }

            // Buscar usuario por email en la tabla 'usuarios'
            // Columnas: id_usuario, apodo, contraseña
            $stmt = $conn->prepare("SELECT id_usuario, apodo, contraseña FROM usuarios WHERE email = ?");
            if (!$stmt) {
                $response['message'] = 'Error de preparación de la consulta: ' . $conn->error;
                break;
            }
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id_usuario, $apodo, $contrasena_hash); // id_usuario, apodo, contraseña
                $stmt->fetch();

                // Verificar la contraseña hasheada
                if (password_verify($contrasena, $contrasena_hash)) {
                    $response['success'] = true;
                    $response['message'] = 'Inicio de sesión exitoso.';
                    $_SESSION['user_id'] = $id_usuario; // id_usuario
                    $_SESSION['username'] = $apodo; // apodo
                    $_SESSION['email'] = $email;
                } else {
                    $response['message'] = 'Credenciales incorrectas.';
                }
            } else {
                $response['message'] = 'Credenciales incorrectas.';
            }
            $stmt->close();
            break;

        case 'logout':
            session_unset();
            session_destroy();
            $response['success'] = true;
            $response['message'] = 'Sesión cerrada.';
            break;

        default:
            $response['message'] = 'Acción no válida.';
            break;
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

$conn->close();
echo json_encode($response);
exit();
?>
