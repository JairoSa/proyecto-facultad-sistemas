<?php
// Inicia la sesión y protege la página para que solo los usuarios puedan acceder.
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario') {
    header("Location: ../index.php");
    exit();
}

// Incluye la conexión a la base de datos.
include('../config/db.php');

// Define el título de la página y carga el header.
$page_title = "Anuncios y Novedades";
include('../includes/header.php');

// Realiza la consulta para obtener todas las publicaciones, ordenadas por la más reciente.
$result = $conn->query("SELECT * FROM publicaciones ORDER BY fecha_creacion DESC");

// Separa las publicaciones en diferentes arrays según su tipo.
$banners = [];
$cursos_eventos = [];
$anuncios_convocatorias = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['tipo'] == 'banner') {
            $banners[] = $row;
        } elseif ($row['tipo'] == 'curso') {
            $cursos_eventos[] = $row;
        } else {
            $anuncios_convocatorias[] = $row;
        }
    }
}
?>

<main class="container mt-5">

    <section class="text-center mb-5" data-aos="fade-down">
        <h1 class="display-5 fw-bold">¡Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>!</h1>
        <p class="lead text-muted">Aquí encontrarás las últimas noticias, eventos y oportunidades de la Facultad.</p>
    </section>

    <?php if (!empty($banners)): ?>
    <section id="banner-carousel" class="carousel slide shadow-lg mb-5" data-bs-ride="carousel" data-aos="zoom-in">
        <div class="carousel-inner" style="border-radius: 15px;">
            <?php foreach ($banners as $index => $banner): ?>
            <div class="carousel-item <?= $index == 0 ? 'active' : '' ?>">
                <?php if (!empty($banner['imagen_url'])): ?>
                    <img src="../assets/img/uploads/<?= htmlspecialchars($banner['imagen_url']) ?>" class="d-block w-100" style="max-height: 450px; object-fit: cover;" alt="<?= htmlspecialchars($banner['titulo']) ?>">
                <?php endif; ?>
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 p-3" style="border-radius: 10px;">
                    <h5><?= htmlspecialchars($banner['titulo']) ?></h5>
                    <p><?= htmlspecialchars($banner['descripcion']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#banner-carousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#banner-carousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </section>
    <?php endif; ?>

    <?php if (!empty($cursos_eventos)): ?>
    <section class="mb-5" data-aos="fade-up">
        <h2 class="display-6 border-bottom pb-2 mb-4"><i class="fas fa-calendar-alt me-2 text-primary"></i>Cursos y Eventos</h2>
        <div class="row">
            <?php foreach ($cursos_eventos as $curso): ?>
            <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card h-100 shadow-sm publication-card">
                    <?php if (!empty($curso['imagen_url'])): ?>
                        <img src="../assets/img/uploads/<?= htmlspecialchars($curso['imagen_url']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($curso['titulo']) ?>">
                    <?php endif; ?>
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
            <div class="list-group-item list-group-item-action flex-column align-items-start mb-3 shadow-sm" data-aos="fade-left" style="border-radius: 10px;">
                <div class="d-flex w-100">
                    <?php if (!empty($anuncio['imagen_url'])): ?>
			<div class="me-3" style="width: 120px; flex-shrink: 0;">
			<a href="../assets/img/uploads/<?= htmlspecialchars($anuncio['imagen_url']) ?>" data-lightbox="anuncios-galeria" data-title="<?= htmlspecialchars($anuncio['titulo']) ?>">
                        <img src="../assets/img/uploads/<?= htmlspecialchars($anuncio['imagen_url']) ?>" class="img-thumbnail me-3" style="width: 120px; height: 80px; object-fit: cover;" alt="<?= htmlspecialchars($anuncio['titulo']) ?>">
    		       </a>
		   </div>
                    <?php endif; ?>
                    <div class="flex-grow-1">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1 text-primary"><?= htmlspecialchars($anuncio['titulo']) ?></h5>
                            <small class="text-muted"><?= date("d/m/Y", strtotime($anuncio['fecha_creacion'])) ?></small>
                        </div>
                        <p class="mb-1"><?= nl2br(htmlspecialchars($anuncio['descripcion'])) ?></p>
                        <small class="badge bg-info text-dark"><?= ucfirst(htmlspecialchars($anuncio['tipo'])) ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (empty($banners) && empty($cursos_eventos) && empty($anuncios_convocatorias)): ?>
        <div class="text-center text-muted mt-5">
            <p>Aún no hay publicaciones disponibles. ¡Vuelve pronto!</p>
        </div>
    <?php endif; ?>
    
</main>

<?php 
// Carga el footer y los scripts.
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
