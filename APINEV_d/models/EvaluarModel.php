<?php
// =============================================================
// models/EvaluarModel.php — Módulo Evaluar
// =============================================================

class EvaluarModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ── GRUPOS del docente ────────────────────────────────────
    public function obtenerGruposDocente(int $idUsuario): array
    {
        $sql = "SELECT g.idGrupo,
                       CONCAT(g.clave, ' - ', m.nombre) AS nombre
                FROM grupos g
                JOIN materias m ON m.idMateria = g.idMateria
                WHERE g.idUsuario_Docente = :uid
                ORDER BY g.idGrupo ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $idUsuario]);
        return $stmt->fetchAll();
    }

    // ── INSTRUMENTOS del docente ──────────────────────────────
    public function obtenerInstrumentosDocente(int $idUsuario): array
    {
        $sql = "SELECT ie.idInstrumentoEvaluacion,
                       CONCAT(ie.clave, ' - ', ie.nombre) AS nombre
                FROM instrumentos_evaluacion ie
                JOIN docentes_instrumentosevaluacion die
                     ON die.idInstrumentoEvaluacion = ie.idInstrumentoEvaluacion
                WHERE die.idUsuario_Docente = :uid
                ORDER BY ie.idInstrumentoEvaluacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $idUsuario]);
        return $stmt->fetchAll();
    }

    // ── ALUMNOS de un grupo con puntos obtenidos en instrumento ─
    // Si aún no hay evaluación sumativa para el alumno → NULL (NC)
    public function obtenerAlumnosGrupo(int $idGrupo, int $idInstrumento): array
    {
        $sql = "SELECT
                    u.idUsuario,
                    i.idInscripcion                         AS matricula,
                    CONCAT(u.apPaterno,' ',u.apMaterno,' ',u.nombres) AS nombre,
                    COALESCE(
                        (
                            SELECT SUM(ef.calificacion)
                            FROM evaluaciones_formativas ef
                            JOIN evaluaciones_sumativas es2
                                 ON es2.idEvaluacionSumativa = ef.idEvaluacionSumativa
                            WHERE es2.idHistorialEstudiante = he.idHistorialEstudiante
                              AND ef.idInstrumentoEvaluacion = :idInstr
                        ),
                        -1
                    ) AS puntosObtenidos
                FROM historial_estudiantes he
                JOIN inscripciones i    ON i.idInscripcion = he.idInscripcion
                JOIN usuarios u         ON u.idUsuario     = i.idUsuario_Estudiante
                WHERE he.idGrupo = :idGrupo
                ORDER BY u.apPaterno, u.apMaterno, u.nombres";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idGrupo' => $idGrupo, ':idInstr' => $idInstrumento]);
        $rows = $stmt->fetchAll();
        // -1 = sin evaluación → "NC"
        foreach ($rows as &$r) {
            $r['puntosObtenidos'] = ($r['puntosObtenidos'] == -1) ? 'NC' : (int)$r['puntosObtenidos'];
        }
        return $rows;
    }

    // ── REACTIVOS de un instrumento ───────────────────────────
    public function obtenerReactivosInstrumento(int $idInstrumento): array
    {
        $sql = "SELECT r.idReactivo, r.enunciado, r.indicador, r.puntajeMaximo
                FROM reactivos r
                JOIN instrumentos_evaluacion_reactivos ier
                     ON ier.idReactivo = r.idReactivo
                WHERE ier.idInstrumentoEvaluacion = :idInstr
                ORDER BY r.idReactivo ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idInstr' => $idInstrumento]);
        return $stmt->fetchAll();
    }

    // ── CRITERIOS de un reactivo ──────────────────────────────
    public function obtenerCriteriosReactivo(int $idReactivo): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT idCriterio, puntajeObtenido, retroalimentacion
             FROM criterios
             WHERE idReactivo = :idReact
             ORDER BY puntajeObtenido ASC"
        );
        $stmt->execute([':idReact' => $idReactivo]);
        return $stmt->fetchAll();
    }

    // ── GUARDAR calificación formativa de un alumno ───────────
    // Crea evaluacion_sumativa si no existe, luego upsert en formativas
    public function guardarEvaluacion(
        int $idUsuarioEstudiante,
        int $idGrupo,
        int $idInstrumento,
        int $idReactivo,
        int $idCriterio,
        int $puntaje
    ): bool {
        $this->pdo->beginTransaction();
        try {
            // 1. Obtener historial del estudiante en ese grupo
            $stmt = $this->pdo->prepare(
                "SELECT he.idHistorialEstudiante
                 FROM historial_estudiantes he
                 JOIN inscripciones i ON i.idInscripcion = he.idInscripcion
                 WHERE i.idUsuario_Estudiante = :uid
                   AND he.idGrupo = :idGrupo
                 LIMIT 1"
            );
            $stmt->execute([':uid' => $idUsuarioEstudiante, ':idGrupo' => $idGrupo]);
            $historial = $stmt->fetchColumn();
            if (!$historial) {
                $this->pdo->rollBack();
                return false;
            }

            // 2. Obtener o crear evaluación sumativa para ese historial
            $stmt2 = $this->pdo->prepare(
                "SELECT idEvaluacionSumativa
                 FROM evaluaciones_sumativas
                 WHERE idHistorialEstudiante = :idH
                 LIMIT 1"
            );
            $stmt2->execute([':idH' => $historial]);
            $idEvalSumativa = $stmt2->fetchColumn();

            if (!$idEvalSumativa) {
                $maxId = (int)$this->pdo->query(
                    "SELECT COALESCE(MAX(idEvaluacionSumativa),0) FROM evaluaciones_sumativas"
                )->fetchColumn();
                $idEvalSumativa = $maxId + 1;
                $this->pdo->prepare(
                    "INSERT INTO evaluaciones_sumativas
                         (idEvaluacionSumativa, idTema, calificacion, desempenio, idHistorialEstudiante)
                     VALUES (:id, 0, 0, 'En proceso', :idH)"
                )->execute([':id' => $idEvalSumativa, ':idH' => $historial]);
            }

            // 3. Buscar si ya existe evaluación formativa para este instrumento+sumativa
            $stmt3 = $this->pdo->prepare(
                "SELECT idEvaluacionFormativa
                 FROM evaluaciones_formativas
                 WHERE idEvaluacionSumativa = :idES
                   AND idInstrumentoEvaluacion = :idInstr
                 LIMIT 1"
            );
            $stmt3->execute([':idES' => $idEvalSumativa, ':idInstr' => $idInstrumento]);
            $idEvalFormativa = $stmt3->fetchColumn();

            if (!$idEvalFormativa) {
                $maxF = (int)$this->pdo->query(
                    "SELECT COALESCE(MAX(idEvaluacionFormativa),0) FROM evaluaciones_formativas"
                )->fetchColumn();
                $idEvalFormativa = $maxF + 1;
                $this->pdo->prepare(
                    "INSERT INTO evaluaciones_formativas
                         (idEvaluacionFormativa, fecha, calificacion, idEvaluacionSumativa, idInstrumentoEvaluacion)
                     VALUES (:id, CURDATE(), 0, :idES, :idInstr)"
                )->execute([':id' => $idEvalFormativa, ':idES' => $idEvalSumativa, ':idInstr' => $idInstrumento]);
            }

            // 4. Upsert en detalle: si ya existe ese criterio en esa eval formativa, actualiza; si no, inserta
            // Primero quitamos criterios anteriores del MISMO reactivo en esta eval formativa
            $critReact = $this->pdo->prepare(
                "SELECT idCriterio FROM criterios WHERE idReactivo = :idR"
            );
            $critReact->execute([':idR' => $idReactivo]);
            $criteriosDelReactivo = $critReact->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($criteriosDelReactivo)) {
                $in = implode(',', array_map('intval', $criteriosDelReactivo));
                $this->pdo->prepare(
                    "DELETE FROM evaluaciones_formativas_detalle
                     WHERE idEvaluacionFormativa = :idEF
                       AND idCriterio IN ($in)"
                )->execute([':idEF' => $idEvalFormativa]);
            }

            // Insertar el criterio elegido
            $this->pdo->prepare(
                "INSERT INTO evaluaciones_formativas_detalle
                     (idEvaluacionFormativa, idCriterio)
                 VALUES (:idEF, :idC)"
            )->execute([':idEF' => $idEvalFormativa, ':idC' => $idCriterio]);

            // 5. Recalcular calificación total de la evaluación formativa
            $this->pdo->prepare(
                "UPDATE evaluaciones_formativas ef
                 SET ef.calificacion = (
                     SELECT COALESCE(SUM(c.puntajeObtenido), 0)
                     FROM evaluaciones_formativas_detalle efd
                     JOIN criterios c ON c.idCriterio = efd.idCriterio
                     WHERE efd.idEvaluacionFormativa = :idEF
                 )
                 WHERE ef.idEvaluacionFormativa = :idEF2"
            )->execute([':idEF' => $idEvalFormativa, ':idEF2' => $idEvalFormativa]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("guardarEvaluacion: " . $e->getMessage());
            return false;
        }
    }

    // ── PUNTOS totales de alumno en un instrumento ────────────
    public function obtenerPuntosAlumno(int $idUsuarioEstudiante, int $idGrupo, int $idInstrumento): int|string
    {
        $sql = "SELECT COALESCE(SUM(ef.calificacion), -1) AS total
                FROM evaluaciones_formativas ef
                JOIN evaluaciones_sumativas es ON es.idEvaluacionSumativa = ef.idEvaluacionSumativa
                JOIN historial_estudiantes he  ON he.idHistorialEstudiante = es.idHistorialEstudiante
                JOIN inscripciones i           ON i.idInscripcion = he.idInscripcion
                WHERE i.idUsuario_Estudiante = :uid
                  AND he.idGrupo = :idGrupo
                  AND ef.idInstrumentoEvaluacion = :idInstr";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $idUsuarioEstudiante, ':idGrupo' => $idGrupo, ':idInstr' => $idInstrumento]);
        $val = $stmt->fetchColumn();
        return ($val == -1) ? 'NC' : (int)$val;
    }
}