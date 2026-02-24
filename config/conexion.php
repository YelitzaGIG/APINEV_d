<?php
// =============================================================
// config/conexion.php — Conexión PDO a la base de datos SINTESU
// =============================================================

//error_reporting(0);
//ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ── Datos de la base de datos ──────────────────────────────
$host    = "127.0.0.1";
$port    = "8080";
$dbname  = "sintesu";
$user    = "desarrollo";
$pass    = "desarrollo";
$charset = "utf8mb4";

// ── Opciones PDO ───────────────────────────────────────────
try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset",
        $user,
        $pass,
        $options
    );

} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());

    // Si es petición AJAX → responder JSON
    if (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
        header("Content-Type: application/json");
        echo json_encode([
            "status"  => "error",
            "mensaje" => "Error de conexión a la base de datos"
        ]);
        exit;
    }

    die("Error de conexión a la base de datos");
}
