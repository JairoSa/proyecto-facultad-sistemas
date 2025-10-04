<?php
// ... (verificación de sesión de admin) ...
include('../config/db.php');
$page_title = "Listado de Publicaciones";
include('../includes/header.php');

$result = $conn->query("SELECT * FROM publicaciones ORDER BY fecha_creacion DESC");
?>
<div class="container mt-5" data-aos="fade-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Publicaciones</h2>
        <a href="gestionar_publicacion.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Crear Nueva</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Imagen</th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <img src="../assets/img/uploads/<?= htmlspecialchars($row['imagen_url']) ?>" alt="Imagen" width="100" class="img-thumbnail">
                        </td>
                        <td><?= htmlspecialchars($row['titulo']) ?></td>
                        <td><span class="badge bg-secondary"><?= ucfirst($row['tipo']) ?></span></td>
                        <td><?= date("d/m/Y H:i", strtotime($row['fecha_creacion'])) ?></td>
                        <td>
                            <a href="gestionar_publicacion.php?editar=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>
                            <a href="eliminar_publicacion.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta publicación?')" title="Eliminar"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include('../includes/footer.php'); ?>