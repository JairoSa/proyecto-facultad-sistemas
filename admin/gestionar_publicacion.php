<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');

// Generar token para evitar envíos duplicados
if (empty($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['form_token'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar el token
    if (!isset($_POST['form_token']) || !hash_equals($_SESSION['form_token'], $_POST['form_token'])) {
        die("Error de validación: Intento de envío duplicado detectado.");
    }
    unset($_SESSION['form_token']);

    // Recoger datos del formulario
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $tipo = $_POST['tipo'];
    $link_externo = trim($_POST['link_externo']) ?? '';
    $id_admin = $_SESSION['id'];

    // Lógica para subir la imagen
    $imagen_url = '';
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $directorio_subida = '../assets/img/uploads/';
        $nombre_archivo = time() . '_' . basename($_FILES['imagen']['name']);
        $ruta_archivo = $directorio_subida . $nombre_archivo;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_archivo)) {
            $imagen_url = $nombre_archivo;
        } else {
            $error = "Hubo un error al subir la imagen.";
        }
    }

    if (empty($error)) {
        // Insertar en la tabla 'publicaciones'
        $stmt = $conn->prepare("INSERT INTO publicaciones (titulo, descripcion, tipo, link_externo, imagen_url, creado_por) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $titulo, $descripcion, $tipo, $link_externo, $imagen_url, $id_admin);

        if ($stmt->execute()) {
            $id_nueva_publicacion = $conn->insert_id;
            
            // --- CÓDIGO PARA GENERAR NOTIFICACIONES DE PUBLICACIÓN ---
            $result_usuarios = $conn->query("SELECT id FROM usuarios WHERE rol = 'usuario'");
            if ($result_usuarios->num_rows > 0) {
                $stmt_notificacion = $conn->prepare("INSERT INTO notificaciones (id_usuario, tipo_contenido, id_contenido) VALUES (?, 'publicacion', ?)");
                while ($usuario = $result_usuarios->fetch_assoc()) {
                    $stmt_notificacion->bind_param("ii", $usuario['id'], $id_nueva_publicacion);
                    $stmt_notificacion->execute();
                }
                $stmt_notificacion->close();
            }
            // --- FIN ---

            header("Location: listar_publicaciones.php?exito=creada");
            exit();
        } else {
            $error = "Hubo un error al guardar los datos.";
        }
    }
}

$page_title = "Crear Publicación";
include('../includes/header.php');
?>

<main class="container mt-5">
    <h1 class="page-title" data-aos="fade-down">Crear Nueva Publicación</h1>

    <div class="card content-card shadow-sm" data-aos="fade-up">
        <div class="card-body">
            <h4 class="mb-4"><i class="fas fa-plus-circle me-2 text-primary"></i>Nueva Publicación</h4>
            <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <form id="publicacionForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="form_token" value="<?= htmlspecialchars($token) ?>">
                
                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="5" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de Contenido</label>
                        <select name="tipo" class="form-select" required>
                            <option value="anuncio">Anuncio</option>
                            <option value="banner">Banner Principal</option>
                            <option value="convocatoria">Convocatoria</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Enlace Externo (Opcional)</label>
                        <input type="url" name="link_externo" class="form-control" placeholder="https://ejemplo.com">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="imagen" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>Guardar Publicación</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</main>

<?php 
$conn->close();
include('../includes/footer.php'); 
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
  AOS.init();
  const form = document.getElementById('publicacionForm');
  if (form) {
    form.addEventListener('submit', function() {
      const submitButton = form.querySelector('button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
      }
    });
  }
</script>
</body>
</html>
