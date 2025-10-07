<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') { header("Location: ../index.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: dashboard.php"); exit(); }

include('../config/db.php');

$id_curso = (int)$_GET['id'];
$id_docente = $_SESSION['id'];

// --- VERIFICACIÓN DE SEGURIDAD ---
$stmt_check = $conn->prepare("SELECT * FROM cursos WHERE id = ? AND id_docente_asignado = ?");
$stmt_check->bind_param("ii", $id_curso, $id_docente);
$stmt_check->execute();
$resultado_check = $stmt_check->get_result();
if ($resultado_check->num_rows == 0) {
    die("Acceso denegado: No tienes permiso para gestionar este curso.");
}
$curso = $resultado_check->fetch_assoc();
$stmt_check->close();

$mensaje = '';
// --- LÓGICA PARA CREAR TEMAS Y SUBIR MATERIAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear_tema'])) {
        $titulo_tema = trim($_POST['titulo_tema']);
        if (!empty($titulo_tema)) {
            $stmt = $conn->prepare("INSERT INTO temas_curso (id_curso, titulo_tema) VALUES (?, ?)");
            $stmt->bind_param("is", $id_curso, $titulo_tema);
            $stmt->execute();
            $stmt->close();
            header("Location: ver_curso.php?id=$id_curso"); 
            exit();
        }
    }

    if (isset($_POST['subir_material'])) {
        $id_tema = $_POST['id_tema'];
        $titulo = trim($_POST['titulo']);
        $descripcion = trim($_POST['descripcion_material']);
        $tipo = $_POST['tipo'];
        $url_recurso = trim($_POST['url_recurso']);

        if ($tipo === 'pdf' && isset($_FILES['archivo_material']) && $_FILES['archivo_material']['error'] == 0) {
            $directorio_subida = '../assets/documentos/cursos/';
            $nombre_archivo = time() . '_' . preg_replace("/[^a-zA-Z0-9.\-_]/", "", basename($_FILES['archivo_material']['name']));
            $ruta_archivo = $directorio_subida . $nombre_archivo;
            if (move_uploaded_file($_FILES['archivo_material']['tmp_name'], $ruta_archivo)) {
                $url_recurso = '/assets/documentos/cursos/' . $nombre_archivo;
            } else { $mensaje = "<div class='alert alert-danger'>Hubo un error al mover el archivo subido.</div>"; }
        }

        if (empty($mensaje) && (empty($titulo) || empty($tipo) || empty($url_recurso) || empty($id_tema))) {
            $mensaje = "<div class='alert alert-danger'>Todos los campos, incluyendo el tema, son obligatorios.</div>";
        }

        if (empty($mensaje)) {
            $stmt = $conn->prepare("INSERT INTO materiales_curso (id_curso, id_tema, titulo, descripcion_material, tipo, url_recurso) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissss", $id_curso, $id_tema, $titulo, $descripcion, $tipo, $url_recurso);
            if ($stmt->execute()) {
                header("Location: ver_curso.php?id=$id_curso&exito=subido"); 
                exit();
            }
        }
    }
}
if (isset($_GET['exito'])) { $mensaje = "<div class='alert alert-success'>Acción completada correctamente.</div>"; }

$temas = $conn->query("SELECT * FROM temas_curso WHERE id_curso = $id_curso ORDER BY orden, id ASC");

$page_title = "Gestionar: " . htmlspecialchars($curso['nombre']);
include('../includes/header.php');
?>
<style>
    .course-hero {
        position: relative; padding: 4rem 2rem; border-radius: 15px;
        background-size: cover; background-position: center; color: white;
        margin-bottom: 2rem; overflow: hidden;
    }
    .course-hero::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.6); z-index: 1;
    }
    .course-hero-content { position: relative; z-index: 2; }
</style>

