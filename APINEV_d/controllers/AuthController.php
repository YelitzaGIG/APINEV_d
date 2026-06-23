<?php
// =============================================================
// controllers/AuthController.php — Controlador de Autenticación
// Gestiona: login, logout, registro
// =============================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/AccesoModel.php';

class AuthController
{
    private UsuarioModel $usuarioModel;
    private AccesoModel  $accesoModel;

    public function __construct(PDO $pdo)
    {
        $this->usuarioModel = new UsuarioModel($pdo);
        $this->accesoModel  = new AccesoModel($pdo);
    }

    // ----------------------------------------------------------
    // POST → procesa credenciales y redirige o regresa con error
    // ----------------------------------------------------------
    public function login(): void
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario   = (int)trim($_POST['idUsuario']   ?? '');
            $contrasenia = trim($_POST['contrasenia'] ?? '');

            if (!$idUsuario || !$contrasenia) {
                $error = 'Por favor ingresa tu matrícula y contraseña.';
            } else {
                $usuario = $this->usuarioModel->buscarPorId($idUsuario);

                if ($usuario && password_verify($contrasenia, $usuario['contrasenia'])) {
                    // Credenciales correctas → iniciar sesión
                    $idAcceso = $this->accesoModel->registrarEntrada($idUsuario);

                    $_SESSION['sintesu_auth']      = true;
                    $_SESSION['sintesu_idUsuario'] = $usuario['idUsuario'];
                    $_SESSION['sintesu_nombre']    = $usuario['nombres'] . ' ' . $usuario['apPaterno'];
                    $_SESSION['sintesu_rol']       = $usuario['rol'];
                    $_SESSION['sintesu_nivelRol']  = $usuario['nivelRol'];
                    $_SESSION['sintesu_depto']     = $usuario['departamento'];
                    $_SESSION['sintesu_idAcceso']  = $idAcceso;
                    $_SESSION['sintesu_inicio']    = time();

                    header('Location: ' . BASE_URL . '/views/dashboard/inicio.php');
                    exit;
                } else {
                    // Registrar fallo si el usuario existe
                    if ($usuario) {
                        $this->accesoModel->registrarFallo($idUsuario);
                    }
                    $error = 'Matrícula o contraseña incorrectos.';
                }
            }
        }

        // Si hay error en POST → renderizar vista con el mensaje
        $pageTitle = 'Inicio de Sesión';
        $subtitulo = APP_SUBNAME;
        require __DIR__ . '/../views/_partials/header.php';
        require __DIR__ . '/../views/auth/login_form.php';
        require __DIR__ . '/../views/_partials/footer.php';
    }

    // ----------------------------------------------------------
    // Cierra la sesión del usuario
    // ----------------------------------------------------------
    public function logout(): void
    {
        if (!empty($_SESSION['sintesu_idAcceso'])) {
            $this->accesoModel->registrarSalida((int)$_SESSION['sintesu_idAcceso']);
        }

        session_unset();
        session_destroy();

        header('Location: ' . BASE_URL . '/views/auth/login.php');
        exit;
    }

    // ----------------------------------------------------------
    // POST → crea el usuario en la BD
    // ----------------------------------------------------------
    public function registro(): void
    {
        $error   = '';
        $success = '';
        $datos   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recoger y sanear datos del formulario
            $datos = [
                'idUsuario'      => (int)trim($_POST['idUsuario']      ?? ''),
                'contrasenia'    => trim($_POST['contrasenia']         ?? ''),
                'confirmar'      => trim($_POST['confirmar']           ?? ''),
                'nombres'        => htmlspecialchars(trim($_POST['nombres']    ?? '')),
                'apPaterno'      => htmlspecialchars(trim($_POST['apPaterno']  ?? '')),
                'apMaterno'      => htmlspecialchars(trim($_POST['apMaterno']  ?? '')),
                'telefono'       => trim($_POST['telefono']            ?? ''),
                'correo'         => strtolower(trim($_POST['correo']   ?? '')),
                'idDepartamento' => (int)trim($_POST['idDepartamento'] ?? ''),
            ];

            // Validaciones
            if (!$datos['idUsuario'])
                $error = 'La matrícula es requerida.';
            elseif ($this->usuarioModel->existeId($datos['idUsuario']))
                $error = 'Esa matrícula ya está registrada.';
            elseif (strlen($datos['contrasenia']) < 4)
                $error = 'La contraseña debe tener al menos 4 caracteres.';
            elseif ($datos['contrasenia'] !== $datos['confirmar'])
                $error = 'Las contraseñas no coinciden.';
            elseif (!$datos['nombres'] || !$datos['apPaterno'])
                $error = 'Nombre y apellido paterno son requeridos.';
            elseif (!preg_match('/^\d{7,15}$/', $datos['telefono']))
                $error = 'Teléfono inválido (solo dígitos, 7–15 caracteres).';
            elseif (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL))
                $error = 'Correo electrónico inválido.';
            elseif (!str_ends_with($datos['correo'], '@itsoeh.edu.mx'))
                $error = 'El correo debe ser @itsoeh.edu.mx.';
            elseif ($this->usuarioModel->existeCorreo($datos['correo']))
                $error = 'Ese correo ya está registrado.';
            elseif (!$datos['idDepartamento'])
                $error = 'Selecciona un departamento.';

            if (!$error) {
                $resultado = $this->usuarioModel->crear($datos);
                if ($resultado !== false) {
                    $success = 'Cuenta creada correctamente. <a href="' . BASE_URL . '/views/auth/login.php">Inicia sesión</a>';
                    $datos   = [];
                } else {
                    $error = 'Error al crear la cuenta. Intenta de nuevo.';
                }
            }
        }

        require __DIR__ . '/../views/auth/registro.php';
    }
}