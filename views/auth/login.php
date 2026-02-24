<?php
// views/auth/login.php
require_once __DIR__ . '/../../config/config.php';

// Si ya hay sesión, redirigir al dashboard
if (!empty($_SESSION['sintesu_auth'])) {
    header('Location: ' . BASE_URL . '/views/dashboard/inicio.php');
    exit;
}

// Solo procesar si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../config/conexion.php';
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $ctrl = new AuthController($pdo);
    $ctrl->login();
    exit;
}

// GET → renderizar vista directamente
$error     = '';
$pageTitle = 'Inicio de Sesión';
$subtitulo = APP_SUBNAME;
require __DIR__ . '/../_partials/header.php';
?>

<main class="form-wrapper">
  <div class="form-card">
    <h2>Inicio de Sesión</h2>

    <form method="POST" action="<?= BASE_URL ?>/views/auth/login.php" novalidate>

      <div class="form-row">
        <label for="idUsuario">Usuario:</label>
        <input id="idUsuario" name="idUsuario" type="number"
               placeholder="Ingresa tu usuario"
               value="<?= htmlspecialchars($_POST['idUsuario'] ?? '') ?>"
               required/>
      </div>

      <div class="form-row">
        <label for="contrasenia">Contraseña:</label>
        <input id="contrasenia" name="contrasenia" type="password"
               placeholder="Coloca tu contraseña" required/>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if (!empty($_GET['timeout'])): ?>
        <div class="alert alert-error">Tu sesión expiró. Vuelve a iniciar sesión.</div>
      <?php endif; ?>

      <div class="btn-row">
        <button type="reset"  class="btn btn-secondary">Borrar</button>
        <button type="submit" class="btn btn-primary">Acceder</button>
      </div>

    </form>

    <p class="register-link">
      ¿No tienes una cuenta?
      <a href="<?= BASE_URL ?>/views/auth/registro.php">Crear cuenta</a>
    </p>
  </div>
</main>

<?php require __DIR__ . '/../_partials/footer.php'; ?>