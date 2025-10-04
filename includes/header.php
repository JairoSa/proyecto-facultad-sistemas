<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Facultad de Sistemas - UNDAC' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="bg-primary text-white p-3 shadow-sm d-flex justify-content-between align-items-center sticky-top">
  <h4 class="mb-0">
    <i class="fas fa-university me-2"></i>
    Facultad de Ingeniería de Sistemas - UNDAC
  </h4>
  <?php if (isset($_SESSION['id'])): ?>
    <div>
        <span class="me-3">Hola, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></span>
        <a href="../logout.php" class="btn btn-light btn-sm"><i class="fas fa-sign-out-alt me-1"></i>Cerrar sesión</a>
    </div>
  <?php endif; ?>
</header>