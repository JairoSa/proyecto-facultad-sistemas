<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}
$page_title = "Panel de Administración";
include('../includes/header.php');
?>

<main class="container mt-5">
    <div class="text-center" data-aos="fade-down">
        <h1 class="display-4">Panel de Administración</h1>
        <p class="lead">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>. Gestiona el contenido del sitio desde aquí.</p>
    </div>

    <div class="row mt-5 text-center">
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card shadow-lg dashboard-card">
                <div class="card-body">
                    <i class="fas fa-plus-circle fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Crear Publicación</h5>
                    <p class="card-text">Crea nuevos anuncios, cursos, banners o convocatorias.</p>
                    <a href="gestionar_publicacion.php" class="btn btn-primary">Ir a Crear</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card shadow-lg dashboard-card">
                <div class="card-body">
                    <i class="fas fa-list-alt fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Listar Publicaciones</h5>
                    <p class="card-text">Visualiza, edita o elimina las publicaciones existentes.</p>
                    <a href="listar_publicaciones.php" class="btn btn-success">Ver Lista</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card shadow-lg dashboard-card">
                <div class="card-body">
                    <i class="fas fa-envelope fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Enviar Correos</h5>
                    <p class="card-text">Notifica a los estudiantes sobre la última publicación.</p>
                    <a href="enviar_correos.php" class="btn btn-warning">Enviar Notificación</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include('../includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init();</script>