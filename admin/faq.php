<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}
$page_title = "Preguntas Frecuentes";
include('../includes/header.php');
?>
<main class="container mt-5">
    <h1 class="display-5" data-aos="fade-down">Preguntas Frecuentes (FAQ)</h1>
    <p class="lead text-muted" data-aos="fade-down">Respuestas a las dudas más comunes sobre la gestión de la plataforma.</p>
    <hr>
    <div class="accordion" id="faqAccordion" data-aos="fade-up">
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">¿Cómo asigno un docente a un curso ya creado?</button></h2>
        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
          <div class="accordion-body">Ve a "Gestionar Cursos", busca el curso en la lista y haz clic en el botón de Editar (✏️). En el formulario de edición, podrás seleccionar un docente de la lista desplegable.</div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingTwo"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">Un docente me informa que no puede subir material, ¿qué hago?</button></h2>
        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body">Primero, verifica en "Gestionar Usuarios" que la cuenta del docente tenga el rol "Docente". Segundo, asegúrate de que el docente esté asignado correctamente al curso en "Gestionar Cursos".</div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="headingThree"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">¿Por qué las notificaciones no le llegan a los estudiantes?</button></h2>
        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body">Las notificaciones en la campanita se generan únicamente para los cursos creados **después** de implementar la función. Para notificar sobre cursos antiguos, puedes usar la herramienta "Enviar Correos", que enviará un email masivo sobre la última publicación o curso creado.</div>
        </div>
      </div>
    </div>
</main>
<?php include('../includes/footer.php'); ?>
