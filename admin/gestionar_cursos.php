<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}
include('../config/db.php');

$mensaje = '';
$modo_edicion = false;
$curso_a_editar = ['id' => '', 'nombre' => '', 'descripcion' => '', 'id_docente_asignado' => '', 'limite_estudiantes' => 300, 'imagen_url' => ''];

// --- Lógica para mostrar mensajes de éxito/error desde la URL ---
if (isset($_GET['exito'])) {
    if ($_GET['exito'] == 'guardado') {
        $mensaje = "<div class='alert alert-success'>Curso guardado correctamente.</div>";
    } elseif ($_GET['exito'] == 'eliminado') {
        $mensaje = "<div class='alert alert-info'>Curso eliminado correctamente.</div>";
    }
}

// --- LÓGICA PARA CARGAR DATOS SI ESTAMOS EDITANDO ---
if (isset($_GET['editar'])) {
    $modo_edicion = true;
    $id_curso = $_GET['editar'];
    $stmt = $conn->prepare("SELECT * FROM cursos WHERE id = ?");
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows > 0) {
        $curso_a_editar = $resultado->fetch_assoc();
    }
}

// --- LÓGICA PARA GUARDAR (CREAR O ACTUALIZAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $id_docente = $_POST['id_docente'] ?: null;
    $limite = $_POST['limite'];
    $id_curso_post = $_POST['id_curso'] ?? null;

    if (empty($nombre) || empty($descripcion)) {
        $mensaje = "<div class='alert alert-danger'>El nombre y la descripción son obligatorios.</div>";
    } else {
        $imagen_url = $curso_a_editar['imagen_url'] ?? '';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $directorio_subida = '../assets/img/uploads/';
            $nombre_archivo = time() . '_' . basename($_FILES['imagen']['name']);
            $ruta_archivo = $directorio_subida . $nombre_archivo;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_archivo)) {
                $imagen_url = $nombre_archivo;
            } else {
                $mensaje = "<div class='alert alert-danger'>Hubo un error al subir la imagen.</div>";
            }
        }

        if (empty($mensaje)) {
            if ($id_curso_post) {
                // Lógica de ACTUALIZACIÓN
                $stmt = $conn->prepare("UPDATE cursos SET nombre = ?, descripcion = ?, id_docente_asignado = ?, limite_estudiantes = ?, imagen_url = ? WHERE id = ?");
                $stmt->bind_param("ssiisi", $nombre, $descripcion, $id_docente, $limite, $imagen_url, $id_curso_post);
            } else {
                // Lógica de CREACIÓN
                $codigo_acceso = strtoupper(bin2hex(random_bytes(4)));
                $stmt = $conn->prepare("INSERT INTO cursos (nombre, descripcion, id_docente_asignado, limite_estudiantes, codigo_acceso, imagen_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiiss", $nombre, $descripcion, $id_docente, $limite, $codigo_acceso, $imagen_url);
            }
            
            if ($stmt->execute()) {
                if (!$id_curso_post) { // Si es una creación, generar notificaciones
                    $id_nuevo_curso = $conn->insert_id;
                    $result_usuarios = $conn->query("SELECT id FROM usuarios WHERE rol = 'usuario'");
                    if ($result_usuarios->num_rows > 0) {
                        $stmt_notificacion = $conn->prepare("INSERT INTO notificaciones (id_usuario, tipo_contenido, id_contenido) VALUES (?, 'curso', ?)");
                        while ($usuario = $result_usuarios->fetch_assoc()) {
                            $stmt_notificacion->bind_param("ii", $usuario['id'], $id_nuevo_curso);
                            $stmt_notificacion->execute();
                        }
                        $stmt_notificacion->close();
                    }
                }
                header("Location: gestionar_cursos.php?exito=guardado");
                exit();
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al guardar el curso.</div>";
            }
        }
    }
}

// Obtener listas para los formularios y la tabla
$docentes = $conn->query("SELECT id, nombre FROM usuarios WHERE rol = 'docente' ORDER BY nombre");
$cursos = $conn->query("SELECT c.id, c.nombre, c.codigo_acceso, c.limite_estudiantes, c.imagen_url, u.nombre AS nombre_docente FROM cursos c LEFT JOIN usuarios u ON c.id_docente_asignado = u.id ORDER BY c.fecha_creacion DESC");

$page_title = "Gestionar Cursos";
include('../includes/header.php');
?>

<main class="container mt-5">
    <h1 class="page-title" data-aos="fade-down">Gestión de Cursos</h1>
    <?= $mensaje ?>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card content-card shadow-sm" data-aos="fade-right">
                <div class="card-body">
                    <h4 class="mb-4"><i class="fas fa-edit me-2 text-primary"></i><?= $modo_edicion ? "Editar Curso" : "Crear Nuevo Curso" ?></h4>
                    <form method="POST" action="gestionar_cursos.php<?= $modo_edicion ? '?editar='.$curso_a_editar['id'] : '' ?>" enctype="multipart/form-data">
                        <?php if ($modo_edicion): ?>
                            <input type="hidden" name="id_curso" value="<?= $curso_a_editar['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Nombre del Curso</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($curso_a_editar['nombre']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="4" required><?= htmlspecialchars($curso_a_editar['descripcion']) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Imagen del Curso</label>
                            <input type="file" name="imagen" class="form-control" accept="image/*">
                            <?php if ($modo_edicion && !empty($curso_a_editar['imagen_url'])): ?>
                                <small class="form-text text-muted">Imagen actual: <?= htmlspecialchars($curso_a_editar['imagen_url']) ?>.</small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Asignar Docente</label>
                            <select name="id_docente" class="form-select">
                                <option value="">(Sin asignar)</option>
                                <?php if($docentes && $docentes->num_rows > 0) mysqli_data_seek($docentes, 0); ?>
                                <?php while($docente = $docentes->fetch_assoc()): ?>
                                    <option value="<?= $docente['id'] ?>" <?= ($modo_edicion && $curso_a_editar['id_docente_asignado'] == $docente['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($docente['nombre']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Límite de Estudiantes</label>
                            <input type="number" name="limite" class="form-control" value="<?= htmlspecialchars($curso_a_editar['limite_estudiantes']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><?= $modo_edicion ? "Actualizar Curso" : "Crear Curso" ?></button>
                        <?php if ($modo_edicion): ?>
                            <a href="gestionar_cursos.php" class="btn btn-secondary w-100 mt-2">Cancelar Edición</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card content-card shadow-sm" data-aos="fade-left">
                <div class="card-body">
                    <h4 class="mb-4"><i class="fas fa-list-ul me-2 text-primary"></i>Cursos Existentes</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Imagen</th>
                                    <th>Curso</th>
                                    <th>Docente</th>
                                    <th>Límite</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($cursos && $cursos->num_rows > 0): ?>
                                    <?php while($curso = $cursos->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <img src="../assets/img/uploads/<?= htmlspecialchars($curso['imagen_url'] ?: 'default.png') ?>" alt="Miniatura" width="80" class="img-thumbnail">
                                            </td>
                                            <td><?= htmlspecialchars($curso['nombre']) ?></td>
                                            <td><?= htmlspecialchars($curso['nombre_docente'] ?? '<em>Sin asignar</em>') ?></td>
                                            <td><?= htmlspecialchars($curso['limite_estudiantes']) ?></td>
                                            <td class="text-end">
                                                <a href="gestionar_cursos.php?editar=<?= $curso['id'] ?>" class="btn btn-warning btn-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="eliminar_curso.php?id=<?= $curso['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro? Se borrarán inscripciones y materiales.')" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">No hay cursos creados todavía.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
