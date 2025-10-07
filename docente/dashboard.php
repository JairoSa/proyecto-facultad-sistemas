<?php
session_start();
// Protección de la página para el rol de docente
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');
$page_title = "Panel de Docente";
include('../includes/header.php');

$id_docente = $_SESSION['id'];

// --- CONSULTAS PARA ESTADÍSTICAS ---
// 1. Total de cursos asignados
$total_cursos_stmt = $conn->prepare("SELECT COUNT(id) AS total FROM cursos WHERE id_docente_asignado = ?");
$total_cursos_stmt->bind_param("i", $id_docente);
$total_cursos_stmt->execute();
$total_cursos = $total_cursos_stmt->get_result()->fetch_assoc()['total'];

// 2. Total de estudiantes únicos en todos sus cursos
$total_estudiantes_stmt = $conn->prepare("SELECT COUNT(DISTINCT id_estudiante) AS total FROM inscripciones WHERE id_curso IN (SELECT id FROM cursos WHERE id_docente_asignado = ?)");
$total_estudiantes_stmt->bind_param("i", $id_docente);
$total_estudiantes_stmt->execute();
$total_estudiantes = $total_estudiantes_stmt->get_result()->fetch_assoc()['total'];

// 3. Total de materiales subidos
$total_materiales_stmt = $conn->prepare("SELECT COUNT(id) AS total FROM materiales_curso WHERE id_curso IN (SELECT id FROM cursos WHERE id_docente_asignado = ?)");
$total_materiales_stmt->bind_param("i", $id_docente);
$total_materiales_stmt->execute();
$total_materiales = $total_materiales_stmt->get_result()->fetch_assoc()['total'];


// --- CONSULTA PARA OBTENER LOS CURSOS Y EL CONTEO DE INSCRITOS EN CADA UNO ---
$stmt = $conn->prepare(
    "SELECT c.id, c.nombre, c.descripcion, c.imagen_url, COUNT(i.id) AS inscritos 
     FROM cursos c 
     LEFT JOIN inscripciones i ON c.id = i.id_curso
     WHERE c.id_docente_asignado = ? 
     GROUP BY c.id
     ORDER BY c.fecha_creacion DESC"
);
$stmt->bind_param("i", $id_docente);
$stmt->execute();
$cursos_asignados = $stmt->get_result();
?>

<style>
    .stat-card {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border: none;
        border-radius: 15px;
        text-align: center;
        padding: 1.5rem;
    }
    .stat-card .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--bs-primary);
    }
    .stat-card .stat-label {
        font-size: 1rem;
        color: #555;
        font-weight: 600;
    }
    .course-card-footer {
        border-top: 1px solid #eee;
        padding-top: 0.75rem;
        margin-top: 1rem;
    }
</style>

<main class="container mt-5">
    <div class="text-center mb-5" data-aos="fade-down">
        <h1 class="display-5 fw-bold">Panel de Docente</h1>
        <p class="lead text-muted">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>. Inspira, enseña y gestiona tus cursos.</p>
    </div>

    <div class="row mb-5">
        <div class="col-md-4 mb-3" data-aos="fade-up">
            <div class="stat-card">
                <div class="stat-number"><?= $total_cursos ?></div>
                <div class="stat-label">Cursos Asignados</div>
            </div>
        </div>
        <div class="col-md-4 mb-3" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-number"><?= $total_estudiantes ?></div>
                <div class="stat-label">Estudiantes Totales</div>
            </div>
        </div>
        <div class="col-md-4 mb-3" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card">
                <div class="stat-number"><?= $total_materiales ?></div>
                <div class="stat-label">Materiales Subidos</div>
            </div>
        </div>
    </div>

    <hr>
    
    <h2 class="page-title mt-5" data-aos="fade-right">Mis Cursos</h2>
    <div class="row">
        <?php if ($cursos_asignados && $cursos_asignados->num_rows > 0): ?>
            <?php while ($curso = $cursos_asignados->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up">
                <div class="card h-100 shadow-sm publication-card">
                    <img src="../assets/img/uploads/<?= htmlspecialchars($curso['imagen_url'] ?: 'default.png') ?>" class="card-img-top" style="height: 180px; object-fit: cover;" alt="<?= htmlspecialchars($curso['nombre']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($curso['nombre']) ?></h5>
                        <p class="card-text small text-muted flex-grow-1"><?= htmlspecialchars(substr($curso['descripcion'], 0, 100)) ?>...</p>
                        
                        <div class="course-card-footer d-flex justify-content-between align-items-center">
                            <span class="text-muted small"><i class="fas fa-users me-1"></i> <?= $curso['inscritos'] ?> Estudiantes</span>
                            <a href="ver_curso.php?id=<?= $curso['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-cog me-1"></i>Gestionar</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted mt-5 py-5">
                <h4 data-aos="zoom-in">Aún no tienes cursos asignados.</h4>
                <p data-aos="zoom-in" data-aos-delay="100">Contacta al administrador para que te asigne a un curso.</p>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php 
$total_cursos_stmt->close();
$total_estudiantes_stmt->close();
$total_materiales_stmt->close();
$stmt->close();
$conn->close();
include('../includes/footer.php'); 
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init();</script>
</body>
</html>