<main class="container mt-5">
    
    <section class="course-hero text-center" style="background-image: url('<?= htmlspecialchars('../assets/img/uploads/' . ($curso['imagen_url'] ?: 'default.png')) ?>');" data-aos="zoom-in">
        <div class="course-hero-content">
            <h1 class="display-4 fw-bold"><?= htmlspecialchars($curso['nombre']) ?></h1>
            <p class="lead">Aquí puedes gestionar todo el material de tu curso.</p>
        </div>
    </section>

    <?= $mensaje ?>

    <div class="d-flex justify-content-between align-items-center mt-4 mb-4" data-aos="fade-right">
        <h2 class="page-title mb-0">Trabajo en Clase</h2>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver a Mis Cursos
        </a>
    </div>

    <div class="row mt-4">
        <div class="col-lg-4 mb-4">
            <div class="card content-card shadow-sm mb-4" data-aos="fade-right">
                <div class="card-body">
                    <h4 class="mb-3"><i class="fas fa-plus-circle me-2 text-primary"></i>Crear Tema/Unidad</h4>
                    <form method="POST">
                        <div class="mb-3"><label>Título del Tema</label><input type="text" name="titulo_tema" class="form-control" placeholder="Ej: Unidad II" required></div>
                        <button type="submit" name="crear_tema" class="btn btn-secondary w-100">Crear Tema</button>
                    </form>
                </div>
            </div>
            <div class="card content-card shadow-sm" data-aos="fade-right" data-aos-delay="100">
                <div class="card-body">
                    <h4 class="mb-3"><i class="fas fa-upload me-2 text-primary"></i>Subir Nuevo Material</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Asignar a Tema</label>
                            <select name="id_tema" class="form-select" required>
                                <option value="">Selecciona un tema...</option>
                                <?php if($temas && $temas->num_rows > 0) mysqli_data_seek($temas, 0); ?>
                                <?php while($tema_option = $temas->fetch_assoc()): ?>
                                    <option value="<?= $tema_option['id'] ?>"><?= htmlspecialchars($tema_option['titulo_tema']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Título del Material</label><input type="text" name="titulo" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Descripción Breve</label><textarea name="descripcion_material" class="form-control" rows="2"></textarea></div>
                        <div class="mb-3"><label class="form-label">Tipo</label><select name="tipo" class="form-select" required><option value="link">Enlace</option><option value="video">Video</option><option value="pdf">Documento</option></select></div>
                        <div class="mb-3"><label class="form-label">URL (para Video/Enlace)</label><input type="url" name="url_recurso" class="form-control" placeholder="https://..."></div>
                        <div class="mb-3"><label class="form-label">O Sube un Archivo (para Documento)</label><input type="file" name="archivo_material" class="form-control"></div>
                        <button type="submit" name="subir_material" class="btn btn-primary w-100">Añadir Material</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <?php if ($temas && $temas->num_rows > 0): ?>
                <?php mysqli_data_seek($temas, 0); while($tema = $temas->fetch_assoc()): ?>
                <div class="mb-5" data-aos="fade-up">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h3 class="mb-0"><?= htmlspecialchars($tema['titulo_tema']) ?></h3>
                        <div>
                            <a href="editar_tema.php?id=<?= $tema['id'] ?>&curso_id=<?= $id_curso ?>" class="btn btn-outline-warning btn-sm" title="Editar Tema">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="eliminar_tema.php?id=<?= $tema['id'] ?>&curso_id=<?= $id_curso ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Seguro que quieres eliminar este tema?')" title="Eliminar Tema">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <?php
                    $id_tema_actual = $tema['id'];
                    $materiales = $conn->query("SELECT * FROM materiales_curso WHERE id_tema = $id_tema_actual ORDER BY fecha_subida ASC");
                    if ($materiales && $materiales->num_rows > 0):
                        while($material = $materiales->fetch_assoc()):
                    ?>
                    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded mb-2 shadow-sm list-group-item-action">
                        <div class="d-flex align-items-center">
                            <div class="me-3 fs-3 text-secondary">
                                <?php if($material['tipo'] == 'video'): ?><i class="fab fa-youtube text-danger"></i>
                                <?php elseif($material['tipo'] == 'pdf'): ?><i class="fas fa-file-pdf text-primary"></i>
                                <?php else: ?><i class="fas fa-link text-secondary"></i><?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?= htmlspecialchars($material['titulo']) ?></h6>
                                <p class="small text-muted mb-1"><?= htmlspecialchars($material['descripcion_material']) ?></p>
                                <a href="<?= htmlspecialchars($material['url_recurso']) ?>" target="_blank" class="small">Ver material</a>
                            </div>
                        </div>
                        <a href="eliminar_material.php?id=<?= $material['id'] ?>&curso_id=<?= $id_curso ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Seguro?')"><i class="fas fa-trash"></i></a>
                    </div>
                    <?php
                        endwhile;
                    else:
                        echo "<p class='text-muted ms-2'>No hay materiales en este tema.</p>";
                    endif;
                    ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center text-muted p-5 bg-light rounded-3" data-aos="zoom-in">
                    <p class="mb-0 h5">Aún no has creado ningún tema.</p>
                    <p>Usa el formulario de la izquierda para empezar a organizar tu curso.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php 
$conn->close();
include('../includes/footer.php'); 
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init();</script>
</body>
</html>
