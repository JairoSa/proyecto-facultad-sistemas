<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}
$page_title = "Guía de Administrador";
include('../includes/header.php');
?>
<main class="container mt-5">
    <h1 class="display-5" data-aos="fade-down">Guía Rápida para Administradores</h1>
    <p class="lead text-muted" data-aos="fade-down">Bienvenido a la guía de uso del panel de administración de la Facultad de Ingeniería de Sistemas - UNDAC.</p>
    <hr>
    <div class="row g-4">
        <div class="col-md-6" data-aos="fade-right">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title"><i class="fas fa-file-alt me-2 text-primary"></i>Gestión de Contenidos</h4>
                    <p><strong>Crear Publicaciones:</strong> Utiliza esta sección para anuncios generales, noticias, banners para el carrusel y convocatorias. Cada elemento se mostrará en la página principal de los estudiantes.</p>
                    <p><strong>Gestionar Cursos:</strong> Esta es el área principal para la oferta académica. Aquí puedes crear un nuevo curso, y luego editarlo para asignarle un docente responsable y definir el límite de estudiantes que pueden inscribirse.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6" data-aos="fade-left">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title"><i class="fas fa-users me-2 text-primary"></i>Gestión de Usuarios</h4>
                    <p><strong>Crear Usuarios:</strong> Desde "Gestionar Usuarios", puedes crear cuentas para los tres roles del sistema: Administrador, Docente y Estudiante. Es importante asignar el rol correcto a cada nuevo usuario.</p>
                    <p><strong>Eliminar Usuarios:</strong> En la misma sección, puedes eliminar cuentas que ya no sean necesarias. Ten en cuenta que no puedes eliminar tu propia cuenta.</p>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include('../includes/footer.php'); ?>
