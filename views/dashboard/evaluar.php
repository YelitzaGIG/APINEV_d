<?php
// views/dashboard/evaluar.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';

DashboardController::verificarSesion();
$usuario = DashboardController::usuarioSesion();

$pageTitle = 'Evaluar';
$subtitulo  = APP_SUBNAME;
require __DIR__ . '/../_partials/header.php';
?>

<div class="app-body">
  <?php require __DIR__ . '/../_partials/sidebar.php'; ?>

  <section class="content-area">
    <h2>Evaluar</h2>
    <div class="section-placeholder">
      ✅ Módulo de aplicación de evaluaciones.<br>
      <small>Contenido por implementar.</small>
    </div>
  </section>
</div>

<script src="<?= BASE_URL ?>/public/js/dashboard.js"></script>
<?php require __DIR__ . '/../_partials/footer.php'; ?>
