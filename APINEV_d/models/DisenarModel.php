<?php
// =============================================================
// models/DisenarModel.php — Módulo Diseñar
// Usa la BD sintesu tal como está (sin modificar ninguna tabla)
// =============================================================
 
class DisenarModel
{
    private PDO $pdo;
 
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
 
    // ──────────────────────────────────────────────────────────
    // RETÍCULAS disponibles para el docente
    // Ruta: grupos → reticulas_materias → reticulas
    // ──────────────────────────────────────────────────────────
 
    /**
     * Retículas distintas que tiene asignadas el docente en sus grupos.
     */
    public function obtenerReticulasDocente(int $idUsuario): array
    {
        $sql = "SELECT DISTINCT r.idReticula, r.nombre
                FROM grupos g
                JOIN reticulas_materias rm ON rm.idMateria  = g.idMateria
                JOIN reticulas r           ON r.idReticula  = rm.idReticula
                WHERE g.idUsuario_Docente = :idUsuario
                ORDER BY r.idReticula ASC";
 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idUsuario' => $idUsuario]);
        return $stmt->fetchAll();
    }
 
    // ──────────────────────────────────────────────────────────
    // INSTRUMENTOS DE EVALUACIÓN
    // La retícula se guarda como prefijo en indicaciones:
    //   formato: [RETICULA:ISIC-2010-224]texto indicaciones...
    // ──────────────────────────────────────────────────────────
 
    /**
     * Extraer retícula guardada en indicaciones.
     */
    private function extraerReticula(string $indicaciones): array
    {
        if (preg_match('/^\[RETICULA:([^\]]+)\](.*)$/s', $indicaciones, $m)) {
            return ['reticula' => trim($m[1]), 'indicaciones' => trim($m[2])];
        }
        return ['reticula' => '', 'indicaciones' => $indicaciones];
    }
 
    /**
     * Empaquetar retícula + indicaciones en el campo indicaciones.
     */
    private function empacarIndicaciones(string $reticula, string $indicaciones): string
    {
        if ($reticula !== '') {
            return '[RETICULA:' . $reticula . ']' . $indicaciones;
        }
        return $indicaciones;
    }
 
    /**
     * Listar instrumentos del docente con retícula y conteo de reactivos.
     */
    public function obtenerInstrumentos(int $idUsuario): array
    {
        $sql = "SELECT
                    ie.idInstrumentoEvaluacion,
                    ie.clave,
                    ie.nombre,
                    ie.indicaciones,
                    COALESCE(
                        GROUP_CONCAT(
                            DISTINCT u.nombres
                            ORDER BY u.nombres
                            SEPARATOR '-'
                        ), ''
                    ) AS docentes,
                    COUNT(DISTINCT ier.idReactivo) AS totalReactivos
                FROM instrumentos_evaluacion ie
                JOIN docentes_instrumentosevaluacion die
                     ON die.idInstrumentoEvaluacion = ie.idInstrumentoEvaluacion
                LEFT JOIN docentes_instrumentosevaluacion die2
                     ON die2.idInstrumentoEvaluacion = ie.idInstrumentoEvaluacion
                LEFT JOIN usuarios u
                     ON u.idUsuario = die2.idUsuario_Docente
                LEFT JOIN instrumentos_evaluacion_reactivos ier
                     ON ier.idInstrumentoEvaluacion = ie.idInstrumentoEvaluacion
                WHERE die.idUsuario_Docente = :idUsuario
                GROUP BY ie.idInstrumentoEvaluacion, ie.clave, ie.nombre, ie.indicaciones
                ORDER BY ie.idInstrumentoEvaluacion DESC";
 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idUsuario' => $idUsuario]);
        $rows = $stmt->fetchAll();
 
        // Extraer retícula del campo indicaciones
        foreach ($rows as &$row) {
            $parsed = $this->extraerReticula($row['indicaciones'] ?? '');
            $row['reticula']    = $parsed['reticula'];
            $row['indicaciones'] = $parsed['indicaciones'];
        }
        return $rows;
    }
 
