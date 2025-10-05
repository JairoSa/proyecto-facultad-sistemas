<?php
// Si la sesión no está iniciada, la iniciamos para poder leer las variables.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Facultad de Sistemas - UNDAC' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= isset($es_raiz) ? 'assets/css/style.css' : '../assets/css/style.css' ?>">
    <link rel="stylesheet" href="../assets/lightbox/dist/css/lightbox.min.css">
    <style>
        /* Estilos para la insignia de notificación */
        .notification-badge { 
            position: absolute; top: -5px; right: -5px; padding: 5px; 
            border-radius: 50%; background: red; height: 10px; width: 10px; 
        }
    </style>
</head>
<body>

<header class="bg-primary text-white p-3 shadow-sm d-flex justify-content-between align-items-center sticky-top">
  <div class="d-flex align-items-center">
      <h4 class="mb-0">
        <i class="fas fa-university me-2"></i>
        Facultad de Ingeniería de Sistemas - UNDAC
      </h4>
      
      <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <a href="/admin/dashboard.php" class="btn btn-outline-light btn-sm ms-4">
            <i class="fas fa-home me-1"></i> Panel Principal
        </a>
      <?php endif; ?>
      </div>
  
  <?php if (isset($_SESSION['id'])): ?>
    <div class="d-flex align-items-center">
        <?php
        // Lógica para la campana de notificación del estudiante
        if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'usuario') {
            if (!isset($conn)) {
                // Incluye la BD si no ha sido incluida antes
                include(isset($es_raiz) ? 'config/db.php' : '../config/db.php');
            }
            
            $id_usuario_actual = $_SESSION['id'];
            
            // Contar notificaciones no leídas
            $stmt_count = $conn->prepare("SELECT COUNT(id) AS no_leidas FROM notificaciones WHERE id_usuario = ? AND leido = FALSE");
            $stmt_count->bind_param("i", $id_usuario_actual);
            $stmt_count->execute();
            $total_no_leidas = $stmt_count->get_result()->fetch_assoc()['no_leidas'];
            
            // Obtener las 5 notificaciones más recientes
            $stmt_notif = $conn->prepare(
                "SELECT p.titulo, n.fecha_creacion FROM notificaciones n 
                 JOIN publicaciones p ON n.id_publicacion = p.id
                 WHERE n.id_usuario = ? ORDER BY n.fecha_creacion DESC LIMIT 5");
            $stmt_notif->bind_param("i", $id_usuario_actual);
            $stmt_notif->execute();
            $notificaciones = $stmt_notif->get_result();
        ?>
        <div class="dropdown me-3">
          <a href="#" class="text-white" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-bell fa-lg"></i>
            <?php if ($total_no_leidas > 0): ?>
              <span class="notification-badge"></span>
            <?php endif; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <?php if ($notificaciones->num_rows > 0): ?>
                <?php while($notif = $notificaciones->fetch_assoc()): ?>
                    <li><a class="dropdown-item" href="#">
                        <strong><?= htmlspecialchars($notif['titulo']) ?></strong><br>
                        <small class="text-muted"><?= date("d/m/Y H:i", strtotime($notif['fecha_creacion'])) ?></small>
                    </a></li>
                <?php endwhile; ?>
            <?php else: ?>
                <li><a class="dropdown-item" href="#">No hay notificaciones.</a></li>
            <?php endif; ?>
          </ul>
        </div>
        <?php } ?>
        
        <span class="me-3">Hola, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></span>
        <a href="<?= isset($es_raiz) ? 'logout.php' : '../logout.php' ?>" class="btn btn-light btn-sm">
            <i class="fas fa-sign-out-alt me-1"></i>Cerrar sesión
        </a>
    </div>
  <?php endif; ?>
</header>
