<?php
session_start();
// Protección de la página
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: ../index.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: mis_cursos.php");
    exit();
}

include('../config/db.php');

$id_curso = (int)$_GET['id'];
$id_estudiante = $_SESSION['id'];

// --- VERIFICACIÓN DE SEGURIDAD CRUCIAL ---
// Nos aseguramos de que este estudiante esté inscrito en este curso.
$stmt_check = $conn->prepare("SELECT id FROM inscripciones WHERE id_estudiante = ? AND id_curso = ?");
$stmt_check->bind_param("ii", $id_estudiante, $id_curso);
$stmt_check->execute();
$resultado_check = $stmt_check->get_result();
if ($resultado_check->num_rows == 0) {
    // Si no está inscrito, lo sacamos de la página con un mensaje.
    header("Location: mis_cursos.php?error=no_inscrito");
    exit();
}
$stmt_check->close();

// Si la verificación pasa, obtenemos los datos del curso y sus temas
$stmt_curso = $conn->prepare("SELECT c.*, u.nombre as nombre_docente FROM cursos c LEFT JOIN usuarios u ON c.id_docente_asignado = u.id WHERE c.id = ?");
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();
$curso = $stmt_curso->get_result()->fetch_assoc();

$temas = $conn->query("SELECT * FROM temas_curso WHERE id_curso = $id_curso ORDER BY orden, id ASC");

$page_title = "Curso: " . htmlspecialchars($curso['nombre']);
include('../includes/header.php');
?>
<style>
    .course-hero {
        position: relative;
        padding: 4rem 2rem;
        border-radius: 15px;
        background-size: cover;
        background-position: center;
        color: white;
        margin-bottom: 2rem;
        overflow: hidden;
    }
    .course-hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1;
    }
    .course-hero-content {
        position: relative;
        z-index: 2;
    }
</style>

<main class="container mt-5">
    
    <section class="course-hero text-center" style="background-image: url('<?= htmlspecialchars('../assets/img/uploads/' . ($curso['imagen_url'] ?: 'default.png')) ?>');" data-aos="zoom-in">
        <div class="course-hero-content">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb justify-content-center bg-transparent p-0 m-0">
                <li class="breadcrumb-item"><a href="mis_cursos.php" class="text-white">Mis Cursos</a></li>
                <li class="breadcrumb-item active text-white-50" aria-current="page"><?= htmlspecialchars($curso['nombre']) ?></li>
              </ol>
            </nav>
            <h1 class="display-4 fw-bold mt-2"><?= htmlspecialchars($curso['nombre']) ?></h1>
            <p class="lead">Docente: <?= htmlspecialchars($curso['nombre_docente'] ?? 'Por asignar') ?></p>
        </div>
    </section>

    <h2 class="page-title mt-5" data-aos="fade-right">Trabajo en Clase</h2>
    
    <?php if ($temas && $temas->num_rows > 0): ?>
        <?php while($tema = $temas->fetch_assoc()): ?>
        <div class="mb-5" data-aos="fade-up">
            <h3 class="border-bottom pb-2 mb-3"><?= htmlspecialchars($tema['titulo_tema']) ?></h3>
            <?php
            $id_tema_actual = $tema['id'];
            $materiales = $conn->query("SELECT * FROM materiales_curso WHERE id_tema = $id_tema_actual ORDER BY fecha_subida ASC");
            if ($materiales && $materiales->num_rows > 0):
                while($material = $materiales->fetch_assoc()):
            ?>
            <a href="<?= htmlspecialchars($material['url_recurso']) ?>" target="_blank" class="text-decoration-none text-dark">
                <div class="d-flex align-items-center bg-light p-3 rounded mb-2 shadow-sm list-group-item-action">
                    <div class="me-3 fs-3">
                        <?php if($material['tipo'] == 'video'): ?><i class="fab fa-youtube text-danger"></i>
                        <?php elseif($material['tipo'] == 'pdf'): ?><i class="fas fa-file-pdf text-primary"></i>
                        <?php else: ?><i class="fas fa-link text-secondary"></i><?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0"><?= htmlspecialchars($material['titulo']) ?></h6>
                        <?php if(!empty($material['descripcion_material'])): ?>
                            <p class="mb-0 small text-muted"><?= htmlspecialchars($material['descripcion_material']) ?></p>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted ms-3"><?= date("d/m/Y", strtotime($material['fecha_subida'])) ?></small>
                </div>
            </a>
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
            <p class="mb-0 h5">El docente aún no ha organizado el material de este curso.</p>
        </div>
    <?php endif; ?>
</main>

<?php 
$stmt_curso->close();
$conn->close();
include('../includes/footer.php'); 
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init();</script>
</body>
</html>
