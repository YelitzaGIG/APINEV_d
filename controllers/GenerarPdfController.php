<?php
// =============================================================
// controllers/GenerarPdfController.php
// =============================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/DashboardController.php';

DashboardController::verificarSesion();
$usuario   = DashboardController::usuarioSesion();
$idUsuario = (int) $usuario['id'];

// ── Parámetros GET ────────────────────────────────────────────
$idEstudiante  = (int) ($_GET['idEstudiante']  ?? 0);
$idGrupo       = (int) ($_GET['idGrupo']       ?? 0);
$idInstrumento = (int) ($_GET['idInstrumento'] ?? 0);

if ($idEstudiante <= 0 || $idGrupo <= 0 || $idInstrumento <= 0) {
    http_response_code(400);
    die('Parámetros incompletos.');
}

// ── Consultas a la BD ─────────────────────────────────────────

// Datos del alumno
$stmtAlumno = $pdo->prepare("
    SELECT u.nombres, u.apPaterno, u.apMaterno, i.idInscripcion AS matricula
    FROM usuarios u
    JOIN inscripciones i ON i.idUsuario_Estudiante = u.idUsuario
    WHERE u.idUsuario = :uid
    LIMIT 1
");
$stmtAlumno->execute([':uid' => $idEstudiante]);
$alumno = $stmtAlumno->fetch();

if (!$alumno) { http_response_code(404); die('Alumno no encontrado.'); }

// Datos del instrumento
$stmtInstr = $pdo->prepare("
    SELECT ie.clave, ie.nombre, ie.indicaciones
    FROM instrumentos_evaluacion ie
    WHERE ie.idInstrumentoEvaluacion = :id
    LIMIT 1
");
$stmtInstr->execute([':id' => $idInstrumento]);
$instrumento = $stmtInstr->fetch();

if (!$instrumento) { http_response_code(404); die('Instrumento no encontrado.'); }

// Datos del grupo / materia / docente
$stmtGrupo = $pdo->prepare("
    SELECT g.clave AS grupoNombre,
           m.nombre AS materiaNombre,
           CONCAT(u.nombres,' ',u.apPaterno) AS docenteNombre
    FROM grupos g
    JOIN materias m  ON m.idMateria  = g.idMateria
    JOIN usuarios u  ON u.idUsuario  = g.idUsuario_Docente
    WHERE g.idGrupo = :idG
    LIMIT 1
");
$stmtGrupo->execute([':idG' => $idGrupo]);
$grupo = $stmtGrupo->fetch();

// Reactivos con criterio seleccionado para este alumno
$stmtReact = $pdo->prepare("
    SELECT
        r.idReactivo,
        r.enunciado,
        r.indicador,
        r.puntajeMaximo,
        c.puntajeObtenido,
        c.retroalimentacion
    FROM reactivos r
    JOIN instrumentos_evaluacion_reactivos ier
         ON ier.idReactivo = r.idReactivo
         AND ier.idInstrumentoEvaluacion = :idInstr
    LEFT JOIN (
        SELECT efd.idCriterio, cr.puntajeObtenido, cr.retroalimentacion, cr.idReactivo
        FROM evaluaciones_formativas_detalle efd
        JOIN criterios cr ON cr.idCriterio = efd.idCriterio
        JOIN evaluaciones_formativas ef ON ef.idEvaluacionFormativa = efd.idEvaluacionFormativa
            AND ef.idInstrumentoEvaluacion = :idInstr2
        JOIN evaluaciones_sumativas es ON es.idEvaluacionSumativa = ef.idEvaluacionSumativa
        JOIN historial_estudiantes he  ON he.idHistorialEstudiante = es.idHistorialEstudiante
            AND he.idGrupo = :idGrupo
        JOIN inscripciones i ON i.idInscripcion = he.idInscripcion
            AND i.idUsuario_Estudiante = :idEst
    ) c ON c.idReactivo = r.idReactivo
    ORDER BY r.idReactivo ASC
");
$stmtReact->execute([
    ':idInstr'  => $idInstrumento,
    ':idInstr2' => $idInstrumento,
    ':idGrupo'  => $idGrupo,
    ':idEst'    => $idEstudiante,
]);
$reactivos = $stmtReact->fetchAll();

// Puntaje total
$puntajeMax     = array_sum(array_column($reactivos, 'puntajeMaximo'));
$puntajeObtenido = array_sum(array_column($reactivos, 'puntajeObtenido'));

// ── Cargar TCPDF ──────────────────────────────────────────────
$tcpdfPath = __DIR__ . '/../lib/tcpdf/tcpdf.php';
if (!file_exists($tcpdfPath)) {
    http_response_code(500);
    die('TCPDF no encontrado. Copia la carpeta tcpdf en APINEV_d/lib/tcpdf/');
}
require_once $tcpdfPath;

// ── Crear PDF ─────────────────────────────────────────────────
$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);

$pdf->SetCreator('SINTESU APINEV');
$pdf->SetAuthor($usuario['nombre']);
$pdf->SetTitle('Instrumento de Evaluación');
$pdf->SetSubject('Escala de Valoración');

// Sin header/footer por defecto de TCPDF
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);

$pdf->AddPage();

// ── Paleta y helpers ──────────────────────────────────────────
$azulOscuro = [13,  43,  85];   // --color-primary
$azulMedio  = [26,  68, 128];
$azulClaro  = [184, 204, 228];  // cabecera tabla
$amarillo   = [200, 146,  42];  // --color-accent
$blanco     = [255, 255, 255];
$grisClaro  = [244, 241, 235];
$texto      = [ 26,  26,  46];

// Ancho útil
$w = $pdf->getPageWidth() - 30; // 185.9 mm en LETTER

// ── ENCABEZADO ────────────────────────────────────────────────
// Barra azul superior
$pdf->SetFillColor(...$azulOscuro);
$pdf->Rect(15, 15, $w, 12, 'F');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(...$blanco);
$pdf->SetXY(15, 15);
$pdf->Cell($w, 12, 'Instrumento de Evaluación – Escala de Valoración', 0, 1, 'C', false);

// Línea dorada
$pdf->SetDrawColor(...$amarillo);
$pdf->SetLineWidth(0.8);
$pdf->Line(15, 27, 15 + $w, 27);
$pdf->SetLineWidth(0.2);

// ── TABLA DE DATOS GENERALES ──────────────────────────────────
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(...$texto);

$nombreAlumno = $alumno['apPaterno'] . ' ' . $alumno['apMaterno'] . ' ' . $alumno['nombres'];
$nombreInstr  = $instrumento['clave'] . ' – ' . $instrumento['nombre'];

$filasDatos = [
    ['Asignatura',          $grupo['materiaNombre'] ?? '',   'Grupo',          $grupo['grupoNombre'] ?? ''],
    ['Docente',             $grupo['docenteNombre'] ?? '',   'Fecha',          date('d/m/Y')],
    ['Evidencia',           $nombreInstr,                    'Puntaje Máximo', $puntajeMax],
    ['Alumno(s)',           $alumno['matricula'] . ' – ' . $nombreAlumno, '', ''],
];

$pdf->SetY(30);
$colW  = [$w * 0.17, $w * 0.32, $w * 0.17, $w * 0.34];
$rowH  = 6;

foreach ($filasDatos as $fila) {
    $x = 15;
    foreach ([0,1,2,3] as $i) {
        $esTitulo = ($i % 2 === 0);
        if ($esTitulo) {
            $pdf->SetFillColor(...$azulClaro);
            $pdf->SetFont('helvetica', 'B', 7.5);
        } else {
            $pdf->SetFillColor(...$blanco);
            $pdf->SetFont('helvetica', '', 7.5);
        }
        $pdf->SetXY($x, $pdf->GetY());
        $pdf->Cell($colW[$i], $rowH, $fila[$i], 1, 0, 'L', true);
        $x += $colW[$i];
    }
    $pdf->Ln($rowH);
}

// ── INSTRUCCIONES ─────────────────────────────────────────────
$pdf->SetFont('helvetica', 'I', 7.5);
$pdf->SetTextColor(...$texto);
$pdf->SetFillColor(...$grisClaro);
$pdf->MultiCell($w, 5,
    'Instrucciones: Marque con una ✓ en la columna "Cumple" cuando se muestren las evidencias ' .
    'correspondientes o una ✗ en caso contrario.',
    1, 'L', true);
$pdf->Ln(2);

// ── TABLA DE REACTIVOS ────────────────────────────────────────
// Cabecera
$colsR = [
    'No'                => $w * 0.05,
    'Reactivo o Aspecto a Evaluar' => $w * 0.35,
    'Ind'               => $w * 0.06,
    'Pts'               => $w * 0.06,
    'PO'                => $w * 0.06,
    'Cumple'            => $w * 0.08,
    'Retroalimentación' => $w * 0.34,
];

$pdf->SetFillColor(...$azulClaro);
$pdf->SetFont('helvetica', 'B', 7.5);
$pdf->SetTextColor(...$texto);
$x = 15;
foreach ($colsR as $titulo => $ancho) {
    $pdf->SetXY($x, $pdf->GetY());
    $pdf->Cell($ancho, 6, $titulo, 1, 0, 'C', true);
    $x += $ancho;
}
$pdf->Ln(6);

// Filas
$pdf->SetFont('helvetica', '', 7.5);
$retroTexto = '';          // acumula retroalimentaciones únicas

foreach ($reactivos as $i => $r) {
    $no      = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
    $cumple  = ($r['puntajeObtenido'] !== null) ? '✓' : '';
    $po      = ($r['puntajeObtenido'] !== null) ? $r['puntajeObtenido'] : '';
    $retro   = $r['retroalimentacion'] ?? '';

    // Altura de la fila según el texto del enunciado
    $lineas  = ceil(strlen($r['enunciado']) / 52);
    $rH      = max(6, $lineas * 4.5);

    // Fondo alterno
    if ($i % 2 === 0) {
        $pdf->SetFillColor(244, 248, 253);
    } else {
        $pdf->SetFillColor(...$blanco);
    }

    $yFila = $pdf->GetY();
    $x     = 15;

    $vals = [
        $no,
        $r['enunciado'],
        $r['indicador']     ?? '',
        $r['puntajeMaximo'] ?? '',
        $po,
        $cumple,
        '',   // retroalimentación va aparte en el lateral
    ];

    $keys = array_keys($colsR);
    foreach ($vals as $ci => $val) {
        $ancho = array_values($colsR)[$ci];
        $pdf->SetXY($x, $yFila);
        if ($ci === 1) {
            // Enunciado: multiline
            $pdf->MultiCell($ancho, $rH, $val, 1, 'L', true);
        } else {
            $pdf->Cell($ancho, $rH, $val, 1, 0, 'C', true);
        }
        $x += $ancho;
    }

    // Columna Retroalimentación (última) — usa MultiCell
    $retroCol = array_values($colsR)[6];
    $pdf->SetXY(15 + $w - $retroCol, $yFila);
    $pdf->MultiCell($retroCol, $rH, $retro, 1, 'L', true);

    $pdf->SetY($yFila + $rH);

    if ($retro) {
        $retroTexto .= "• " . $retro . "\n";
    }
}

// ── FILA PUNTAJE TOTAL ────────────────────────────────────────
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetFillColor(...$azulClaro);

$anchoLabel = $w * 0.52;
$anchoMax   = $w * 0.06;
$anchoPO    = $w * 0.06;
$anchoFinal = $w * 0.36;

$yTotal = $pdf->GetY();
$pdf->SetXY(15, $yTotal);
$pdf->Cell($anchoLabel, 6, 'Puntaje Máximo', 1, 0, 'R', true);
$pdf->Cell($anchoMax,   6, $puntajeMax,      1, 0, 'C', true);
$pdf->Cell($anchoPO,    6, $puntajeObtenido, 1, 0, 'C', true);
$pdf->Cell($anchoFinal, 6, 'Puntaje Obtenido', 1, 1, 'C', true);

$pdf->Ln(4);

// ── TABLA INDICADORES DE ALCANCE ─────────────────────────────
$pdf->SetFont('helvetica', 'B', 7.5);
$pdf->SetFillColor(...$azulClaro);

// Contar puntos por indicador
$porIndicador = [];
foreach ($reactivos as $r) {
    $ind = $r['indicador'] ?? '';
    if (!isset($porIndicador[$ind])) $porIndicador[$ind] = ['max' => 0, 'ob' => 0];
    $porIndicador[$ind]['max'] += (int)$r['puntajeMaximo'];
    $porIndicador[$ind]['ob']  += (int)$r['puntajeObtenido'];
}

$indicadores = ['A','B','C','D','E','F'];
$wInd = $w / (count($indicadores) + 3);

$pdf->SetXY(15, $pdf->GetY());
$pdf->Cell($w * 0.35, 5, 'Evidencia de Aprendizaje', 1, 0, 'C', true);
$pdf->Cell($w * 0.05, 5, '%', 1, 0, 'C', true);

foreach ($indicadores as $ind) {
    $pdf->Cell($wInd, 5, $ind, 1, 0, 'C', true);
}
$pdf->Cell($w * 0.25, 5, 'Método de Evaluación', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 7.5);
$pdf->SetFillColor(...$blanco);
$pdf->Cell($w * 0.35, 5, $instrumento['nombre'], 1, 0, 'L', true);
$pdf->Cell($w * 0.05, 5, $puntajeMax, 1, 0, 'C', true);
foreach ($indicadores as $ind) {
    $val = $porIndicador[$ind]['ob'] ?? 0;
    $pdf->Cell($wInd, 5, ($val ?: ''), 1, 0, 'C', true);
}
$pdf->Cell($w * 0.25, 5, 'Escala de Valoración', 1, 1, 'C', true);

$pdf->Ln(5);

// ── FIRMAS ────────────────────────────────────────────────────
$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetFillColor(...$azulClaro);
$wFirma = $w / 3;
$pdf->Cell($wFirma, 6, 'Puntaje Obtenido: ' . $puntajeObtenido, 1, 0, 'C', true);
$pdf->Cell($wFirma, 6, 'Sello del Profesor', 1, 0, 'C', true);
$pdf->Cell($wFirma, 6, 'Firma del Estudiante', 1, 1, 'C', true);
$pdf->SetFillColor(...$blanco);
$pdf->Cell($wFirma, 14, '', 1, 0, 'C', true);
$pdf->Cell($wFirma, 14, '', 1, 0, 'C', true);
$pdf->Cell($wFirma, 14, '', 1, 1, 'C', true);

// ── PIE DE PÁGINA ─────────────────────────────────────────────
$pdf->Ln(4);
$pdf->SetFont('helvetica', 'I', 7);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell($w, 4, $grupo['docenteNombre'] ?? '', 0, 1, 'L');
$pdf->SetFont('helvetica', 'I', 6.5);
$pdf->Cell($w, 4, 'Generado por SINTESU – ' . date('d/m/Y H:i'), 0, 1, 'R');

// ── Generar ───────────────────────────────────────────────────
$filename = 'EV_' . $alumno['matricula'] . '_' . $instrumento['clave'] . '_' . date('Ymd') . '.pdf';
$pdf->Output($filename, 'D');   // 'D' = descarga directa; cambiar a 'I' para abrir en navegador