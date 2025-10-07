<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../index.php");
    exit();
}
include('../config/db.php');

$mensaje = '';
if(isset($_GET['exito'])){
    if($_GET['exito'] == 'creada'){
        $mensaje = "<div class='alert alert-success'>Publicación creada correctamente.</div>";
    }
    if($_GET['exito'] == 'eliminada'){
        $mensaje = "<div class='alert alert-info'>Publicación eliminada correctamente.</div>";
    }
}

$publicaciones = $conn->query("SELECT * FROM publicaciones ORDER BY fecha_creacion DESC");

$page_title = "Listado de Publicaciones";
include('../includes/header.php');
?>

<main class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
        <h1 class="page-title">Publicaciones del Sitio</h1>
        <a href="gestionar_publicacion.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Crear Nueva</a>
    </div>

    <?= $mensaje ?>

    <div class="card content-card shadow-sm" data-aos="fade-up">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Fecha de Creación</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($publicaciones && $publicaciones->num_rows > 0): ?>
                            <?php while ($row = $publicaciones->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['imagen_url'])): ?>
                                            <img src="../assets/img/uploads/<?= htmlspecialchars($row['imagen_url']) ?>" alt="Miniatura" width="100" class="img-thumbnail">
                                        <?php else: ?>
                                            <span class="text-muted small">Sin imagen</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                                    <td><span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($row['tipo'])) ?></span></td>
                                    <td><?= date("d/m/Y H:i", strtotime($row['fecha_creacion'])) ?></td>
                                    <td class="text-end">
                                        <a href="eliminar_publicacion.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta publicación?')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Aún no hay publicaciones creadas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
