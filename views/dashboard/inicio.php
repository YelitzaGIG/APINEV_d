<?php
// views/dashboard/inicio.php — Panel de bienvenida
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';

DashboardController::verificarSesion();
$usuario = DashboardController::usuarioSesion();

$pageTitle = 'Inicio';
$subtitulo  = APP_SUBNAME;
require __DIR__ . '/../_partials/header.php';
?>

<div class="app-body">
  <?php require __DIR__ . '/../_partials/sidebar.php'; ?>

  <section class="content-area">
    <h2>Inicio</h2>

    <p style="color:var(--color-text-muted); margin-bottom:1.2rem;">
      Bienvenido, <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>.
      Has iniciado sesión como <em><?= htmlspecialchars($usuario['rol']) ?></em>
      — <?= htmlspecialchars($usuario['depto']) ?>.
    </p>

    <div class="welcome-grid">

      <div class="info-card">
        <div class="card-icon">
          <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/></svg>
        </div>
        <h3>Diseñar</h3>
        <p>Crea y edita instrumentos de evaluación.</p>
      </div>

      <div class="info-card">
        <div class="card-icon">
          <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
        </div>
        <h3>Evaluar</h3>
        <p>Aplica evaluaciones a tus alumnos.</p>
      </div>

      <div class="info-card">
        <div class="card-icon">
          <svg viewBox="0 0 24 24">
            <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5
                     6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79
                     l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5
                     9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
          </svg>
        </div>
        <h3>Consultar</h3>
        <p>Revisa resultados y reportes.</p>
      </div>

    </div>
  </section>
</div>

<script src="<?= BASE_URL ?>/public/js/dashboard.js"></script>
<?php require __DIR__ . '/../_partials/footer.php'; ?>
