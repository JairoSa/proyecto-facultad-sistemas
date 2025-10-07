<?php
session_start();
// Protección de la página
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');
$page_title = "Mis Cursos";
include('../includes/header.php');

$id_estudiante = $_SESSION['id'];

// --- CONSULTA CORREGIDA Y SEGURA PARA OBTENER LOS CURSOS INSCRITOS ---
$stmt = $conn->prepare(
    "SELECT c.id, c.nombre, c.descripcion, c.imagen_url, u.nombre AS nombre_docente 
     FROM cursos c 
     JOIN inscripciones i ON c.id = i.id_curso 
     LEFT JOIN usuarios u ON c.id_docente_asignado = u.id
     WHERE i.id_estudiante = ? 
     ORDER BY i.fecha_inscripcion DESC"
);
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$cursos_inscritos = $stmt->get_result();

?>
<main class="container mt-5">
    <h1 class="page-title" data-aos="fade-down">Mi Aprendizaje</h1>
    <p class="lead text-muted" data-aos="fade-down">Aquí encontrarás todos los cursos en los que te has inscrito. ¡Sigue adelante!</p>
    <hr class.="mb-5">

    <div class="row">
        <?php if ($cursos_inscritos && $cursos_inscritos->num_rows > 0): ?>
            <?php while ($curso = $cursos_inscritos->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up">
                <div class="card h-100 shadow-sm publication-card">
                    <img src="../assets/img/uploads/<?= htmlspecialchars($curso['imagen_url'] ?: 'default.png') ?>" class="card-img-top" style="height: 180px; object-fit: cover;" alt="<?= htmlspecialchars($curso['nombre']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($curso['nombre']) ?></h5>
                        <?php if(!empty($curso['nombre_docente'])): ?>
                            <p class="card-subtitle mb-2 text-muted"><small>Por: <?= htmlspecialchars($curso['nombre_docente']) ?></small></p>
                        <?php endif; ?>
                        <p class="card-text small text-muted flex-grow-1"><?= htmlspecialchars(substr($curso['descripcion'], 0, 100)) ?>...</p>
                        <a href="ver_curso.php?id=<?= $curso['id'] ?>" class="btn btn-primary mt-auto"><i class="fas fa-play-circle me-2"></i>Continuar con el curso</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted mt-5 py-5">
                <h4 data-aos="zoom-in">Aún no te has inscrito a ningún curso.</h4>
                <p data-aos="zoom-in" data-aos-delay="100">Explora los <a href="anuncios.php">cursos disponibles</a> y empieza a aprender.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php 
$stmt->close();
$conn->close();
include('../includes/footer.php'); 
?>
<script src="https://cdn.jsdelivr/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init();</script>
</body>
</html>
