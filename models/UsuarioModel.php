<?php
// =============================================================
// models/UsuarioModel.php — Modelo de Usuarios
// Interactúa directamente con la tabla Usuarios de SINTESU
// =============================================================

class UsuarioModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ----------------------------------------------------------
    // Busca un usuario por su idUsuario (login con matrícula)
    // Retorna el array con todos sus datos o false si no existe
    // ----------------------------------------------------------
    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT u.idUsuario,
                       u.contrasenia,
                       u.nombres,
                       u.apPaterno,
                       u.apMaterno,
                       u.telefono,
                       u.`correo-e`  AS correo,
                       r.nombre      AS rol,
                       r.nivel       AS nivelRol,
                       d.nombre      AS departamento
                FROM   Usuarios u
                JOIN   Roles r        ON r.idRol          = u.idRol
                JOIN   Departamentos d ON d.idDepartamento = u.idDepartamento
                WHERE  u.idUsuario = :id
                LIMIT  1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ----------------------------------------------------------
    // Busca un usuario por correo electrónico
    // ----------------------------------------------------------
    public function buscarPorCorreo(string $correo): array|false
    {
        $sql = "SELECT u.idUsuario,
                       u.contrasenia,
                       u.nombres,
                       u.apPaterno,
                       u.apMaterno,
                       u.telefono,
                       u.`correo-e`  AS correo,
                       r.nombre      AS rol,
                       r.nivel       AS nivelRol,
                       d.nombre      AS departamento
                FROM   Usuarios u
                JOIN   Roles r         ON r.idRol          = u.idRol
                JOIN   Departamentos d  ON d.idDepartamento = u.idDepartamento
                WHERE  u.`correo-e` = :correo
                LIMIT  1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetch();
    }

    // ----------------------------------------------------------
    // Registra un nuevo usuario en la BD
    // Retorna el idUsuario insertado o false en error
    // ----------------------------------------------------------
    public function crear(array $datos): int|false
    {
        $sql = "INSERT INTO Usuarios
                    (idUsuario, contrasenia, apPaterno, apMaterno,
                     nombres, telefono, `correo-e`, idRol, idDepartamento)
                VALUES
                    (:idUsuario, :contrasenia, :apPaterno, :apMaterno,
                     :nombres, :telefono, :correo, :idRol, :idDepartamento)";

        $stmt = $this->pdo->prepare($sql);
        $ok   = $stmt->execute([
            ':idUsuario'      => $datos['idUsuario'],
            ':contrasenia'    => password_hash($datos['contrasenia'], PASSWORD_BCRYPT),
            ':apPaterno'      => $datos['apPaterno'],
            ':apMaterno'      => $datos['apMaterno']  ?? null,
            ':nombres'        => $datos['nombres'],
            ':telefono'       => $datos['telefono']   ?? null,
            ':correo'         => $datos['correo'],
            ':idRol'          => $datos['idRol']      ?? 3,   // Rol por defecto: Docente
            ':idDepartamento' => $datos['idDepartamento'],
        ]);

        return $ok ? (int)$this->pdo->lastInsertId() : false;
    }

    // ----------------------------------------------------------
    // Verifica si ya existe un idUsuario (matrícula) en la BD
    // ----------------------------------------------------------
    public function existeId(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM Usuarios WHERE idUsuario = :id"
        );
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ----------------------------------------------------------
    // Verifica si ya existe un correo en la BD
    // ----------------------------------------------------------
    public function existeCorreo(string $correo): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM Usuarios WHERE `correo-e` = :correo"
        );
        $stmt->execute([':correo' => $correo]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
