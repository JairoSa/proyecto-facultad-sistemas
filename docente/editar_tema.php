<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') { die("Acceso denegado."); }
if (!isset($_GET['id']) || !isset($_GET['curso_id'])) { header("Location: dashboard.php"); exit(); }

include('../config/db.php');

$id_tema = (int)$_GET['id'];
$id_curso = (int)$_GET['curso_id'];
$id_docente = $_SESSION['id'];

// Verificación de seguridad
$stmt_check = $conn->prepare("SELECT c.id FROM cursos c JOIN temas_curso t ON c.id = t.id_curso WHERE t.id = ? AND c.id_docente_asignado = ?");
$stmt_check->bind_param("ii", $id_tema, $id_docente);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows == 0) {
    die("Acceso denegado.");
}

// Si se envía el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_titulo = trim($_POST['titulo_tema']);
    if (!empty($nuevo_titulo)) {
        $stmt_update = $conn->prepare("UPDATE temas_curso SET titulo_tema = ? WHERE id = ?");
        $stmt_update->bind_param("si", $nuevo_titulo, $id_tema);
        $stmt_update->execute();
        header("Location: ver_curso.php?id=$id_curso");
        exit();
    }
}

// Obtener el nombre actual del tema
$tema = $conn->query("SELECT titulo_tema FROM temas_curso WHERE id = $id_tema")->fetch_assoc();

$page_title = "Editar Tema";
include('../includes/header.php');
?>
<main class="container mt-5">
    <h1 class="page-title">Editar Tema</h1>
    <div class="card content-card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nuevo Título del Tema</label>
                    <input type="text" name="titulo_tema" class="form-control" value="<?= htmlspecialchars($tema['titulo_tema']) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Actualizar Tema</button>
                <a href="ver_curso.php?id=<?= $id_curso ?>" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</main>
<?php include('../includes/footer.php'); ?>
