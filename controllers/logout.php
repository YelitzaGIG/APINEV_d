<?php
// controllers/logout.php — Punto de entrada para cerrar sesión
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$ctrl = new AuthController($pdo);
$ctrl->logout();
