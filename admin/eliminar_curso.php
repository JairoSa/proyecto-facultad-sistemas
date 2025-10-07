<?php
session_start();
// Solo un admin puede ejecutar este script.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    die("Acceso denegado.");
}

include('../config/db.php');

// Verifica si se recibió un ID de curso por la URL.
if (isset($_GET['id'])) {
    $id_curso_a_eliminar = $_GET['id'];

    // Prepara y ejecuta la sentencia DELETE en la tabla 'cursos'.
    // Gracias a la configuración 'ON DELETE CASCADE' de la base de datos,
    // al eliminar un curso, se borrarán automáticamente todas las inscripciones,
    // materiales y notificaciones asociadas a él.
    $stmt = $conn->prepare("DELETE FROM cursos WHERE id = ?");
    $stmt->bind_param("i", $id_curso_a_eliminar);
    $stmt->execute();
    $stmt->close();

    // Redirige de vuelta a la página de gestión con un mensaje de éxito.
    header("Location: gestionar_cursos.php?exito=eliminado");
    exit();
}

// Si no se proporciona un ID, simplemente redirige.
header("Location: gestionar_cursos.php");
exit();
?>
