<?php
// 1. INICIAR Y PROTEGER LA SESIÓN
session_start();

// Si no hay una sesión iniciada o el rol no es 'usuario', se redirige al login.
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: ../index.php");
    exit();
}

// 2. INCLUIR ARCHIVOS NECESARIOS
include('../config/db.php'); // Conexión a la base de datos

// Se define el título de la página que se usará en el header
$page_title = "Anuncios y Novedades"; 
include('../includes/header.php'); // Cabecera HTML, CSS y menú

// 3. OBTENER Y ORGANIZAR LOS DATOS DE LA BASE DE DATOS
// Se obtiene todo el contenido ordenado por el más reciente
$result = $conn->query("SELECT * FROM publicaciones ORDER BY fecha_creacion DESC");

// Se crean arrays para clasificar cada tipo de publicación
$banners = [];
$cursos_eventos = [];
$anuncios_convocatorias = [];

// Se recorren los resultados y se guarda cada uno en su array correspondiente
while ($row = $result->fetch_assoc()) {
    if ($row['tipo'] == 'banner') {
        $banners[] = $row;
    } elseif ($row['tipo'] == 'curso') {
        $cursos_eventos[] = $row;
    } else {
        $anuncios_convocatorias[] = $row;
    }
}
$conn->close(); // Se cierra la conexión a la BD
?>

<main class="container mt-5">

    <section class="text-center mb-5" data-aos="fade-down">
        <h1 class="display-5 fw-bold">¡Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>!</h1>
        <p class="lead text-muted">Aquí encontrarás las últimas noticias, eventos y oportunidades de la Facultad.</p>
    </section>

    <?php if (!empty($banners)): ?>
    <section class="mb-5" data-aos="zoom-in">
        <div id="bannerCarousel" class="carousel slide carousel-fade shadow-lg" data-bs-ride="carousel" style="border-radius: 15px; overflow: hidden;">
            <div class="carousel-inner">
                <?php foreach ($banners as $index => $banner): ?>
                <div class="carousel-item <?= $index == 0 ? 'active' : '' ?>">
                    <img src="../assets/img/uploads/<?= htmlspecialchars($banner['imagen_url']) ?>" class="d-block w-100" style="max-height: 450px; object-fit: cover;" alt="<?= htmlspecialchars($banner['titulo']) ?>">
                    <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 p-3 rounded">
                        <h5><?= htmlspecialchars($banner['titulo']) ?></h5>
                        <p><?= htmlspecialchars($banner['descripcion']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($cursos_eventos)): ?>
    <section class="mb-5" data-aos="fade-up">
        <h2 class="display-6 border-bottom pb-2 mb-4"><i class="fas fa-calendar-alt me-2 text-primary"></i>Cursos y Eventos</h2>
        <div class="row">
            <?php foreach ($cursos_eventos as $curso): ?>
            <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 shadow-sm publication-card">
                    <img src="../assets/img/uploads/<?= htmlspecialchars($curso['imagen_url']) ?>" class="card-img-top" alt="Imagen del curso">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($curso['titulo']) ?></h5>
                        <p class="card-text flex-grow-1"><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>
                        <?php if (!empty($curso['link_externo'])): ?>
                            <a href="<?= htmlspecialchars($curso['link_externo']) ?>" target="_blank" class="btn btn-success mt-auto"><i class="fas fa-arrow-right me-2"></i>Inscribirse o Ver Más</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-muted">
                        <small>Publicado: <?= date("d/m/Y", strtotime($curso['fecha_creacion'])) ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($anuncios_convocatorias)): ?>
    <section data-aos="fade-up">
        <h2 class="display-6 border-bottom pb-2 mb-4"><i class="fas fa-bullhorn me-2 text-primary"></i>Anuncios y Convocatorias</h2>
        <div class="list-group">
            <?php foreach ($anuncios_convocatorias as $anuncio): ?>
            <div class="list-group-item list-group-item-action flex-column align-items-start mb-3 shadow-sm" data-aos="fade-left" style="border-radius: 10px; border-left: 5px solid #007bff;">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1 text-primary"><?= htmlspecialchars($anuncio['titulo']) ?></h5>
                    <small class="text-muted"><?= date("d/m/Y", strtotime($anuncio['fecha_creacion'])) ?></small>
                </div>
                <p class="mb-1"><?= nl2br(htmlspecialchars($anuncio['descripcion'])) ?></p>
                <small class="badge bg-info text-dark"><?= ucfirst($anuncio['tipo']) ?></small>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
</main>

<?php 
// 5. INCLUIR EL PIE DE PÁGINA Y SCRIPTS
include('../includes/footer.php'); 
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  // Inicializar la librería de animaciones
  AOS.init({
      duration: 800, // Duración de la animación
      once: true,    // La animación ocurre solo una vez
  });
</script>

</body>
</html>