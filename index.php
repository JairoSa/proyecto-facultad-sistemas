<?php
// Incluir la configuración de la base de datos
include('config/db.php');
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Iniciar la sesión para manejar las variables del usuario
session_start();

// Si el usuario ya ha iniciado sesión, redirigirlo a su panel correspondiente
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: usuario/anuncios.php");
    }
    exit();
}

$error = ''; // Variable para almacenar mensajes de error

// Procesar el formulario solo si se envió usando el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$correo = trim($_POST['correo']);
	$password = trim($_POST['password']);

    // Validar que los campos no estén vacíos
    if (empty($correo) || empty($password)) {
        $error = "Por favor, ingrese su correo y contraseña.";
    } else {
        // Usar sentencias preparadas para prevenir inyección SQL
        $query = $conn->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE correo = ?");
        $query->bind_param("s", $correo);
        $query->execute();
        $resultado = $query->get_result();

        if ($resultado->num_rows > 0) {
            $usuario = $resultado->fetch_assoc();
            
            // Verificar si la contraseña ingresada coincide con la hasheada en la BD
            if (password_verify($password, $usuario['password'])) {
                // Si la contraseña es correcta, guardar datos en la sesión
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
		// --- INICIO: CÓDIGO PARA ENVIAR CORREO AL ESTUDIANTE ---
    if ($usuario['rol'] === 'usuario') {
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor (igual que en enviar_correos.php)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jairosantosm25@gmail.com';     // TU CORREO DE GMAIL
            $mail->Password   = 'hzyf atdn mawd juvx';     // TU CONTRASEÑA DE APLICACIÓN
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Remitente y Destinatario
            $mail->setFrom('tu_correo@gmail.com', 'Facultad de Sistemas - UNDAC');
            // Añadimos el correo y nombre del usuario que acaba de iniciar sesión
            $mail->addAddress($usuario['correo'], $usuario['nombre']);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Notificación de Inicio de Sesión';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif;'>
                    <h3>Hola, " . htmlspecialchars($usuario['nombre']) . "</h3>
                    <p>Te informamos que se ha iniciado sesión en tu cuenta en el portal de la Facultad de Sistemas.</p>
                    <p>Si no reconoces esta actividad, por favor contacta al administrador.</p>
                    <br>
                    <p><em>Facultad de Ingeniería de Sistemas - UNDAC</em></p>
                </div>";

            $mail->send();
        } catch (Exception $e) {
            // Si el correo falla, no detenemos el login.
            // Opcional: podrías guardar el error en un log si quisieras.
            // error_log("No se pudo enviar correo de login a {$usuario['correo']}. Error: {$mail->ErrorInfo}");
        }
    }
    // --- FIN: CÓDIGO PARA ENVIAR CORREO ---
                
                // Redirigir según el rol del usuario
                if ($usuario['rol'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } elseif ($usuario['rol'] === 'docente') {
                    header("Location: docente/dashboard.php");
                } else {
                    header("Location: usuario/anuncios.php");
                }
                exit(); // Detener la ejecución del script después de redirigir
            } else {
                $error = "La contraseña es incorrecta. Inténtelo de nuevo.";
            }
        } else {
            $error = "El correo electrónico no se encuentra registrado.";
        }
        $query->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Facultad de Ingeniería de Sistemas</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css?v=1.4">
</head>
<body class="login-moderno-bg">

<div class="container d-flex align-items-center justify-content-center min-vh-100">
  
  <div class="card login-card-moderno" data-aos="zoom-in-up">
      
      <div class="text-center mb-4">
        <img src="assets/documentos/logo_undac.png" alt="Logo UNDAC" class="login-logo">
        <h3 class="mt-3 text-primary fw-bold">UNDAC - Facultad de</h3>
       <h3 class="mt-3 text-primary fw-bold">Ing. de Sistemas y Computación</h3>
        <p class="text-muted">Sistema de Anuncios y Publicidad</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center"><i class="fas fa-exclamation-triangle me-2"></i><?= $error ?></div>
      <?php endif; ?>

      <form method="POST" action="index.php">
        
        <div class="form-group mb-3">
          <label for="correo" class="form-label">Correo Institucional</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" id="correo" name="correo" class="form-control" placeholder="ejemplo@undac.edu.pe" required>
          </div>
        </div>

        <div class="form-group mb-4">
          <label for="password" class="form-label">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
          </div>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg fw-bold">Ingresar</button>
        </div>
      </form>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  // Inicializar la librería de animaciones
  AOS.init();
</script>

</body>
</html>
