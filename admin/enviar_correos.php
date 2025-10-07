<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensaje_feedback = '';
$contenido_reciente = null;

// --- LÓGICA PARA ENCONTRAR EL CONTENIDO MÁS RECIENTE (CURSO O PUBLICACIÓN) ---
$query = "
    (SELECT nombre AS titulo, descripcion, fecha_creacion, 'Curso' AS tipo FROM cursos)
    UNION
    (SELECT titulo, descripcion, fecha_creacion, 'Publicacion' AS tipo FROM publicaciones)
    ORDER BY fecha_creacion DESC LIMIT 1
";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    $contenido_reciente = $result->fetch_assoc();
}

// --- LÓGICA PARA ENVIAR EL CORREO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $contenido_reciente) {
    $result_est = $conn->query("SELECT correo_institucional FROM estudiantes");
    
    if ($result_est && $result_est->num_rows > 0) {
        $mail = new PHPMailer(true);
        try {
            // --- CONFIGURACIÓN DEL SERVIDOR DE CORREO (SMTP con Gmail) ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jairosantosm25@gmail.com'; // TU CORREO DE GMAIL
            $mail->Password   = 'hzyf atdn mawd juvx'; // TU CONTRASEÑA DE APLICACIÓN
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // --- CONFIGURACIÓN DEL MENSAJE ---
            $mail->setFrom('tu_correo@gmail.com', 'Facultad de Sistemas - UNDAC');
            $mail->isHTML(true);
            $mail->Subject = 'Nuevo ' . $contenido_reciente['tipo'] . ': ' . $contenido_reciente['titulo'];
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h1 style='color: #004aad;'>{$contenido_reciente['titulo']}</h1>
                    <p style='font-size: 16px;'>" . nl2br($contenido_reciente['descripcion']) . "</p>
                    <p>Para más detalles, visita nuestra página web.</p><hr>
                    <p style='font-size: 12px; color: #777;'><em>Facultad de Ingeniería de Sistemas - UNDAC</em></p>
                </div>";
            
            while ($estudiante = $result_est->fetch_assoc()) {
                $mail->addAddress($estudiante['correo_institucional']);
            }

            $mail->send();
            $mensaje_feedback = "<div class='alert alert-success'>La notificación ha sido enviada a {$result_est->num_rows} estudiantes.</div>";

        } catch (Exception $e) {
            $mensaje_feedback = "<div class='alert alert-danger'>El mensaje no se pudo enviar. Error: {$mail->ErrorInfo}</div>";
        }
    } else {
        $mensaje_feedback = "<div class='alert alert-warning'>No hay estudiantes registrados para enviar correos.</div>";
    }
}

$page_title = "Enviar Notificaciones";
include('../includes/header.php');
?>

<main class="container mt-5">
    <h1 class="page-title" data-aos="fade-down">Enviar Notificación por Correo</h1>
    <?= $mensaje_feedback; ?>

    <div class="card content-card shadow-sm" data-aos="fade-up">
        <div class="card-body">
            <h4 class="mb-3"><i class="fas fa-paper-plane me-2 text-primary"></i>Último Contenido Registrado</h4>
            <p class="text-muted">El sistema enviará una notificación por correo sobre el último contenido creado (ya sea un curso o una publicación).</p>
            
            <?php if ($contenido_reciente): ?>
                <div class="card my-4 bg-light">
                    <div class="card-header fw-bold">
                        Vista Previa (Tipo: <?= htmlspecialchars($contenido_reciente['tipo']) ?>)
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($contenido_reciente['titulo']) ?></h5>
                        <p class="card-text"><?= nl2br(htmlspecialchars($contenido_reciente['descripcion'])) ?></p>
                        <p class="card-text"><small class="text-muted">Publicado el: <?= date("d/m/Y H:i", strtotime($contenido_reciente['fecha_creacion'])) ?></small></p>
                    </div>
                </div>

                <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas enviar esta notificación a todos los estudiantes?');">
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-envelope me-2"></i>Confirmar y Enviar a Todos
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-info mt-4">No hay ninguna publicación o curso para enviar.</div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php 
$conn->close();
include('../includes/footer.php'); 
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init();</script>
</body>
</html>
