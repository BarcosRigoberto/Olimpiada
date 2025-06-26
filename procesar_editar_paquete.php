<?php
// --- INICIO: LÍNEAS PARA DEPURACIÓN (¡DESACTIVAR EN PRODUCCIÓN!) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN: LÍNEAS PARA DEPURACIÓN ---

session_start();
require_once 'conexion.php';

// Verificar si el usuario está logueado y si es un vendedor o administrador
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['rol'] !== 'vendedor' && $_SESSION['rol'] !== 'admin')) {
    $_SESSION['mensaje_error'] = "Acceso denegado. Debes ser vendedor o administrador.";
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanear y validar el ID del paquete desde el campo oculto
    $paquete_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($paquete_id === false || $paquete_id <= 0) {
        $_SESSION['mensaje_error'] = "ID de paquete inválido o no proporcionado.";
        header("Location: panel_vendedor.php"); // Redirigir si el ID es crítico
        exit();
    }

    // Obtener la imagen actual (campo oculto)
    $imagen_actual = filter_input(INPUT_POST, 'imagen_actual', FILTER_SANITIZE_URL);

    // Obtener los datos del formulario y sanearlos
    // ¡IMPORTANTE!: Eliminamos mysqli->real_escape_string() aquí.
    // filter_input(..., FILTER_SANITIZE_STRING) ya es suficiente para la sanitización,
    // y bind_param se encarga del escape correcto para la DB.
    $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING));
    $descomp = trim(filter_input(INPUT_POST, 'descomp', FILTER_SANITIZE_STRING));
    $destino = trim(filter_input(INPUT_POST, 'destino', FILTER_SANITIZE_STRING));
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $duracion = filter_input(INPUT_POST, 'duracion', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT); 

    $id_usuario_sesion = $_SESSION['id']; // ID del usuario que intenta editar

    // --- VERIFICACIÓN DE PERMISOS (ya lo tenías, lo mantengo) ---
    $sql_check_owner = "SELECT id_usuario FROM paquetes WHERE id = ?";
    $stmt_check_owner = $mysqli->prepare($sql_check_owner);

    if (!$stmt_check_owner) {
        $_SESSION['mensaje_error'] = "Error de seguridad (preparación de consulta de permisos): " . $mysqli->error;
        header("Location: panel_vendedor.php");
        exit();
    }
    $stmt_check_owner->bind_param("i", $paquete_id);
    $stmt_check_owner->execute();
    $res_check_owner = $stmt_check_owner->get_result();

    if ($res_check_owner->num_rows === 0) {
        $_SESSION['mensaje_error'] = "Paquete no encontrado para edición.";
        header("Location: panel_vendedor.php");
        exit();
    }
    $paquete_data = $res_check_owner->fetch_assoc();
    $stmt_check_owner->close();

    if ($_SESSION['rol'] === 'vendedor' && $paquete_data['id_usuario'] !== $id_usuario_sesion) {
        $_SESSION['mensaje_error'] = "No tienes permiso para editar este paquete.";
        header("Location: panel_vendedor.php");
        exit();
    }
    // --- FIN VERIFICACIÓN DE PERMISOS ---

    // Validación de datos del formulario (más estricta)
    if (empty($nombre) || empty($descripcion) || empty($descomp) || empty($destino) || 
        $precio === false || $precio <= 0 || 
        $duracion === false || $duracion <= 0 || 
        $rating === false || $rating < 1 || $rating > 5) {
        
        $_SESSION['mensaje_error'] = "Por favor, completa todos los campos obligatorios y asegúrate que los valores sean válidos (precio/duración > 0, rating entre 1 y 5).";
        header("Location: editar_paquete.php?id=" . $paquete_id); // Redirige al formulario de edición con el ID
        exit();
    }

    $ruta_imagen = $imagen_actual; // Por defecto, mantenemos la imagen actual

    // --- MANEJO DE LA IMAGEN ---
    // Verificar si se subió una nueva imagen
    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/Portadas/"; 
        // Asegúrate de que el nombre del archivo sea único y seguro
        $image_name = uniqid() . "_" . str_replace(" ", "_", basename($_FILES["imagen"]["name"]));
        $target_file = $target_dir . $image_name;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Verificar si el directorio existe y tiene permisos, si no, intenta crearlo
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) { // 0777 da permisos completos (puede que necesites ajustarlo en producción)
                $_SESSION['mensaje_error'] = "Error: No se pudo crear el directorio de subida de imágenes. Verifica permisos.";
                $uploadOk = 0;
            }
        }

        // Verificar si el archivo es una imagen real o un fake
        if ($uploadOk) {
            $check = getimagesize($_FILES["imagen"]["tmp_name"]);
            if($check === false) {
                $_SESSION['mensaje_error'] = "El nuevo archivo no es una imagen o está corrupto.";
                $uploadOk = 0;
            }
        }
        
        // Verificar tamaño del archivo (5MB)
        if ($uploadOk && $_FILES["imagen"]["size"] > 5000000) { 
            $_SESSION['mensaje_error'] = "Lo siento, la nueva imagen es demasiado grande. Máximo 5MB.";
            $uploadOk = 0;
        }

        // Permitir solo ciertos formatos de archivo
        if($uploadOk && !in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
            $_SESSION['mensaje_error'] = "Solo se permiten archivos JPG, JPEG, PNG y GIF para la nueva imagen.";
            $uploadOk = 0;
        }

        // Si alguna validación de subida falla, redirige antes de intentar mover el archivo
        if ($uploadOk == 0) {
            header("Location: editar_paquete.php?id=" . $paquete_id);
            exit();
        } else {
            // Si todo está bien, intentar subir el archivo
            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
                $ruta_imagen = $target_file;
                // Si la subida fue exitosa y había una imagen anterior, la eliminamos
                if (!empty($imagen_actual) && file_exists($imagen_actual) && $imagen_actual != $ruta_imagen) {
                    unlink($imagen_actual);
                }
            } else {
                $_SESSION['mensaje_error'] = "Hubo un error al subir la nueva imagen. Código de error: " . $_FILES["imagen"]["error"];
                error_log("Error al mover el archivo subido: " . $_FILES["imagen"]["tmp_name"] . " a " . $target_file);
                header("Location: editar_paquete.php?id=" . $paquete_id);
                exit();
            }
        }
    }
    // --- FIN MANEJO DE LA IMAGEN ---

    // Actualizar el paquete en la base de datos
    // Asegúrate de que el orden de los placeholders y las variables coincida
    $stmt = $mysqli->prepare("UPDATE paquetes SET nombre = ?, descripcion = ?, destino = ?, precio = ?, duracion = ?, descomp = ?, imagen = ?, rating = ? WHERE id = ?");
    
    if ($stmt) {
        // Tipos de datos: s (string), d (double/float), i (integer)
        // El orden debe ser: nombre (s), descripcion (s), destino (s), precio (d), duracion (i), descomp (s), imagen (s), rating (d), id (i)
        $stmt->bind_param("sssdissdi", 
            $nombre, 
            $descripcion, 
            $destino, 
            $precio, 
            $duracion, 
            $descomp, 
            $ruta_imagen, 
            $rating, 
            $paquete_id
        );
        
        if ($stmt->execute()) {
            $_SESSION['mensaje_exito'] = "Paquete '$nombre' actualizado exitosamente.";
            header("Location: panel_vendedor.php");
            exit();
        } else {
            $_SESSION['mensaje_error'] = "Error al actualizar el paquete: " . $stmt->error;
            error_log("Error al ejecutar UPDATE: " . $stmt->error); // Log del error
            header("Location: editar_paquete.php?id=" . $paquete_id); // Redirigir de vuelta al formulario de edición con el error
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['mensaje_error'] = "Error en la preparación de la consulta SQL: " . $mysqli->error;
        error_log("Error en la preparación de la consulta: " . $mysqli->error); // Log del error
        header("Location: editar_paquete.php?id=" . $paquete_id); // Redirigir de vuelta al formulario de edición con el error
        exit();
    }
} else {
    // Si no es una solicitud POST, redirigir
    $_SESSION['mensaje_error'] = "Acceso inválido al procesador de edición de paquetes.";
    header("Location: panel_vendedor.php");
    exit();
}

$mysqli->close(); // Cerrar la conexión si no se salió antes
?>