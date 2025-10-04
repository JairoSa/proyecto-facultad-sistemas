<?php
session_start();
// ... (verificación de sesión de admin) ...

include('../config/db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Opcional: Borrar el archivo de imagen del servidor
    $stmt_select = $conn->prepare("SELECT imagen_url FROM publicaciones WHERE id = ?");
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $result = $stmt_select->get_result()->fetch_assoc();
    if ($result && !empty($result['imagen_url'])) {
        $ruta_imagen = '../assets/img/uploads/' . $result['imagen_url'];
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
    }

    // Borrar el registro de la base de datos
    $stmt_delete = $conn->prepare("DELETE FROM publicaciones WHERE id = ?");
    $stmt_delete->bind_param("i", $id);
    $stmt_delete->execute();
}

header("Location: listar_publicaciones.php");
exit();
?>