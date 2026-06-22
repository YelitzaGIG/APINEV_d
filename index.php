<?php
// index.php — Página de inicio (raíz del proyecto)
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Inicio';
$subtitulo  = '';
require __DIR__ . '/views/_partials/header.php';
?>

<main class="index-main">
  <h2 class="section-title">Aplicaciones</h2>
  <div class="apps-grid">

    <!-- Nuevo Usuario → registro -->
    <a href="<?= BASE_URL ?>/views/auth/registro.php" class="app-card">
      <div class="app-icon">
        <svg viewBox="0 0 24 24">
          <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4
                   1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9
                   4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>
      </div>
      <span>Nuevo Usuario</span>
    </a>

    <!-- APINEV → login -->
    <a href="<?= BASE_URL ?>/views/auth/login.php" class="app-card">
      <div class="app-icon">
        <svg viewBox="0 0 24 24">
          <path d="M3 18h13v-2H3v2zm0-5h10v-2H3v2zm0-7v2h13V6H3zm18
                   9.59L17.42 12 21 8.41 19.59 7l-5 5 5 5L21 15.59z"/>
        </svg>
      </div>
      <span>APINEV</span>
    </a>

  </div>
</main>

<?php require __DIR__ . '/views/_partials/footer.php'; ?>
