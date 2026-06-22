<?php
// views/_partials/sidebar.php
// Variable esperada: $usuario (array con datos de sesión)
?>
<aside class="sidebar">
  <nav>

    <a href="<?= BASE_URL ?>/views/dashboard/inicio.php">
      <svg class="sidebar-icon" viewBox="0 0 24 24">
        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
      </svg>
      Inicio
    </a>

    <a href="<?= BASE_URL ?>/views/dashboard/disenar.php">
      <svg class="sidebar-icon" viewBox="0 0 24 24">
        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm21.41-14.66
                 a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41
                 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
      </svg>
      Diseñar
    </a>

    <a href="<?= BASE_URL ?>/views/dashboard/evaluar.php">
      <svg class="sidebar-icon" viewBox="0 0 24 24">
        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
      </svg>
      Evaluar
    </a>

    <a href="<?= BASE_URL ?>/views/dashboard/consultar.php">
      <svg class="sidebar-icon" viewBox="0 0 24 24">
        <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5
                 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79
                 l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5
                 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
      </svg>
      Consultar
    </a>

  </nav>

  <div class="sidebar-bottom">
    <a href="<?= BASE_URL ?>/controllers/logout.php">
      <svg class="sidebar-icon" viewBox="0 0 24 24">
        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17
                 l5-5-5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2
                 2h8v-2H4V5z"/>
      </svg>
      Salir
    </a>
  </div>
</aside>
