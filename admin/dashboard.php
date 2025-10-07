<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}
// Incluir la conexión a la base de datos para las "Últimas Acciones"
include('../config/db.php');

$page_title = "Panel de Administración";
include('../includes/header.php');

// --- Lógica para obtener "Últimas Acciones" ---
$ultimas_acciones = [];

// Últimas 3 publicaciones creadas
$stmt_publicaciones = $conn->prepare("SELECT titulo, fecha_creacion, 'publicacion' as tipo FROM publicaciones ORDER BY fecha_creacion DESC LIMIT 3");
$stmt_publicaciones->execute();
$res_publicaciones = $stmt_publicaciones->get_result();
while ($row = $res_publicaciones->fetch_assoc()) {
    $ultimas_acciones[] = $row;
}
$stmt_publicaciones->close();

// Últimos 3 cursos creados
$stmt_cursos = $conn->prepare("SELECT nombre as titulo, fecha_creacion, 'curso' as tipo FROM cursos ORDER BY fecha_creacion DESC LIMIT 3");
$stmt_cursos->execute();
$res_cursos = $stmt_cursos->get_result();
while ($row = $res_cursos->fetch_assoc()) {
    $ultimas_acciones[] = $row;
}
$stmt_cursos->close();

// Ordenar todas las acciones por fecha (las más recientes primero)
usort($ultimas_acciones, function($a, $b) {
    return strtotime($b['fecha_creacion']) - strtotime($a['fecha_creacion']);
});

// Limitar a las 5 acciones más recientes
$ultimas_acciones = array_slice($ultimas_acciones, 0, 5);

?>

