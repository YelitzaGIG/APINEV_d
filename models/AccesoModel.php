<?php
// =============================================================
// models/AccesoModel.php — Modelo de Accesos (bitácora)
// Registra entradas/salidas en la tabla Accesos de SINTESU
// =============================================================

class AccesoModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ----------------------------------------------------------
    // Registra un acceso exitoso (entrada)
    // ----------------------------------------------------------
    public function registrarEntrada(int $idUsuario, string $autorizacion = 'OK'): int|false
    {
        $sql = "INSERT INTO Accesos (fechaAcceso, autorizacion, estado, idUsuario)
                VALUES (NOW(), :autorizacion, 'A', :idUsuario)";

        $stmt = $this->pdo->prepare($sql);
        $ok   = $stmt->execute([
            ':autorizacion' => $autorizacion,
            ':idUsuario'    => $idUsuario,
        ]);

        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    // ----------------------------------------------------------
    // Registra un intento fallido de acceso
    // ----------------------------------------------------------
    public function registrarFallo(int $idUsuario): void
    {
        $sql = "INSERT INTO Accesos (fechaAcceso, autorizacion, estado, idUsuario)
                VALUES (NOW(), 'FALLO', 'F', :idUsuario)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idUsuario' => $idUsuario]);
    }

    // ----------------------------------------------------------
    // Registra la salida del usuario (cierre de sesión)
    // ----------------------------------------------------------
    public function registrarSalida(int $idAcceso): void
    {
        $sql = "UPDATE Accesos
                SET    fechaSalida = NOW(), estado = 'S'
                WHERE  idAcceso = :idAcceso";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idAcceso' => $idAcceso]);
    }
}
