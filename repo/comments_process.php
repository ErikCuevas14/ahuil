<?php
session_start();
require_once 'includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_comment': // Para comentarios de nivel superior
            if (!isset($_SESSION['user_id'])) {
                $response['message'] = 'Debes iniciar sesión para comentar.';
                break;
            }

            $contenido = trim($_POST['comment_text'] ?? '');
            $id_usuario = $_SESSION['user_id'];

            if (empty($contenido)) {
                $response['message'] = 'El comentario no puede estar vacío.';
                break;
            }

            if (strlen($contenido) > 500) {
                $response['message'] = 'El comentario es demasiado largo (máximo 500 caracteres).';
                break;
            }

            // Insertar nuevo comentario en la tabla 'comentarios'
            // Columnas: id_usuario, contenido. id_comentario_padre se deja NULL por defecto
            $stmt = $conn->prepare("INSERT INTO comentarios (id_usuario, contenido) VALUES (?, ?)");
            if (!$stmt) {
                $response['message'] = 'Error de preparación de la consulta: ' . $conn->error;
                break;
            }
            $stmt->bind_param("is", $id_usuario, $contenido);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Comentario añadido exitosamente.';
                $response['comment'] = [
                    'id_comentario' => $conn->insert_id, // Necesario para futuras respuestas a este comentario
                    'username' => $_SESSION['username'],
                    'comment_text' => htmlspecialchars($contenido),
                    'created_at' => date('Y-m-d H:i:s'), // Usar la fecha actual para la respuesta
                    'parent_comment_id' => null // Es un comentario de nivel superior
                ];
            } else {
                $response['message'] = 'Error al añadir el comentario: ' . $stmt->error;
            }
            $stmt->close();
            break;

        case 'add_reply': // Para respuestas a comentarios existentes
            if (!isset($_SESSION['user_id'])) {
                $response['message'] = 'Debes iniciar sesión para responder.';
                break;
            }

            $contenido = trim($_POST['reply_text'] ?? '');
            $id_usuario = $_SESSION['user_id'];
            $id_comentario_padre = $_POST['parent_comment_id'] ?? null;

            if (empty($contenido)) {
                $response['message'] = 'La respuesta no puede estar vacía.';
                break;
            }

            if (strlen($contenido) > 500) {
                $response['message'] = 'La respuesta es demasiado larga (máximo 500 caracteres).';
                break;
            }

            // Validar que id_comentario_padre sea un número entero y exista
            if (!is_numeric($id_comentario_padre) || $id_comentario_padre <= 0) {
                $response['message'] = 'ID de comentario padre inválido.';
                break;
            }

            // Insertar nueva respuesta en la tabla 'comentarios'
            // Columnas: id_usuario, contenido, id_comentario_padre
            $stmt = $conn->prepare("INSERT INTO comentarios (id_usuario, contenido, id_comentario_padre) VALUES (?, ?, ?)");
            if (!$stmt) {
                $response['message'] = 'Error de preparación de la consulta: ' . $conn->error;
                break;
            }
            $stmt->bind_param("isi", $id_usuario, $contenido, $id_comentario_padre);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Respuesta añadida exitosamente.';
                $response['comment'] = [
                    'id_comentario' => $conn->insert_id,
                    'username' => $_SESSION['username'],
                    'comment_text' => htmlspecialchars($contenido),
                    'created_at' => date('Y-m-d H:i:s'),
                    'parent_comment_id' => (int)$id_comentario_padre // Asegurarse de que sea un entero
                ];
            } else {
                $response['message'] = 'Error al añadir la respuesta: ' . $stmt->error;
            }
            $stmt->close();
            break;

        default:
            $response['message'] = 'Acción POST no válida.';
            break;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'get_comments') {
        $comments_flat = [];
        // Seleccionar todos los campos necesarios, incluyendo id_comentario y id_comentario_padre
        // Columnas: c.id_comentario, c.contenido, c.fecha, u.apodo, c.id_comentario_padre
        $sql = "SELECT c.id_comentario, c.contenido, c.fecha, u.apodo, c.id_comentario_padre
                FROM comentarios c
                JOIN usuarios u ON c.id_usuario = u.id_usuario
                ORDER BY c.fecha ASC"; // Ordenar por fecha ascendente para construir la jerarquía

        $result = $conn->query($sql);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $comment_data = [
                    'id_comentario' => (int)$row['id_comentario'],
                    'comment_text' => htmlspecialchars($row['contenido']),
                    'created_at' => $row['fecha'],
                    'username' => $row['apodo'],
                    'parent_comment_id' => $row['id_comentario_padre'] ? (int)$row['id_comentario_padre'] : null,
                    'replies' => [] // Para almacenar las respuestas anidadas
                ];
                $comments_flat[$row['id_comentario']] = $comment_data;
            }

            $comments_tree = [];
            foreach ($comments_flat as $id => $comment) {
                if ($comment['parent_comment_id'] === null) {
                    // Es un comentario de nivel superior
                    $comments_tree[] = &$comments_flat[$id]; // Usar referencia para poder añadir respuestas
                } else {
                    // Es una respuesta, añadirla a su padre
                    if (isset($comments_flat[$comment['parent_comment_id']])) {
                        $comments_flat[$comment['parent_comment_id']]['replies'][] = &$comments_flat[$id];
                    }
                }
            }

            // Opcional: Ordenar los comentarios de nivel superior por fecha descendente para mostrarlos más recientes primero
            usort($comments_tree, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            $response['success'] = true;
            $response['comments'] = $comments_tree;
        } else {
            $response['message'] = 'Error al obtener comentarios: ' . $conn->error;
        }
    } else {
        $response['message'] = 'Acción GET no válida.';
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

$conn->close();
echo json_encode($response);
exit();
?><?php
session_start();
require_once 'includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_comment': // Para comentarios de nivel superior
            if (!isset($_SESSION['user_id'])) {
                $response['message'] = 'Debes iniciar sesión para comentar.';
                break;
            }

            $contenido = trim($_POST['comment_text'] ?? '');
            $id_usuario = $_SESSION['user_id'];

            if (empty($contenido)) {
                $response['message'] = 'El comentario no puede estar vacío.';
                break;
            }

            if (strlen($contenido) > 500) {
                $response['message'] = 'El comentario es demasiado largo (máximo 500 caracteres).';
                break;
            }

            // Insertar nuevo comentario en la tabla 'comentarios'
            // Columnas: id_usuario, contenido. id_comentario_padre se deja NULL por defecto
            $stmt = $conn->prepare("INSERT INTO comentarios (id_usuario, contenido) VALUES (?, ?)");
            if (!$stmt) {
                $response['message'] = 'Error de preparación de la consulta: ' . $conn->error;
                break;
            }
            $stmt->bind_param("is", $id_usuario, $contenido);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Comentario añadido exitosamente.';
                $response['comment'] = [
                    'id_comentario' => $conn->insert_id, // Necesario para futuras respuestas a este comentario
                    'username' => $_SESSION['username'],
                    'comment_text' => htmlspecialchars($contenido),
                    'created_at' => date('Y-m-d H:i:s'), // Usar la fecha actual para la respuesta
                    'parent_comment_id' => null // Es un comentario de nivel superior
                ];
            } else {
                $response['message'] = 'Error al añadir el comentario: ' . $stmt->error;
            }
            $stmt->close();
            break;

        case 'add_reply': // Para respuestas a comentarios existentes
            if (!isset($_SESSION['user_id'])) {
                $response['message'] = 'Debes iniciar sesión para responder.';
                break;
            }

            $contenido = trim($_POST['reply_text'] ?? '');
            $id_usuario = $_SESSION['user_id'];
            $id_comentario_padre = $_POST['parent_comment_id'] ?? null;

            if (empty($contenido)) {
                $response['message'] = 'La respuesta no puede estar vacía.';
                break;
            }

            if (strlen($contenido) > 500) {
                $response['message'] = 'La respuesta es demasiado larga (máximo 500 caracteres).';
                break;
            }

            // Validar que id_comentario_padre sea un número entero y exista
            if (!is_numeric($id_comentario_padre) || $id_comentario_padre <= 0) {
                $response['message'] = 'ID de comentario padre inválido.';
                break;
            }

            // Insertar nueva respuesta en la tabla 'comentarios'
            // Columnas: id_usuario, contenido, id_comentario_padre
            $stmt = $conn->prepare("INSERT INTO comentarios (id_usuario, contenido, id_comentario_padre) VALUES (?, ?, ?)");
            if (!$stmt) {
                $response['message'] = 'Error de preparación de la consulta: ' . $conn->error;
                break;
            }
            $stmt->bind_param("isi", $id_usuario, $contenido, $id_comentario_padre);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Respuesta añadida exitosamente.';
                $response['comment'] = [
                    'id_comentario' => $conn->insert_id,
                    'username' => $_SESSION['username'],
                    'comment_text' => htmlspecialchars($contenido),
                    'created_at' => date('Y-m-d H:i:s'),
                    'parent_comment_id' => (int)$id_comentario_padre // Asegurarse de que sea un entero
                ];
            } else {
                $response['message'] = 'Error al añadir la respuesta: ' . $stmt->error;
            }
            $stmt->close();
            break;

        default:
            $response['message'] = 'Acción POST no válida.';
            break;
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'get_comments') {
        $comments_flat = [];
        // Seleccionar todos los campos necesarios, incluyendo id_comentario y id_comentario_padre
        // Columnas: c.id_comentario, c.contenido, c.fecha, u.apodo, c.id_comentario_padre
        $sql = "SELECT c.id_comentario, c.contenido, c.fecha, u.apodo, c.id_comentario_padre
                FROM comentarios c
                JOIN usuarios u ON c.id_usuario = u.id_usuario
                ORDER BY c.fecha ASC"; // Ordenar por fecha ascendente para construir la jerarquía

        $result = $conn->query($sql);

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $comment_data = [
                    'id_comentario' => (int)$row['id_comentario'],
                    'comment_text' => htmlspecialchars($row['contenido']),
                    'created_at' => $row['fecha'],
                    'username' => $row['apodo'],
                    'parent_comment_id' => $row['id_comentario_padre'] ? (int)$row['id_comentario_padre'] : null,
                    'replies' => [] // Para almacenar las respuestas anidadas
                ];
                $comments_flat[$row['id_comentario']] = $comment_data;
            }

            $comments_tree = [];
            foreach ($comments_flat as $id => $comment) {
                if ($comment['parent_comment_id'] === null) {
                    // Es un comentario de nivel superior
                    $comments_tree[] = &$comments_flat[$id]; // Usar referencia para poder añadir respuestas
                } else {
                    // Es una respuesta, añadirla a su padre
                    if (isset($comments_flat[$comment['parent_comment_id']])) {
                        $comments_flat[$comment['parent_comment_id']]['replies'][] = &$comments_flat[$id];
                    }
                }
            }

            // Opcional: Ordenar los comentarios de nivel superior por fecha descendente para mostrarlos más recientes primero
            usort($comments_tree, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            $response['success'] = true;
            $response['comments'] = $comments_tree;
        } else {
            $response['message'] = 'Error al obtener comentarios: ' . $conn->error;
        }
    } else {
        $response['message'] = 'Acción GET no válida.';
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

$conn->close();
echo json_encode($response);
exit();
?>