    /**
     * Insertar instrumento + registrar al usuario como Autor.
     */
    public function insertarInstrumento(string $clave, string $nombre, string $indicaciones, string $reticula, int $idUsuario): int|false
    {
        $this->pdo->beginTransaction();
        try {
            $maxId = (int) $this->pdo->query(
                "SELECT COALESCE(MAX(idInstrumentoEvaluacion), 0) FROM instrumentos_evaluacion"
            )->fetchColumn();
            $nuevoId = $maxId + 1;
 
            $indicacionesPacked = $this->empacarIndicaciones($reticula, $indicaciones);
 
            $this->pdo->prepare(
                "INSERT INTO instrumentos_evaluacion
                     (idInstrumentoEvaluacion, clave, nombre, indicaciones)
                 VALUES (:id, :clave, :nombre, :indicaciones)"
            )->execute([
                ':id'           => $nuevoId,
                ':clave'        => $clave,
                ':nombre'       => $nombre,
                ':indicaciones' => $indicacionesPacked,
            ]);
 
            $this->pdo->prepare(
                "INSERT INTO docentes_instrumentosevaluacion
                     (idUsuario_Docente, idInstrumentoEvaluacion, colaboracion)
                 VALUES (:uid, :idInstr, 'Autor')"
            )->execute([':uid' => $idUsuario, ':idInstr' => $nuevoId]);
 
            $this->pdo->commit();
            return $nuevoId;
 
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("insertarInstrumento: " . $e->getMessage());
            return false;
        }
    }
 
    /**
     * Actualizar instrumento. Solo el Autor puede hacerlo.
     */
    public function actualizarInstrumento(int $id, string $clave, string $nombre, string $indicaciones, string $reticula, int $idUsuario): bool
    {
        $chk = $this->pdo->prepare(
            "SELECT 1 FROM docentes_instrumentosevaluacion
             WHERE idInstrumentoEvaluacion = :id
               AND idUsuario_Docente = :uid
               AND colaboracion = 'Autor'"
        );
        $chk->execute([':id' => $id, ':uid' => $idUsuario]);
        if (!$chk->fetch()) return false;
 
        $indicacionesPacked = $this->empacarIndicaciones($reticula, $indicaciones);
 
        $stmt = $this->pdo->prepare(
            "UPDATE instrumentos_evaluacion
             SET clave = :clave, nombre = :nombre, indicaciones = :indicaciones
             WHERE idInstrumentoEvaluacion = :id"
        );
        return $stmt->execute([
            ':clave'        => $clave,
            ':nombre'       => $nombre,
            ':indicaciones' => $indicacionesPacked,
            ':id'           => $id,
        ]);
    }
 
    /**
     * Eliminar instrumento y sus reactivos/criterios exclusivos.
     */
    public function eliminarInstrumento(int $id, int $idUsuario): bool
    {
        $chk = $this->pdo->prepare(
            "SELECT 1 FROM docentes_instrumentosevaluacion
             WHERE idInstrumentoEvaluacion = :id
               AND idUsuario_Docente = :uid
               AND colaboracion = 'Autor'"
        );
        $chk->execute([':id' => $id, ':uid' => $idUsuario]);
        if (!$chk->fetch()) return false;
 
        $this->pdo->beginTransaction();
        try {
            $solosStmt = $this->pdo->prepare(
                "SELECT ier.idReactivo
                 FROM instrumentos_evaluacion_reactivos ier
                 WHERE ier.idInstrumentoEvaluacion = :id
                   AND ier.idReactivo NOT IN (
                       SELECT idReactivo FROM instrumentos_evaluacion_reactivos
                       WHERE idInstrumentoEvaluacion <> :id2
                   )"
            );
            $solosStmt->execute([':id' => $id, ':id2' => $id]);
            $soloIds = $solosStmt->fetchAll(PDO::FETCH_COLUMN);
 
            if (!empty($soloIds)) {
                $in = implode(',', array_map('intval', $soloIds));
                $this->pdo->exec("DELETE FROM criterios WHERE idReactivo IN ($in)");
                $this->pdo->exec("DELETE FROM reactivos  WHERE idReactivo IN ($in)");
            }
 
            $this->pdo->prepare("DELETE FROM instrumentos_evaluacion_reactivos WHERE idInstrumentoEvaluacion = :id")->execute([':id' => $id]);
            $this->pdo->prepare("DELETE FROM docentes_instrumentosevaluacion WHERE idInstrumentoEvaluacion = :id")->execute([':id' => $id]);
 
            $stmt = $this->pdo->prepare("DELETE FROM instrumentos_evaluacion WHERE idInstrumentoEvaluacion = :id");
            $stmt->execute([':id' => $id]);
 
            $this->pdo->commit();
            return $stmt->rowCount() > 0;
 
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("eliminarInstrumento: " . $e->getMessage());
            return false;
        }
    }
 
    // ──────────────────────────────────────────────────────────
    // REACTIVOS
    // ──────────────────────────────────────────────────────────
 
