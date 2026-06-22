<?php
// =============================================================
// controllers/EvaluarController.php — API AJAX módulo Evaluar
// =============================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../models/EvaluarModel.php';

DashboardController::verificarSesion();
$usuario   = DashboardController::usuarioSesion();
$idUsuario = (int) $usuario['id'];

header('Content-Type: application/json; charset=utf-8');

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$model  = new EvaluarModel($pdo);

try {
    switch ($accion) {

        // ── Grupos del docente ─────────────────────────────
        case 'listar_grupos':
            $datos = $model->obtenerGruposDocente($idUsuario);
            echo json_encode(['status' => 'ok', 'datos' => $datos]);
            break;

        // ── Instrumentos del docente ───────────────────────
        case 'listar_instrumentos':
            $datos = $model->obtenerInstrumentosDocente($idUsuario);
            echo json_encode(['status' => 'ok', 'datos' => $datos]);
            break;

        // ── Alumnos del grupo con puntos en el instrumento ─
        case 'listar_alumnos':
            $idGrupo   = (int) ($_GET['idGrupo']      ?? $_POST['idGrupo']      ?? 0);
            $idInstr   = (int) ($_GET['idInstrumento'] ?? $_POST['idInstrumento'] ?? 0);
            if ($idGrupo <= 0 || $idInstr <= 0) {
                echo json_encode(['status' => 'ok', 'datos' => []]);
                break;
            }
            $datos = $model->obtenerAlumnosGrupo($idGrupo, $idInstr);
            echo json_encode(['status' => 'ok', 'datos' => $datos]);
            break;

        // ── Reactivos de un instrumento ────────────────────
        case 'listar_reactivos':
            $idInstr = (int) ($_GET['idInstrumento'] ?? $_POST['idInstrumento'] ?? 0);
            $datos   = $idInstr > 0 ? $model->obtenerReactivosInstrumento($idInstr) : [];
            echo json_encode(['status' => 'ok', 'datos' => $datos]);
            break;

        // ── Criterios de un reactivo ───────────────────────
        case 'listar_criterios':
            $idReact = (int) ($_GET['idReactivo'] ?? $_POST['idReactivo'] ?? 0);
            $datos   = $idReact > 0 ? $model->obtenerCriteriosReactivo($idReact) : [];
            echo json_encode(['status' => 'ok', 'datos' => $datos]);
            break;

        // ── Guardar evaluación de un alumno/reactivo ───────
        case 'guardar_evaluacion':
            $idEstudiante = (int) ($_POST['idEstudiante'] ?? 0);
            $idGrupo      = (int) ($_POST['idGrupo']      ?? 0);
            $idInstr      = (int) ($_POST['idInstrumento'] ?? 0);
            $idReactivo   = (int) ($_POST['idReactivo']   ?? 0);
            $idCriterio   = (int) ($_POST['idCriterio']   ?? 0);
            $puntaje      = (int) ($_POST['puntaje']      ?? 0);

            if ($idEstudiante <= 0 || $idGrupo <= 0 || $idInstr <= 0 || $idReactivo <= 0 || $idCriterio <= 0) {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'mensaje' => 'Datos incompletos.']);
                break;
            }

            $ok = $model->guardarEvaluacion($idEstudiante, $idGrupo, $idInstr, $idReactivo, $idCriterio, $puntaje);
            if (!$ok) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'mensaje' => 'No se pudo guardar la evaluación.']);
                break;
            }

            // Devolver puntos actualizados del alumno
            $puntos = $model->obtenerPuntosAlumno($idEstudiante, $idGrupo, $idInstr);
            echo json_encode(['status' => 'ok', 'puntosObtenidos' => $puntos]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'mensaje' => 'Acción desconocida.']);
            break;
    }

} catch (PDOException $e) {
    error_log("EvaluarController PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error en base de datos.']);
} catch (Throwable $e) {
    error_log("EvaluarController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error interno.']);
}
