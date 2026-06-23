<?php
// views/auth/registro.php
// Variables disponibles: $error, $success, $datos

require_once __DIR__ . '/../../config/config.php';

// Ejecutar controlador en POST o primera carga
if ($_SERVER['REQUEST_METHOD'] === 'POST' || !isset($error)) {
    require_once __DIR__ . '/../../config/conexion.php';
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $ctrl = new AuthController($pdo);
    $ctrl->registro();
    exit;
}

// ── Renderizado ─────────────────────────────────────────────
$pageTitle = 'Registro de cuenta';
$subtitulo = 'Crear una nueva cuenta para ' . APP_NAME;
require __DIR__ . '/../_partials/header.php';

// Valores previos (para repintar si hay error)
$v = $datos ?? [];
$val = fn(string $k) => htmlspecialchars($v[$k] ?? '');
?>

<main class="form-wrapper top">
  <div class="form-card">
    <h2>Registro de nueva cuenta</h2>

    <form method="POST" action="<?= BASE_URL ?>/views/auth/registro.php" novalidate>

      <div class="form-group">
        <label for="idUsuario">Matrícula (ID de usuario):</label>
        <input id="idUsuario" name="idUsuario" type="number"
               placeholder="Número de matrícula" value="<?= $val('idUsuario') ?>" required/>
      </div>

      <div class="form-group">
        <label for="contrasenia">Contraseña:</label>
        <input id="contrasenia" name="contrasenia" type="password"
               placeholder="Mínimo 4 caracteres" required/>
      </div>

      <div class="form-group">
        <label for="confirmar">Confirmar Contraseña:</label>
        <input id="confirmar" name="confirmar" type="password"
               placeholder="Repite tu contraseña" required/>
      </div>

      <div class="form-group">
        <label for="nombres">Nombre(s):</label>
        <input id="nombres" name="nombres" type="text"
               placeholder="coloca tu nombre" value="<?= $val('nombres') ?>" required/>
      </div>

      <div class="form-group">
        <label for="apPaterno">Apellido Paterno:</label>
        <input id="apPaterno" name="apPaterno" type="text"
               placeholder="coloca tu apellido paterno" value="<?= $val('apPaterno') ?>" required/>
      </div>

      <div class="form-group">
        <label for="apMaterno">Apellido Materno:</label>
        <input id="apMaterno" name="apMaterno" type="text"
               placeholder="coloca tu apellido materno" value="<?= $val('apMaterno') ?>"/>
      </div>

      <div class="form-group">
        <label for="telefono">Teléfono:</label>
        <input id="telefono" name="telefono" type="tel"
               placeholder="Solo dígitos (7–15)" value="<?= $val('telefono') ?>"/>
      </div>

      <div class="form-group">
        <label for="correo">Correo Electrónico:</label>
        <input id="correo" name="correo" type="email"
               placeholder="matricula@itsoeh.edu.mx" value="<?= $val('correo') ?>" required/>
      </div>

      <div class="form-group">
        <label for="idDepartamento">Departamento:</label>
        <select id="idDepartamento" name="idDepartamento" required>
          <option value="">— Selecciona —</option>
          <?php
          // Cargar departamentos desde la BD
          try {
              require_once __DIR__ . '/../../config/conexion.php';
              $deptos = $pdo->query("SELECT idDepartamento, nombre FROM Departamentos ORDER BY nombre")->fetchAll();
              foreach ($deptos as $d):
                  $sel = ($v['idDepartamento'] ?? '') == $d['idDepartamento'] ? 'selected' : '';
          ?>
            <option value="<?= $d['idDepartamento'] ?>" <?= $sel ?>>
              <?= htmlspecialchars($d['nombre']) ?>
            </option>
          <?php endforeach;
          } catch (Exception $e) {
              echo '<option value="">Error al cargar departamentos</option>';
          }
          ?>
        </select>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php endif; ?>

      <div class="btn-row">
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Registrar</button>
      </div>

    </form>
  </div>
</main>

<?php require __DIR__ . '/../_partials/footer.php'; ?>
