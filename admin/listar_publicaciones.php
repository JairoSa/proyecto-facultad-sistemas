<?php
// Inicia la sesión y protege la página para que solo los administradores puedan acceder.
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Incluye la conexión a la base de datos.
include('../config/db.php');

// Define el título de la página y carga el header.
$page_title = "Listado de Publicaciones";
include('../includes/header.php');

// Realiza la consulta para obtener todas las publicaciones, ordenadas por la más reciente.
$result = $conn->query("SELECT * FROM publicaciones ORDER BY fecha_creacion DESC");
?>

<div class="container mt-5" data-aos="fade-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Publicaciones del Sitio</h2>
        <a href="gestionar_publicacion.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Crear Nueva</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Fecha de Creación</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['imagen_url'])): ?>
                                            <img src="../assets/img/uploads/<?= htmlspecialchars($row['imagen_url']) ?>" alt="Miniatura" width="100" class="img-thumbnail">
                                        <?php else: ?>
                                            <span class="text-muted">Sin imagen</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                                    <td><span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($row['tipo'])) ?></span></td>
                                    <td><?= date("d/m/Y H:i", strtotime($row['fecha_creacion'])) ?></td>
                                    <td class="text-end">
                                        <a href="gestionar_publicacion.php?editar=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="eliminar_publicacion.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta publicación?')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aún no hay publicaciones creadas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
// Carga el footer y los scripts de JavaScript.
include('../includes/footer.php'); 
$conn->close();
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>AOS.init();</script>
</body>
</html>
