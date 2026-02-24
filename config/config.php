<?php
// config/config.php

// ROOT_PATH: ruta absoluta a la carpeta raíz del proyecto (donde está index.php)
// dirname(__FILE__) es más confiable que dirname(__DIR__) en Windows/XAMPP
define('ROOT_PATH', str_replace('\\', '/', dirname(__FILE__) . '/..'));

// BASE_URL: detectada automáticamente — funciona en XAMPP sin tocar nada
(function () {
    $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $docRoot   = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $rootPath  = rtrim(str_replace('\\', '/', dirname(__FILE__) . '/..'), '/');
    $subFolder = str_replace($docRoot, '', $rootPath);
    define('BASE_URL', $protocol . '://' . $host . $subFolder);
})();

define('APP_NAME',    'SINTESU');
define('APP_SUBNAME', 'Aplicación para Instrumentos de Evaluación (APINEV)');
define('APP_INST',    'ITSOEH');
define('SESSION_LIFETIME', 7200);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}