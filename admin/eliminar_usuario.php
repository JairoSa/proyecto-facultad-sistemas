<?php
session_start();
// Solo un admin puede ejecutar este script.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Acceso denegado.");
}

include('../config/db.php');

// Verifica si se recibió un ID de usuario por la URL.
if (isset($_GET['id'])) {
    $id_usuario_a_eliminar = $_GET['id'];

    // IMPORTANTE: Evita que el admin se elimine a sí mismo.
    if ($id_usuario_a_eliminar == $_SESSION['id']) {
        // Redirige con un mensaje de error.
        header("Location: gestionar_usuarios.php?error=autoeliminacion");
        exit();
    }

    // Prepara y ejecuta la sentencia DELETE.
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id_usuario_a_eliminar);
    $stmt->execute();
    $stmt->close();

    // Redirige de vuelta a la página de gestión con un mensaje de éxito.
    header("Location: gestionar_usuarios.php?exito=eliminado");
    exit();
}

// Si no se proporciona un ID, simplemente redirige.
header("Location: gestionar_usuarios.php");
exit();
?>
