<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}
include('../config/db.php');

$mensaje = '';
// --- Lógica para mostrar mensajes de éxito/error desde la URL ---
if (isset($_GET['exito'])) {
    if ($_GET['exito'] == 'eliminado') {
        $mensaje = "<div class='alert alert-success'>Usuario eliminado correctamente.</div>";
    }
}
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'autoeliminacion') {
        $mensaje = "<div class='alert alert-danger'>No puedes eliminar tu propia cuenta de administrador.</div>";
    }
}

// --- Lógica para crear un nuevo usuario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $rol = $_POST['rol'];

    if (empty($nombre) || empty($correo) || empty($password) || empty($rol)) {
        $mensaje = "<div class='alert alert-danger'>Todos los campos son obligatorios.</div>";
    } else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $correo, $password_hashed, $rol);
        
        if ($stmt->execute()) {
            $mensaje = "<div class='alert alert-success'>Usuario '" . htmlspecialchars($nombre) . "' creado correctamente.</div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>Error: el correo electrónico ya existe.</div>";
        }
        $stmt->close();
    }
}

// Obtener la lista de todos los usuarios para mostrarla en la tabla
$usuarios = $conn->query("SELECT id, nombre, correo, rol FROM usuarios ORDER BY rol, nombre");

$page_title = "Gestionar Usuarios";
include('../includes/header.php');
?>

<main class="container mt-5">
    <h1 class="page-title" data-aos="fade-down">Gestión de Usuarios</h1>
    
    <?= $mensaje ?>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card content-card shadow-sm" data-aos="fade-right">
                <div class="card-body">
                    <h4 class="mb-4"><i class="fas fa-user-plus me-2 text-primary"></i>Crear Nuevo Usuario</h4>
                    <form method="POST" action="gestionar_usuarios.php">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Institucional</label>
                            <input type="email" name="correo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña Provisional</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select name="rol" class="form-select" required>
                                <option value="usuario">Estudiante</option>
                                <option value="docente">Docente</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <button type="submit" name="crear_usuario" class="btn btn-primary w-100">Crear Usuario</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card content-card shadow-sm" data-aos="fade-left">
                <div class="card-body">
                    <h4 class="mb-4"><i class="fas fa-users me-2 text-primary"></i>Usuarios Existentes</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($usuarios && $usuarios->num_rows > 0): ?>
                                    <?php while($usuario = $usuarios->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                            <td><?= htmlspecialchars($usuario['correo']) ?></td>
                                            <td><span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($usuario['rol'])) ?></span></td>
                                            <td class="text-end">
                                                <?php if ($usuario['id'] != $_SESSION['id']): // Condición para no mostrar el botón para el propio admin ?>
                                                    <a href="eliminar_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar a este usuario?')" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No hay usuarios creados todavía.</td>
                                    </tr>
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
