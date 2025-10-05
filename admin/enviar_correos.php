<?php
// Inicia la sesión y protege la página.
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Incluye la conexión a la BD y el autoloader de Composer.
include('../config/db.php');
require '../vendor/autoload.php'; // Carga PHPMailer

// Usa las clases de PHPMailer.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensaje_feedback = '';

// Si el administrador ha confirmado el envío.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Obtener la última publicación.
    $result_pub = $conn->query("SELECT * FROM publicaciones ORDER BY fecha_creacion DESC LIMIT 1");
    $ultima_publicacion = $result_pub->fetch_assoc();

    // 2. Obtener todos los correos de los estudiantes.
    $result_est = $conn->query("SELECT correo_institucional FROM estudiantes");
    
    if ($ultima_publicacion && $result_est->num_rows > 0) {
        
        $mail = new PHPMailer(true); // Crea una nueva instancia de PHPMailer.

        try {
            // --- CONFIGURACIÓN DEL SERVIDOR DE CORREO (SMTP con Gmail) ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jairosantosm25@gmail.com'; // TU CORREO DE GMAIL
            $mail->Password   = 'hzyf atdn mawd juvx'; // LA CONTRASEÑA DE APLICACIÓN DE 16 LETRAS
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // --- CONFIGURACIÓN DEL MENSAJE ---
            $mail->setFrom('tu_correo@gmail.com', 'Facultad de Sistemas - UNDAC');
            $mail->isHTML(true);
            $mail->Subject = 'Nuevo Anuncio: ' . $ultima_publicacion['titulo'];
            
            // Cuerpo del correo en formato HTML.
            $mail->Body    = "<h1>{$ultima_publicacion['titulo']}</h1>
                              <p>{$ultima_publicacion['descripcion']}</p>
                              <p>Para más detalles, visita nuestra página.</p>
                              <p><br><em>Facultad de Ingeniería de Sistemas - UNDAC</em></p>";
            
            // Bucle para añadir todos los destinatarios.
            while ($estudiante = $result_est->fetch_assoc()) {
                $mail->addBCC($estudiante['correo_institucional']);
            }

            $mail->send();
            $mensaje_feedback = "<div class='alert alert-success'>El anuncio ha sido enviado a {$result_est->num_rows} estudiantes.</div>";

        } catch (Exception $e) {
            $mensaje_feedback = "<div class='alert alert-danger'>El mensaje no se pudo enviar. Error de PHPMailer: {$mail->ErrorInfo}</div>";
        }
    } else {
        $mensaje_feedback = "<div class='alert alert-warning'>No hay publicaciones recientes o no hay estudiantes registrados para enviar correos.</div>";
    }
}

// Define el título de la página y carga el header.
$page_title = "Enviar Notificaciones por Correo";
include('../includes/header.php');
?>

<div class="container mt-5" data-aos="fade-up">
    <h2><i class="fas fa-envelope me-2"></i>Enviar Último Anuncio por Correo</h2>
    <p class="text-muted">Esta herramienta enviará la publicación más reciente a todos los estudiantes registrados en la base de datos.</p>
    
    <?php 
    // Muestra el mensaje de éxito o error después de enviar.
    echo $mensaje_feedback; 
    
    // Obtiene el último anuncio para mostrarlo como vista previa.
    $result = $conn->query("SELECT * FROM publicaciones ORDER BY fecha_creacion DESC LIMIT 1");
    $ultima_publicacion = $result->fetch_assoc();
    ?>

    <?php if ($ultima_publicacion): ?>
        <div class="card my-4">
            <div class="card-header">
                Vista Previa del Anuncio a Enviar
            </div>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($ultima_publicacion['titulo']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($ultima_publicacion['descripcion']) ?></p>
                <p class="card-text"><small class="text-muted">Publicado el: <?= date("d/m/Y", strtotime($ultima_publicacion['fecha_creacion'])) ?></small></p>
            </div>
        </div>

        <form method="POST">
            <button type="submit" class="btn btn-warning btn-lg" onclick="return confirm('¿Estás seguro de que deseas enviar este anuncio a todos los estudiantes?')">
                <i class="fas fa-paper-plane me-2"></i>Confirmar y Enviar Notificación
            </button>
        </form>
    <?php else: ?>
        <div class="alert alert-info mt-4">No hay ninguna publicación para enviar.</div>
    <?php endif; ?>
</div>

<?php 
// Carga el footer y los scripts.
include('../includes/footer.php'); 
$conn->close();
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init();</script>
</body>
</html>
