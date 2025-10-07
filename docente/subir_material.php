<?php
session_start();
// Proteger la página: solo para docentes logueados.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');

$id_docente = $_SESSION['id'];
$mensaje = '';
$curso_seleccionado = null;
$materiales_del_curso = [];

// Obtener los cursos asignados a este docente
$cursos_asignados = $conn->query("SELECT id, nombre FROM cursos WHERE id_docente_asignado = $id_docente ORDER BY nombre");

// --- LÓGICA PARA MANEJAR LA SELECCIÓN DE CURSO Y SUBIDA DE MATERIAL ---

// Si se selecciona un curso desde la URL
if (isset($_GET['curso_id'])) {
    $id_curso_seleccionado = (int)$_GET['curso_id'];
    
    // Verificación de seguridad: ¿este curso realmente le pertenece al docente?
    $stmt_check = $conn->prepare("SELECT * FROM cursos WHERE id = ? AND id_docente_asignado = ?");
    $stmt_check->bind_param("ii", $id_curso_seleccionado, $id_docente);
    $stmt_check->execute();
    $resultado_check = $stmt_check->get_result();
    if ($resultado_check->num_rows > 0) {
        $curso_seleccionado = $resultado_check->fetch_assoc();
        // Si hay un curso seleccionado, obtenemos sus materiales
        $materiales_del_curso = $conn->query("SELECT * FROM materiales_curso WHERE id_curso = $id_curso_seleccionado ORDER BY fecha_subida DESC");
    } else {
        die("Error: Estás intentando acceder a un curso que no te pertenece.");
    }
}

// Si se envía el formulario para subir material
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_material'])) {
    $id_curso = $_POST['id_curso'];
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion_material']);
    $tipo = $_POST['tipo'];
    $url_recurso = trim($_POST['url_recurso']);

    if (empty($titulo) || empty($tipo) || empty($url_recurso)) {
        $mensaje = "<div class='alert alert-danger'>El título, tipo y enlace/URL del recurso son obligatorios.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO materiales_curso (id_curso, titulo, descripcion_material, tipo, url_recurso) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $id_curso, $titulo, $descripcion, $tipo, $url_recurso);
        if ($stmt->execute()) {
            // Redirige a la misma página para refrescar la lista de materiales
            header("Location: subir_material.php?curso_id=$id_curso&exito=subido");
            exit();
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al subir el material.</div>";
        }
    }
}
if (isset($_GET['exito']) && $_GET['exito'] == 'subido') {
    $mensaje = "<div class='alert alert-success'>Material subido correctamente.</div>";
}


$page_title = "Subir Material de Clase";
include('../includes/header.php');
?>

<main class="container mt-5">
    <h1 class="page-title" data-aos="fade-down">Gestión de Material de Clase</h1>
    <?= $mensaje ?>

    <div class="card content-card shadow-sm mb-4" data-aos="fade-up">
        <div class="card-body">
            <h4 class="mb-3"><i class="fas fa-book-open me-2 text-primary"></i>Selecciona un Curso</h4>
            <form method="GET" action="subir_material.php">
                <div class="input-group">
                    <select name="curso_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Elige el curso que deseas gestionar...</option>
                        <?php if ($cursos_asignados->num_rows > 0): ?>
                            <?php while($curso = $cursos_asignados->fetch_assoc()): ?>
                                <option value="<?= $curso['id'] ?>" <?= ($curso_seleccionado && $curso_seleccionado['id'] == $curso['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($curso['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <?php if ($curso_seleccionado): ?>
    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card content-card shadow-sm" data-aos="fade-right">
                <div class="card-body">
                    <h4 class="mb-4"><i class="fas fa-upload me-2 text-primary"></i>Subir Nuevo Material</h4>
                    <form method="POST" action="subir_material.php?curso_id=<?= $curso_seleccionado['id'] ?>">
                        <input type="hidden" name="id_curso" value="<?= $curso_seleccionado['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Título del Material</label>
                            <input type="text" name="titulo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción (Opcional)</label>
                            <textarea name="descripcion_material" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de Recurso</label>
                            <select name="tipo" class="form-select" required>
                                <option value="link">Enlace (Link)</option>
                                <option value="video">Video (YouTube, Vimeo, etc.)</option>
                                <option value="pdf">Documento (PDF)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL del Recurso</label>
                            <input type="url" name="url_recurso" class="form-control" placeholder="https://ejemplo.com/recurso.pdf" required>
                            <small class="form-text text-muted">Pega aquí el enlace al video, PDF o página web.</small>
                        </div>
                        <button type="submit" name="subir_material" class="btn btn-primary w-100">Añadir Material</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card content-card shadow-sm" data-aos="fade-left">
                <div class="card-body">
                    <h4 class="mb-4"><i class="fas fa-list-ul me-2 text-primary"></i>Material Existente en "<?= htmlspecialchars($curso_seleccionado['nombre']) ?>"</h4>
                    <div class="list-group">
                        <?php if ($materiales_del_curso && $materiales_del_curso->num_rows > 0): ?>
                            <?php while($material = $materiales_del_curso->fetch_assoc()): ?>
                                <div class="list-group-item">
                                    <h6>
                                        <?php if($material['tipo'] == 'video'): ?><i class="fas fa-video me-2 text-danger"></i>
                                        <?php elseif($material['tipo'] == 'pdf'): ?><i class="fas fa-file-pdf me-2 text-primary"></i>
                                        <?php else: ?><i class="fas fa-link me-2 text-secondary"></i><?php endif; ?>
                                        <?= htmlspecialchars($material['titulo']) ?>
                                    </h6>
                                    <p class="small mb-1"><?= htmlspecialchars($material['descripcion_material']) ?></p>
                                    <a href="<?= htmlspecialchars($material['url_recurso']) ?>" target="_blank" class="small">Ver Recurso &rarr;</a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center text-muted">Aún no hay material para este curso.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php 
$conn->close();
include('../includes/footer.php'); 
?>
</body>
</html>