    public function obtenerReactivos(int $idInstrumento): array
    {
        $sql = "SELECT r.idReactivo, r.enunciado, r.indicador, r.puntajeMaximo
                FROM reactivos r
                JOIN instrumentos_evaluacion_reactivos ier ON ier.idReactivo = r.idReactivo
                WHERE ier.idInstrumentoEvaluacion = :idInstr
                ORDER BY r.idReactivo ASC";
 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idInstr' => $idInstrumento]);
        return $stmt->fetchAll();
    }
 
    public function insertarReactivo(int $idInstrumento, string $enunciado, string $indicador, int $puntajeMaximo): int|false
    {
        $this->pdo->beginTransaction();
        try {
            $maxId = (int) $this->pdo->query("SELECT COALESCE(MAX(idReactivo), 0) FROM reactivos")->fetchColumn();
            $nuevoId = $maxId + 1;
 
            $this->pdo->prepare(
                "INSERT INTO reactivos (idReactivo, enunciado, indicador, puntajeMaximo)
                 VALUES (:id, :enunciado, :indicador, :puntaje)"
            )->execute([':id' => $nuevoId, ':enunciado' => $enunciado, ':indicador' => $indicador, ':puntaje' => $puntajeMaximo]);
 
            $this->pdo->prepare(
                "INSERT INTO instrumentos_evaluacion_reactivos (idInstrumentoEvaluacion, idReactivo)
                 VALUES (:idInstr, :idReact)"
            )->execute([':idInstr' => $idInstrumento, ':idReact' => $nuevoId]);
 
            $this->pdo->commit();
            return $nuevoId;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("insertarReactivo: " . $e->getMessage());
            return false;
        }
    }
 
    public function actualizarReactivo(int $idReactivo, string $enunciado, string $indicador, int $puntajeMaximo): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE reactivos SET enunciado=:enunciado, indicador=:indicador, puntajeMaximo=:puntaje WHERE idReactivo=:id"
        );
        return $stmt->execute([':enunciado'=>$enunciado, ':indicador'=>$indicador, ':puntaje'=>$puntajeMaximo, ':id'=>$idReactivo]);
    }
 
    public function eliminarReactivo(int $idReactivo): bool
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("DELETE FROM criterios WHERE idReactivo=:id")->execute([':id'=>$idReactivo]);
            $this->pdo->prepare("DELETE FROM instrumentos_evaluacion_reactivos WHERE idReactivo=:id")->execute([':id'=>$idReactivo]);
            $stmt = $this->pdo->prepare("DELETE FROM reactivos WHERE idReactivo=:id");
            $stmt->execute([':id'=>$idReactivo]);
            $this->pdo->commit();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("eliminarReactivo: " . $e->getMessage());
            return false;
        }
    }
 
    // ──────────────────────────────────────────────────────────
    // CRITERIOS
    // ──────────────────────────────────────────────────────────
 
    public function obtenerCriterios(int $idReactivo): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT idCriterio, puntajeObtenido, retroalimentacion
             FROM criterios WHERE idReactivo=:idReactivo ORDER BY idCriterio ASC"
        );
        $stmt->execute([':idReactivo' => $idReactivo]);
        return $stmt->fetchAll();
    }
 
    public function insertarCriterio(int $idReactivo, int $puntajeObtenido, string $retroalimentacion): int|false
    {
        $maxId   = (int) $this->pdo->query("SELECT COALESCE(MAX(idCriterio),0) FROM criterios")->fetchColumn();
        $nuevoId = $maxId + 1;
 
        $stmt = $this->pdo->prepare(
            "INSERT INTO criterios (idCriterio, puntajeObtenido, retroalimentacion, idReactivo)
             VALUES (:id, :puntaje, :retro, :idReactivo)"
        );
        $ok = $stmt->execute([':id'=>$nuevoId, ':puntaje'=>$puntajeObtenido, ':retro'=>$retroalimentacion, ':idReactivo'=>$idReactivo]);
        return $ok ? $nuevoId : false;
    }
 
    public function actualizarCriterio(int $idCriterio, int $puntajeObtenido, string $retroalimentacion): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE criterios SET puntajeObtenido=:puntaje, retroalimentacion=:retro WHERE idCriterio=:id"
        );
        return $stmt->execute([':puntaje'=>$puntajeObtenido, ':retro'=>$retroalimentacion, ':id'=>$idCriterio]);
    }
 
    public function eliminarCriterio(int $idCriterio): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM criterios WHERE idCriterio=:id");
        $stmt->execute([':id'=>$idCriterio]);
        return $stmt->rowCount() > 0;
    }
}
 