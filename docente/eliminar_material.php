<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') { die("Acceso denegado."); }

include('../config/db.php');

if (isset($_GET['id']) && isset($_GET['curso_id'])) {
    $id_material = (int)$_GET['id'];
    $id_curso = (int)$_GET['curso_id'];
    $id_docente = $_SESSION['id'];

    // Verificación de seguridad: Asegurarse de que el docente solo borre material de sus propios cursos.
    $stmt_check = $conn->prepare("SELECT id FROM cursos WHERE id = ? AND id_docente_asignado = ?");
    $stmt_check->bind_param("ii", $id_curso, $id_docente);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        // Si el curso le pertenece, procede a borrar el material.
        $stmt_delete = $conn->prepare("DELETE FROM materiales_curso WHERE id = ? AND id_curso = ?");
        $stmt_delete->bind_param("ii", $id_material, $id_curso);
        $stmt_delete->execute();
    }
}
// Redirige de vuelta a la página de gestión del curso.
header("Location: ver_curso.php?id=" . (int)$_GET['curso_id']);
exit();
?>
