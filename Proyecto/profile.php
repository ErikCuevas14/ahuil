<?php
// File: Proyecto/profile.php
session_start();
require_once 'includes/db_connect.php';

// Redirigir si el usuario no está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_data = null;
$message = '';
$message_type = ''; // 'success' or 'error'
$edit_mode = false; // Variable para controlar el modo de edición

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT apodo, email FROM usuarios WHERE id_usuario = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
    } else {
        $message = 'Error: Usuario no encontrado.';
        $message_type = 'error';
    }
    $stmt->close();
} else {
    $message = 'Error de preparación de la consulta: ' . $conn->error;
    $message_type = 'error';
}

// Procesar actualización de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'enter_edit_mode') {
            $edit_mode = true; // Activar el modo de edición
        } elseif ($_POST['action'] === 'update_profile') {
            $new_apodo = trim($_POST['username'] ?? '');
            $new_email = trim($_POST['email'] ?? '');
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_new_password = $_POST['confirm_new_password'] ?? '';

            // Validaciones básicas
            if (empty($new_apodo) || empty($new_email)) {
                $message = 'El nombre de usuario y el correo electrónico no pueden estar vacíos.';
                $message_type = 'error';
            } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                $message = 'El formato del correo electrónico no es válido.';
                $message_type = 'error';
            } else {
                // Verificar si el nuevo email ya existe para otro usuario
                $stmt_check_email = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?");
                if ($stmt_check_email) {
                    $stmt_check_email->bind_param("si", $new_email, $user_id);
                    $stmt_check_email->execute();
                    $stmt_check_email->store_result();
                    if ($stmt_check_email->num_rows > 0) {
                        $message = 'Este correo electrónico ya está en uso por otra cuenta.';
                        $message_type = 'error';
                    }
                    $stmt_check_email->close();
                } else {
                    $message = 'Error de preparación de la consulta de email: ' . $conn->error;
                    $message_type = 'error';
                }

                if ($message_type !== 'error') { // Si no hay errores con el email
                    // Actualizar apodo y email
                    $stmt_update = $conn->prepare("UPDATE usuarios SET apodo = ?, email = ? WHERE id_usuario = ?");
                    if ($stmt_update) {
                        $stmt_update->bind_param("ssi", $new_apodo, $new_email, $user_id);
                        if ($stmt_update->execute()) {
                            $_SESSION['username'] = $new_apodo; // Actualizar la sesión
                            $_SESSION['email'] = $new_email;
                            $user_data['apodo'] = $new_apodo; // Actualizar datos mostrados
                            $user_data['email'] = $new_email;
                            $message = 'Datos de perfil actualizados exitosamente.';
                            $message_type = 'success';
                        } else {
                            $message = 'Error al actualizar el perfil: ' . $stmt_update->error;
                            $message_type = 'error';
                        }
                        $stmt_update->close();
                    } else {
                        $message = 'Error de preparación de la consulta de actualización: ' . $conn->error;
                        $message_type = 'error';
                    }

                    // Procesar cambio de contraseña si se proporcionaron los campos
                    if (!empty($current_password) || !empty($new_password) || !empty($confirm_new_password)) {
                        if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
                            $message = 'Para cambiar la contraseña, todos los campos de contraseña son obligatorios.';
                            $message_type = 'error';
                        } elseif ($new_password !== $confirm_new_password) {
                            $message = 'La nueva contraseña y su confirmación no coinciden.';
                            $message_type = 'error';
                        } elseif (strlen($new_password) < 6) {
                            $message = 'La nueva contraseña debe tener al menos 6 caracteres.';
                            $message_type = 'error';
                        } else {
                            // Verificar la contraseña actual
                            $stmt_check_pass = $conn->prepare("SELECT contraseña FROM usuarios WHERE id_usuario = ?");
                            if ($stmt_check_pass) {
                                $stmt_check_pass->bind_param("i", $user_id);
                                $stmt_check_pass->execute();
                                $stmt_check_pass->bind_result($hashed_password);
                                $stmt_check_pass->fetch();
                                $stmt_check_pass->close();

                                if (password_verify($current_password, $hashed_password)) {
                                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                                    $stmt_update_pass = $conn->prepare("UPDATE usuarios SET contraseña = ? WHERE id_usuario = ?");
                                    if ($stmt_update_pass) {
                                        $stmt_update_pass->bind_param("si", $new_hashed_password, $user_id);
                                        if ($stmt_update_pass->execute()) {
                                            $message .= ' Contraseña actualizada exitosamente.';
                                            $message_type = 'success';
                                        } else {
                                            $message = 'Error al actualizar la contraseña: ' . $stmt_update_pass->error;
                                            $message_type = 'error';
                                        }
                                        $stmt_update_pass->close();
                                    } else {
                                        $message = 'Error de preparación de la consulta de contraseña: ' . $conn->error;
                                        $message_type = 'error';
                                    }
                                } else {
                                    $message = 'La contraseña actual es incorrecta.';
                                    $message_type = 'error';
                                }
                            } else {
                                $message = 'Error de preparación de la consulta de verificación de contraseña: ' . $conn->error;
                                $message_type = 'error';
                            }
                        }
                    }
                }
            }
        }
    }
}

// Incluir la vista HTML
include 'profile_view.php';
?>