<?php
// views/_partials/header.php — Cabecera reutilizable en todas las páginas
$subtitulo = $subtitulo ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars(APP_NAME) ?> – <?= htmlspecialchars($pageTitle ?? 'APINEV') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet"/>

  <!-- CSS: ruta absoluta desde BASE_URL para que funcione en cualquier subcarpeta -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/styles.css"/>
</head>
<body>

<header class="site-header">
  <div class="logo-box">ITS<br>OEH</div>
  <div class="header-title">
    <h1>Sistema Inteligente para la Educación Superior (<?= APP_NAME ?>)</h1>
    <?php if ($subtitulo): ?>
      <p><?= htmlspecialchars($subtitulo) ?></p>
    <?php endif; ?>
  </div>
  <div class="logo-box">ITS<br>OEH</div>
</header>