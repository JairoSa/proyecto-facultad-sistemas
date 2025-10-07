<?php
session_start();
// Proteger la página: solo para estudiantes logueados
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');

// Validar que tengamos los IDs necesarios
if (!isset($_GET['id']) || !isset($_SESSION['id'])) {
    header("Location: mis_cursos.php?mensaje=error_general");
    exit();
}

$id_curso = (int)$_GET['id'];
$id_estudiante = (int)$_SESSION['id'];

// Prepara y ejecuta la sentencia DELETE para eliminar la inscripción
$stmt = $conn->prepare("DELETE FROM inscripciones WHERE id_estudiante = ? AND id_curso = ?");
$stmt->bind_param("ii", $id_estudiante, $id_curso);

if ($stmt->execute()) {
    // Redirige con mensaje de éxito
    header("Location: mis_cursos.php?mensaje=baja_exitosa");
} else {
    // Redirige con mensaje de error general
    header("Location: mis_cursos.php?mensaje=error_general");
}

$stmt->close();
$conn->close();
exit();
?>
