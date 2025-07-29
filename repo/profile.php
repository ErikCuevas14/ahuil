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
$message_type = '';
$edit_mode = false;
$delete_mode = false;

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT apodo, email, contraseña FROM usuarios WHERE id_usuario = ?");
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

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'enter_edit_mode') {
            $edit_mode = true;
        } elseif ($_POST['action'] === 'enter_delete_mode') {
            $delete_mode = true;
        } elseif ($_POST['action'] === 'cancel_delete') {
            $delete_mode = false;
        } elseif ($_POST['action'] === 'update_profile') {
            $new_username = trim($_POST['username'] ?? '');
            $new_email = trim($_POST['email'] ?? '');
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_new_password = $_POST['confirm_new_password'] ?? '';

            // Validaciones básicas
            if (empty($new_username) || empty($new_email)) {
                $message = 'El nombre de usuario y el correo electrónico no pueden estar vacíos.';
                $message_type = 'error';
                $edit_mode = true;
            } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                $message = 'El formato del correo electrónico no es válido.';
                $message_type = 'error';
                $edit_mode = true;
            } else {
                $update_fields = [];
                $update_values = []; // Cambiamos de $update_params a $update_values
                $param_types = '';

                // Verificar si el email ha cambiado y si ya existe
                if ($new_email !== $user_data['email']) {
                    $stmt_check_email = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?");
                    if ($stmt_check_email) {
                        $stmt_check_email->bind_param("si", $new_email, $user_id);
                        $stmt_check_email->execute();
                        $stmt_check_email->store_result();
                        if ($stmt_check_email->num_rows > 0) {
                            $message = 'Este correo electrónico ya está registrado por otro usuario.';
                            $message_type = 'error';
                            $edit_mode = true;
                            $stmt_check_email->close();
                            goto end_update_profile;
                        }
                        $stmt_check_email->close();
                    } else {
                        $message = 'Error de preparación de la consulta de verificación de email: ' . $conn->error;
                        $message_type = 'error';
                        $edit_mode = true;
                        goto end_update_profile;
                    }
                    $update_fields[] = 'email = ?';
                    $update_values[] = $new_email;
                    $param_types .= 's';
                }

                // Verificar si el nombre de usuario ha cambiado
                if ($new_username !== $user_data['apodo']) {
                    $update_fields[] = 'apodo = ?';
                    $update_values[] = $new_username;
                    $param_types .= 's';
                }

                // Lógica para cambiar la contraseña
                if (!empty($new_password)) {
                    if (empty($current_password)) {
                        $message = 'Debes ingresar tu contraseña actual para cambiarla.';
                        $message_type = 'error';
                        $edit_mode = true;
                        goto end_update_profile;
                    }
                    if (!password_verify($current_password, $user_data['contraseña'])) {
                        $message = 'La contraseña actual es incorrecta.';
                        $message_type = 'error';
                        $edit_mode = true;
                        goto end_update_profile;
                    }
                    if ($new_password !== $confirm_new_password) {
                        $message = 'La nueva contraseña y la confirmación no coinciden.';
                        $message_type = 'error';
                        $edit_mode = true;
                        goto end_update_profile;
                    }
                    if (strlen($new_password) < 6) {
                        $message = 'La nueva contraseña debe tener al menos 6 caracteres.';
                        $message_type = 'error';
                        $edit_mode = true;
                        goto end_update_profile;
                    }

                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_fields[] = 'contraseña = ?';
                    $update_values[] = $hashed_new_password;
                    $param_types .= 's';
                } elseif (!empty($current_password)) {
                    $message = 'Ingresa la nueva contraseña si deseas cambiarla.';
                    $message_type = 'error';
                    $edit_mode = true;
                    goto end_update_profile;
                }

                // Si no hay campos para actualizar
                if (empty($update_fields)) {
                    $message = 'No se detectaron cambios para actualizar.';
                    $message_type = 'success';
                    $stmt = $conn->prepare("SELECT apodo, email, contraseña FROM usuarios WHERE id_usuario = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $user_data = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    goto end_update_profile;
                }

                // Construir y ejecutar la consulta de actualización
                $sql_update = "UPDATE usuarios SET " . implode(', ', $update_fields) . " WHERE id_usuario = ?";
                $update_values[] = $user_id;
                $param_types .= 'i';

                $stmt_update = $conn->prepare($sql_update);
                if ($stmt_update) {
                    // Solución al error de referencia:
                    // Crear un array con referencias a los valores
                    $refs = array();
                    foreach($update_values as $key => $value) {
                        $refs[$key] = &$update_values[$key]; 
                    }
                    // Añadir el tipo como primer elemento
                    array_unshift($refs, $param_types);
                    
                    // Llamar a bind_param con los parámetros por referencia
                    call_user_func_array(array($stmt_update, 'bind_param'), $refs);

                    if ($stmt_update->execute()) {
                        $message = 'Perfil actualizado exitosamente.';
                        $message_type = 'success';
                        if ($new_username !== $_SESSION['username']) {
                            $_SESSION['username'] = $new_username;
                        }
                        if ($new_email !== $_SESSION['email']) {
                            $_SESSION['email'] = $new_email;
                        }
                        $stmt = $conn->prepare("SELECT apodo, email, contraseña FROM usuarios WHERE id_usuario = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $user_data = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                    } else {
                        $message = 'Error al actualizar el perfil: ' . $stmt_update->error;
                        $message_type = 'error';
                        $edit_mode = true;
                    }
                    $stmt_update->close();
                } else {
                    $message = 'Error de preparación de la consulta de actualización: ' . $conn->error;
                    $message_type = 'error';
                    $edit_mode = true;
                }
            }
            end_update_profile:;
        } elseif ($_POST['action'] === 'confirm_delete') {
            $password = $_POST['delete_password'] ?? '';
            
            if (empty($password)) {
                $message = 'Debes ingresar tu contraseña para confirmar la eliminación.';
                $message_type = 'error';
                $delete_mode = true;
            } else {
                $stmt = $conn->prepare("SELECT contraseña FROM usuarios WHERE id_usuario = ?");
                if ($stmt) {
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->bind_result($hashed_password);
                    $stmt->fetch();
                    $stmt->close();
                    
                    if (password_verify($password, $hashed_password)) {
                        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
                        
                        try {
                            $stmt_update_comments = $conn->prepare("UPDATE comentarios SET id_comentario_padre = NULL WHERE id_comentario_padre IN (SELECT id_comentario FROM comentarios WHERE id_usuario = ?)");
                            if ($stmt_update_comments) {
                                $stmt_update_comments->bind_param("i", $user_id);
                                $stmt_update_comments->execute();
                                $stmt_update_comments->close();
                            }
                            
                            $stmt_delete_comments = $conn->prepare("DELETE FROM comentarios WHERE id_usuario = ?");
                            if ($stmt_delete_comments) {
                                $stmt_delete_comments->bind_param("i", $user_id);
                                $stmt_delete_comments->execute();
                                $stmt_delete_comments->close();
                            }
                            
                            $stmt_delete_user = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
                            if ($stmt_delete_user) {
                                $stmt_delete_user->bind_param("i", $user_id);
                                if ($stmt_delete_user->execute()) {
                                    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                                    session_unset();
                                    session_destroy();
                                    header('Location: index.php?account_deleted=1');
                                    exit();
                                }
                                $stmt_delete_user->close();
                            }
                        } catch (Exception $e) {
                            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                            throw $e;
                        }
                    } else {
                        $message = 'Contraseña incorrecta. No se pudo eliminar la cuenta.';
                        $message_type = 'error';
                        $delete_mode = true;
                    }
                }
            }
        }
    }
}

include 'profile_view.php';
?>