<style>
    /* Estilos adicionales para un diseño más bonito */
    .dashboard-card { transition: transform 0.2s ease, box-shadow 0.2s ease; border: none; border-radius: 15px; background-color: #fff; }
    .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .dashboard-card .card-body { padding: 30px; }
    .hero-section { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); color: white; padding: 60px 0; border-radius: 15px; margin-bottom: 3rem; }
    .hero-section h1 { font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }
    .footer-section { background-color: #1a1a1a; color: #ddd; padding: 50px 0; margin-top: 5rem; }
    .footer-section h5 { color: #fff; margin-bottom: 20px; font-weight: bold; }
    .footer-section ul { list-style: none; padding: 0; }
    .footer-section ul li { margin-bottom: 10px; }
    .footer-section ul li a { color: #ddd; text-decoration: none; transition: color 0.2s ease; }
    .footer-section ul li a:hover { color: #fff; }
    .footer-bottom { border-top: 1px solid #333; padding-top: 20px; margin-top: 30px; font-size: 0.9em; color: #aaa; }
</style>

<main class="container mt-5">
    
    <section class="hero-section text-center" data-aos="fade-down">
        <h1 class="display-4 mb-3">Panel de Administración</h1>
        <p class="lead">Desde aquí tienes el control total de la plataforma de la Facultad.</p>
    </section>

    <div id="dashboardCarousel" class="carousel slide my-5 shadow-lg" data-bs-ride="carousel" data-aos="zoom-in">
        <div class="carousel-inner" style="border-radius: 15px;">
            <div class="carousel-item active">
                <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=2070&auto=format&fit=crop" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="Desarrollo de Software">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 p-3 rounded">
                    <h5>Desarrollo e Innovación</h5>
                    <p>Mantente al día con las últimas tendencias en tecnología.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1558494949-ef010cbdcc31?q=80&w=1974&auto=format&fit=crop" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="Infraestructura de Redes">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-75 p-3 rounded">
                    <h5>Infraestructura Sólida</h5>
                    <p>La base tecnológica es fundamental para el éxito académico.</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#dashboardCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#dashboardCarousel" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </div>

    <h3 class="border-bottom pb-2 mb-4" data-aos="fade-right"><i class="fas fa-th-large me-2 text-primary"></i>Acciones Rápidas</h3>
    <div class="row g-4 mb-5">
        <div class="col-lg col-md-4 col-6" data-aos="fade-up" data-aos-delay="100"> <a href="gestionar_publicacion.php" class="card shadow-sm dashboard-card h-100 text-decoration-none"> <div class="card-body text-center d-flex flex-column justify-content-center"> <i class="fas fa-plus-circle fa-3x mb-3"></i> <h5 class="card-title text-dark">Crear Publicación</h5> </div> </a> </div>
        <div class="col-lg col-md-4 col-6" data-aos="fade-up" data-aos-delay="200"> <a href="listar_publicaciones.php" class="card shadow-sm dashboard-card h-100 text-decoration-none"> <div class="card-body text-center d-flex flex-column justify-content-center"> <i class="fas fa-list-alt fa-3x mb-3"></i> <h5 class="card-title text-dark">Listar Publicaciones</h5> </div> </a> </div>
        <div class="col-lg col-md-4 col-6" data-aos="fade-up" data-aos-delay="300"> <a href="gestionar_cursos.php" class="card shadow-sm dashboard-card h-100 text-decoration-none"> <div class="card-body text-center d-flex flex-column justify-content-center"> <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i> <h5 class="card-title text-dark">Gestionar Cursos</h5> </div> </a> </div>
        <div class="col-lg col-md-4 col-6" data-aos="fade-up" data-aos-delay="400"> <a href="gestionar_usuarios.php" class="card shadow-sm dashboard-card h-100 text-decoration-none"> <div class="card-body text-center d-flex flex-column justify-content-center"> <i class="fas fa-users-cog fa-3x mb-3"></i> <h5 class="card-title text-dark">Gestionar Usuarios</h5> </div> </a> </div>
        <div class="col-lg col-md-4 col-6" data-aos="fade-up" data-aos-delay="500"> <a href="enviar_correos.php" class="card shadow-sm dashboard-card h-100 text-decoration-none"> <div class="card-body text-center d-flex flex-column justify-content-center"> <i class="fas fa-envelope fa-3x mb-3"></i> <h5 class="card-title text-dark">Enviar Correos</h5> </div> </a> </div>
    </div>

    <section class="mt-5" data-aos="fade-up" data-aos-delay="600">
        <h3 class="border-bottom pb-2 mb-4"><i class="fas fa-history me-2 text-secondary"></i>Actividad Reciente</h3>
        <div class="list-group shadow-sm">
            <?php if (!empty($ultimas_acciones)): ?>
                <?php foreach ($ultimas_acciones as $accion): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary me-2"><?= ucfirst(htmlspecialchars($accion['tipo'])) ?></span>
                            <?= htmlspecialchars($accion['titulo']) ?>
                        </div>
                        <small class="text-muted"><?= date("d/m/Y H:i", strtotime($accion['fecha_creacion'])) ?></small>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="list-group-item text-muted text-center">No hay acciones recientes.</li>
            <?php endif; ?>
        </div>
    </section>
</main>

<footer class="footer-section mt-5" data-aos="fade-up">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5>Recursos Rápidos</h5>
                <ul>
                    <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                    <li><a href="gestionar_publicacion.php">Crear Publicación</a></li>
                    <li><a href="gestionar_usuarios.php">Administrar Usuarios</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Soporte</h5>
                <ul>
                    <li><a href="guia_admin.php">Guía de Administrador</a></li>
                    <li><a href="faq.php">Preguntas Frecuentes</a></li>
                    <li><a href="#">Contactar a Soporte</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5>Legal</h5>
                <ul>
                    <li><a href="terminos.php">Términos y Condiciones</a></li>
                    <li><a href="privacidad.php">Política de Privacidad</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom text-center">
            &copy; <?= date("Y") ?> UNIVERSIDAD NACIONAL DANIEL ALCIDES CARRION. Todos los derechos reservados.
        </div>
    </div>
</footer>

<?php 
$conn->close();
include('../includes/footer.php'); 
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true,
        offset: 50,
    });
</script>
</body>
</html>
