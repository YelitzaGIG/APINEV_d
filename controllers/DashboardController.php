<?php
// =============================================================
// controllers/DashboardController.php — Controlador del panel
// Valida sesión y gestiona las secciones del dashboard
// =============================================================

require_once __DIR__ . '/../config/config.php';

class DashboardController
{
    // ----------------------------------------------------------
    // Verifica que el usuario tenga sesión activa.
    // Si no, redirige al login.
    // ----------------------------------------------------------
    public static function verificarSesion(): void
    {
        if (empty($_SESSION['sintesu_auth']) || $_SESSION['sintesu_auth'] !== true) {
            header('Location: ' . BASE_URL . '/views/auth/login.php');
            exit;
        }

        // Verificar tiempo de sesión
        if (!empty($_SESSION['sintesu_inicio'])) {
            if (time() - $_SESSION['sintesu_inicio'] > SESSION_LIFETIME) {
                session_unset();
                session_destroy();
                header('Location: ' . BASE_URL . '/views/auth/login.php?timeout=1');
                exit;
            }
            // Renovar el tiempo
            $_SESSION['sintesu_inicio'] = time();
        }
    }

    // ----------------------------------------------------------
    // Retorna los datos del usuario en sesión
    // ----------------------------------------------------------
    public static function usuarioSesion(): array
    {
        return [
            'id'     => $_SESSION['sintesu_idUsuario'] ?? '',
            'nombre' => $_SESSION['sintesu_nombre']    ?? 'Usuario',
            'rol'    => $_SESSION['sintesu_rol']        ?? '',
            'nivel'  => $_SESSION['sintesu_nivelRol']   ?? 0,
            'depto'  => $_SESSION['sintesu_depto']      ?? '',
        ];
    }
}
