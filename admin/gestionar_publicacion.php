<?php
session_start();
// ... (verificación de sesión de admin) ...

include('../config/db.php');

$modo_edicion = false;
$publicacion = ['id' => '', 'titulo' => '', 'descripcion' => '', 'tipo' => 'anuncio', 'link_externo' => ''];

if (isset($_GET['editar'])) {
    $modo_edicion = true;
    $id_publicacion = $_GET['editar'];
    $stmt = $conn->prepare("SELECT * FROM publicaciones WHERE id = ?");
    $stmt->bind_param("i", $id_publicacion);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows > 0) {
        $publicacion = $resultado->fetch_assoc();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $tipo = $_POST['tipo'];
    $link_externo = $_POST['link_externo'] ?? '';
    $id_admin = $_SESSION['id'];
    $id_publicacion_post = $_POST['id'] ?? null;

    $imagen_url = $publicacion['imagen_url'] ?? ''; // Mantener la imagen anterior por defecto

    // Lógica para subir imagen
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

    if (!isset($error)) {
        if ($id_publicacion_post) { // Actualizar
            $stmt = $conn->prepare("UPDATE publicaciones SET titulo = ?, descripcion = ?, tipo = ?, link_externo = ?, imagen_url = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $titulo, $descripcion, $tipo, $link_externo, $imagen_url, $id_publicacion_post);
            $mensaje = "Publicación actualizada correctamente.";
        } else { // Insertar
            $stmt = $conn->prepare("INSERT INTO publicaciones (titulo, descripcion, tipo, link_externo, imagen_url, creado_por) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssi", $titulo, $descripcion, $tipo, $link_externo, $imagen_url, $id_admin);
            $mensaje = "Publicación creada correctamente.";
        }
        $stmt->execute();
        header("Location: listar_publicaciones.php");
        exit();
    }
}

$page_title = $modo_edicion ? "Editar Publicación" : "Crear Publicación";
include('../includes/header.php');
?>
<div class="container mt-5" data-aos="fade-up">
    <h2><?= $page_title ?></h2>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($publicacion['id']) ?>">
        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($publicacion['titulo']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="5" required><?= htmlspecialchars($publicacion['descripcion']) ?></textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tipo de Publicación</label>
                <select name="tipo" class="form-select" required>
                    <option value="anuncio" <?= $publicacion['tipo'] == 'anuncio' ? 'selected' : '' ?>>Anuncio</option>
                    <option value="curso" <?= $publicacion['tipo'] == 'curso' ? 'selected' : '' ?>>Curso / Evento</option>
                    <option value="banner" <?= $publicacion['tipo'] == 'banner' ? 'selected' : '' ?>>Banner Principal</option>
                    <option value="convocatoria" <?= $publicacion['tipo'] == 'convocatoria' ? 'selected' : '' ?>>Convocatoria</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Enlace Externo (Opcional)</label>
                <input type="url" name="link_externo" class="form-control" value="<?= htmlspecialchars($publicacion['link_externo']) ?>" placeholder="https://ejemplo.com/registro">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Imagen</label>
            <input type="file" name="imagen" class="form-control" accept="image/*">
            <?php if ($modo_edicion && $publicacion['imagen_url']): ?>
                <small class="form-text text-muted">Imagen actual: <?= htmlspecialchars($publicacion['imagen_url']) ?>. Dejar en blanco para no cambiar.</small>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>Guardar Publicación</button>
        <a href="listar_publicaciones.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<?php include('../includes/footer.php'); ?>