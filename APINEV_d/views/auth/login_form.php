<?php
// views/auth/login_form.php
// Fragmento puro del formulario — usado por AuthController tras un error de login
// Variables disponibles: $error (string)
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