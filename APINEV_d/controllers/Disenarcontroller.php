<?php
// =============================================================
// controllers/DisenarController.php — API AJAX módulo Diseñar
// =============================================================
 
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../models/DisenarModel.php';
 
DashboardController::verificarSesion();
$usuario   = DashboardController::usuarioSesion();
$idUsuario = (int) $usuario['id'];
 
header('Content-Type: application/json; charset=utf-8');
 
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$model  = new DisenarModel($pdo);
 
try {
    switch ($accion) {
 
        // ── RETÍCULAS DEL DOCENTE ──────────────────────────────
        case 'listar_reticulas':
            $datos = $model->obtenerReticulasDocente($idUsuario);
            echo json_encode(['status' => 'ok', 'datos' => $datos]);
            break;
 
        // ── INSTRUMENTOS ───────────────────────────────────────
        case 'listar_instrumentos':
            $datos = $model->obtenerInstrumentos($idUsuario);
            echo json_encode(['status' => 'ok', 'datos' => $datos]);
            break;
 
        case 'crear_instrumento':
            $clave        = trim($_POST['clave']        ?? '');
            $nombre       = trim($_POST['nombre']       ?? '');
            $indicaciones = trim($_POST['indicaciones'] ?? '');
            $reticula     = trim($_POST['reticula']     ?? '');
 
            if ($clave === '' || $nombre === '') {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'mensaje' => 'Clave y nombre son requeridos.']);
                break;
            }
 
            $id = $model->insertarInstrumento($clave, $nombre, $indicaciones, $reticula, $idUsuario);
            if ($id === false) {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'mensaje' => 'No se pudo guardar el instrumento.']);
                break;
            }
            echo json_encode(['status' => 'ok', 'id' => $id]);
            break;
 
        case 'actualizar_instrumento':
            $id           = (int) ($_POST['id']           ?? 0);
            $clave        = trim($_POST['clave']           ?? '');
            $nombre       = trim($_POST['nombre']          ?? '');
            $indicaciones = trim($_POST['indicaciones']    ?? '');
            $reticula     = trim($_POST['reticula']        ?? '');
 
            if ($id <= 0 || $clave === '' || $nombre === '') {
                http_response_code(422);
                echo json_encode(['status' => 'error', 'mensaje' => 'Datos incompletos.']);
                break;
            }
 
            $ok = $model->actualizarInstrumento($id, $clave, $nombre, $indicaciones, $reticula, $idUsuario);
            echo json_encode(['status' => $ok ? 'ok' : 'error', 'mensaje' => $ok ? '' : 'No se pudo actualizar.']);
            break;
 
        case 'eliminar_instrumento':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) { http_response_code(422); echo json_encode(['status'=>'error','mensaje'=>'ID inválido.']); break; }
            $ok = $model->eliminarInstrumento($id, $idUsuario);
            echo json_encode(['status' => $ok ? 'ok' : 'error', 'mensaje' => $ok ? '' : 'No se pudo eliminar.']);
            break;
 
        // ── REACTIVOS ──────────────────────────────────────────
        case 'listar_reactivos':
            $idInstr = (int) ($_GET['idInstrumento'] ?? $_POST['idInstrumento'] ?? 0);
            $datos   = $idInstr > 0 ? $model->obtenerReactivos($idInstr) : [];
            echo json_encode(['status' => 'ok', 'datos' => $datos]);
            break;
 
        case 'crear_reactivo':
            $idInstr  = (int)  ($_POST['idInstrumento'] ?? 0);
            $enunc    = trim(  $_POST['enunciado']      ?? '');
            $indicad  = trim(  $_POST['indicador']      ?? '');
            $puntaje  = (int)  ($_POST['puntajeMaximo'] ?? 0);
 
            if ($idInstr <= 0 || $enunc === '') {
                http_response_code(422); echo json_encode(['status'=>'error','mensaje'=>'Datos incompletos.']); break;
            }
            $id = $model->insertarReactivo($idInstr, $enunc, $indicad, $puntaje);
            if ($id === false) { http_response_code(500); echo json_encode(['status'=>'error','mensaje'=>'No se pudo guardar.']); break; }
            echo json_encode(['status' => 'ok', 'id' => $id]);
            break;
 
        case 'actualizar_reactivo':
            $id      = (int)  ($_POST['id']            ?? 0);
            $enunc   = trim(  $_POST['enunciado']      ?? '');
            $indicad = trim(  $_POST['indicador']      ?? '');
            $puntaje = (int)  ($_POST['puntajeMaximo'] ?? 0);
 
            if ($id <= 0 || $enunc === '') { http_response_code(422); echo json_encode(['status'=>'error','mensaje'=>'Datos incompletos.']); break; }
            $ok = $model->actualizarReactivo($id, $enunc, $indicad, $puntaje);
            echo json_encode(['status' => $ok ? 'ok' : 'error', 'mensaje' => $ok ? '' : 'No se pudo actualizar.']);
            break;
 
        case 'eliminar_reactivo':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) { http_response_code(422); echo json_encode(['status'=>'error','mensaje'=>'ID inválido.']); break; }
            $ok = $model->eliminarReactivo($id);
            echo json_encode(['status' => $ok ? 'ok' : 'error']);
            break;
 
        // ── CRITERIOS ──────────────────────────────────────────
        case 'listar_criterios':
            $idReact = (int) ($_GET['idReactivo'] ?? $_POST['idReactivo'] ?? 0);
            $datos   = $idReact > 0 ? $model->obtenerCriterios($idReact) : [];
            echo json_encode(['status' => 'ok', 'datos' => $datos]);
            break;
 
        case 'crear_criterio':
            $idReact = (int)  ($_POST['idReactivo']       ?? 0);
            $puntaje = (int)  ($_POST['puntajeObtenido']  ?? 0);
            $retro   = trim(  $_POST['retroalimentacion'] ?? '');
 
            if ($idReact <= 0 || $retro === '') { http_response_code(422); echo json_encode(['status'=>'error','mensaje'=>'Datos incompletos.']); break; }
            $id = $model->insertarCriterio($idReact, $puntaje, $retro);
            if ($id === false) { http_response_code(500); echo json_encode(['status'=>'error','mensaje'=>'No se pudo guardar.']); break; }
            echo json_encode(['status' => 'ok', 'id' => $id]);
            break;
 
        case 'actualizar_criterio':
            $id      = (int)  ($_POST['id']               ?? 0);
            $puntaje = (int)  ($_POST['puntajeObtenido']  ?? 0);
            $retro   = trim(  $_POST['retroalimentacion'] ?? '');
 
            if ($id <= 0 || $retro === '') { http_response_code(422); echo json_encode(['status'=>'error','mensaje'=>'Datos incompletos.']); break; }
            $ok = $model->actualizarCriterio($id, $puntaje, $retro);
            echo json_encode(['status' => $ok ? 'ok' : 'error', 'mensaje' => $ok ? '' : 'No se pudo actualizar.']);
            break;
 
        case 'eliminar_criterio':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) { http_response_code(422); echo json_encode(['status'=>'error','mensaje'=>'ID inválido.']); break; }
            $ok = $model->eliminarCriterio($id);
            echo json_encode(['status' => $ok ? 'ok' : 'error']);
            break;
 
        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'mensaje' => 'Acción desconocida.']);
            break;
    }
 
} catch (PDOException $e) {
    error_log("DisenarController PDO: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error en base de datos.']);
} catch (Throwable $e) {
    error_log("DisenarController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'mensaje' => 'Error interno.']);
}
 