<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');

$page_title = "Panel Principal";
include('../includes/header.php');

// --- OBTENER DATOS DE LAS TABLAS ---
// 1. Obtiene las publicaciones (banners, anuncios y convocatorias).
$result_publicaciones = $conn->query("SELECT * FROM publicaciones ORDER BY fecha_creacion DESC");
$banners = [];
$anuncios_convocatorias = [];
if ($result_publicaciones && $result_publicaciones->num_rows > 0) {
    while ($row = $result_publicaciones->fetch_assoc()) {
        if ($row['tipo'] == 'banner') {
            $banners[] = $row;
        } else {
            $anuncios_convocatorias[] = $row;
        }
    }
}

// 2. Obtiene los cursos de la tabla 'cursos'.
$cursos = $conn->query("SELECT c.*, u.nombre as nombre_docente FROM cursos c LEFT JOIN usuarios u ON c.id_docente_asignado = u.id ORDER BY c.fecha_creacion DESC");
?>

<style>
    /* Estilos específicos para el panel de estudiante */
    .hero-student {
        padding: 4rem 2rem;
        background-color: #f8f9fa;
        border-radius: 15px;
        margin-bottom: 3rem;
    }
    .publication-card .card-img-top {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }
</style>

<main class="container mt-5">

    <?php
    // --- INICIO: CÓDIGO PARA MOSTRAR MENSAJES DE INSCRIPCIÓN ---
    if (isset($_GET['mensaje'])) {
        $mensaje_texto = '';
        $tipo_alerta = 'info';
        switch ($_GET['mensaje']) {
            case 'inscripcion_exitosa':
                $mensaje_texto = '¡Felicidades! Te has inscrito al curso correctamente.';
                $tipo_alerta = 'success';
                break;
            case 'ya_inscrito':
                $mensaje_texto = 'Ya te encuentras inscrito en este curso.';
                $tipo_alerta = 'warning';
                break;
            case 'curso_lleno':
                $mensaje_texto = 'Lo sentimos, el curso ha alcanzado su límite de inscritos.';
                $tipo_alerta = 'danger';
                break;
            case 'error_general':
                $mensaje_texto = 'Ocurrió un error inesperado. Por favor, inténtalo de nuevo.';
                $tipo_alerta = 'danger';
                break;
        }
        // Muestra la alerta de Bootstrap
        echo "<div class='alert alert-{$tipo_alerta} alert-dismissible fade show' role='alert' data-aos='fade-down'>
                {$mensaje_texto}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
    // --- FIN: CÓDIGO PARA MOSTRAR MENSAJES ---
    ?>

    <section class="hero-student" data-aos="fade-down">
        <h1 class="display-5 fw-bold">¡Bienvenido de nuevo, <?= htmlspecialchars($_SESSION['nombre']) ?>!</h1>
        <p class="lead text-muted">Explora los últimos cursos y anuncios de la facultad. ¡Sigue aprendiendo!</p>
    </section>

    <?php if (!empty($banners)): ?>
    <section id="banner-carousel" class="carousel slide shadow-lg mb-5" data-bs-ride="carousel" data-aos="zoom-in">
        <div class="carousel-inner" style="border-radius: 15px;">
            <?php foreach ($banners as $index => $banner): ?>
            <div class="carousel-item <?= $index == 0 ? 'active' : '' ?>">
                <?php if (!empty($banner['imagen_url'])): ?>
                    <img src="../assets/img/uploads/<?= htmlspecialchars($banner['imagen_url']) ?>" class="d-block w-100" style="max-height: 400px; object-fit: cover;" alt="<?= htmlspecialchars($banner['titulo']) ?>">
                <?php endif; ?>
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 p-3" style="border-radius: 10px;">
                    <h5><?= htmlspecialchars($banner['titulo']) ?></h5>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#banner-carousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#banner-carousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </section>
    <?php endif; ?>

    <?php if ($cursos && $cursos->num_rows > 0): ?>
    <section class="mb-5" data-aos="fade-up">
        <h2 class="page-title"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Cursos Disponibles</h2>
        <div class="row">
            <?php while ($curso = $cursos->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 shadow-sm publication-card">
                    <img src="../assets/img/uploads/<?= htmlspecialchars($curso['imagen_url'] ?: 'default.png') ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($curso['nombre']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($curso['nombre']) ?></h5>
                        <?php if(!empty($curso['nombre_docente'])): ?>
                            <p class="card-subtitle mb-2 text-muted"><small>Por: <?= htmlspecialchars($curso['nombre_docente']) ?></small></p>
                        <?php endif; ?>
                        <p class="card-text flex-grow-1 small"><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>
                        <a href="inscribir_curso.php?id=<?= $curso['id'] ?>" class="btn btn-primary mt-auto">
                            <i class="fas fa-user-plus me-2"></i>Inscribirse al Curso
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($anuncios_convocatorias)): ?>
    <section data-aos="fade-up">
        <h2 class="page-title"><i class="fas fa-bullhorn me-2 text-primary"></i>Anuncios y Convocatorias</h2>
        <div class="list-group">
            <?php foreach ($anuncios_convocatorias as $anuncio): ?>
            <div class="list-group-item list-group-item-action d-flex gap-3 py-3 shadow-sm mb-3" style="border-radius: 15px;">
                <?php if (!empty($anuncio['imagen_url'])): ?>
                    <div style="width: 100px; flex-shrink: 0;">
                        <a href="../assets/img/uploads/<?= htmlspecialchars($anuncio['imagen_url']) ?>" data-lightbox="anuncios-galeria" data-title="<?= htmlspecialchars($anuncio['titulo']) ?>">
                            <img src="../assets/img/uploads/<?= htmlspecialchars($anuncio['imagen_url']) ?>" alt="imagen" width="100" height="100" class="rounded-circle" style="object-fit: cover;">
                        </a>
                    </div>
                <?php endif; ?>
                <div class="d-flex gap-2 w-100 justify-content-between">
                    <div>
                        <h6 class="mb-0"><?= htmlspecialchars($anuncio['titulo']) ?></h6>
                        <p class="mb-0 opacity-75 small"><?= nl2br(htmlspecialchars($anuncio['descripcion'])) ?></p>
                        <small class="badge bg-info text-dark mt-2"><?= ucfirst(htmlspecialchars($anuncio['tipo'])) ?></small>
                    </div>
                    <small class="opacity-50 text-nowrap"><?= date("d/m/Y", strtotime($anuncio['fecha_creacion'])) ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
</main>

<?php 
include('../includes/footer.php'); 
$conn->close();
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init({
      duration: 800,
      once: true,
  });
</script>

</body>
</html>
