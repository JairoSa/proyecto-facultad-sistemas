<?php
session_start();
// Proteger la página: solo para estudiantes logueados
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');

// 1. Validar que tengamos los IDs necesarios
if (!isset($_GET['id']) || !isset($_SESSION['id'])) {
    header("Location: anuncios.php?mensaje=error_general");
    exit();
}

$id_curso = (int)$_GET['id'];
$id_estudiante = (int)$_SESSION['id'];

// 2. Verificar si el estudiante ya está inscrito
$stmt_verificar = $conn->prepare("SELECT id FROM inscripciones WHERE id_estudiante = ? AND id_curso = ?");
$stmt_verificar->bind_param("ii", $id_estudiante, $id_curso);
$stmt_verificar->execute();
$resultado_verificar = $stmt_verificar->get_result();

if ($resultado_verificar->num_rows > 0) {
    // Si ya existe un registro, redirigir con mensaje de error
    header("Location: anuncios.php?mensaje=ya_inscrito");
    exit();
}

// 3. Verificar el límite de estudiantes
// Primero, obtener el límite del curso
$stmt_limite = $conn->prepare("SELECT limite_estudiantes FROM cursos WHERE id = ?");
$stmt_limite->bind_param("i", $id_curso);
$stmt_limite->execute();
$curso = $stmt_limite->get_result()->fetch_assoc();
$limite_estudiantes = $curso['limite_estudiantes'];

// Segundo, contar cuántos estudiantes ya están inscritos
$stmt_conteo = $conn->prepare("SELECT COUNT(id) AS total_inscritos FROM inscripciones WHERE id_curso = ?");
$stmt_conteo->bind_param("i", $id_curso);
$stmt_conteo->execute();
$conteo = $stmt_conteo->get_result()->fetch_assoc();
$total_inscritos = $conteo['total_inscritos'];

if ($total_inscritos >= $limite_estudiantes) {
    // Si el curso está lleno, redirigir con mensaje de error
    header("Location: anuncios.php?mensaje=curso_lleno");
    exit();
}

// 4. Si todo está en orden, inscribir al estudiante
$stmt_inscribir = $conn->prepare("INSERT INTO inscripciones (id_estudiante, id_curso) VALUES (?, ?)");
$stmt_inscribir->bind_param("ii", $id_estudiante, $id_curso);

if ($stmt_inscribir->execute()) {
    // Redirigir con mensaje de éxito
    header("Location: anuncios.php?mensaje=inscripcion_exitosa");
} else {
    // Redirigir con mensaje de error general
    header("Location: anuncios.php?mensaje=error_general");
}

$stmt_verificar->close();
$stmt_limite->close();
$stmt_conteo->close();
$stmt_inscribir->close();
$conn->close();
exit();
?>
