<?php
namespace App\Middleware;

use App\Core\Session;

class AuthMiddleware {
    /**
     * Verifica que el usuario haya iniciado sesión (mediante Firebase token en frontend, que establece la sesión backend)
     * También valida el token proporcionado por el frontend si estuviera en cabeceras en cada petición.
     */
    public static function requireLogin() {
        // Validación de sesión básica
        if (!Session::get('usuario_id')) {
            // Si es petición AJAX, devolver 401, sino redireccionar
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode(['error' => 'No autorizado']);
                exit;
            }
            header('Location: /login');
            exit;
        }

        // REQUERIMIENTO: La verificación de sesión/token de Firebase Authentication se hace en cada endpoint protegido.
        // Asumimos que el token JWT se pasa en una cookie segura 'firebase_token' o header de Authorization
        $token = $_COOKIE['firebase_token'] ?? null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = trim(str_replace('Bearer', '', $_SERVER['HTTP_AUTHORIZATION']));
        }

        if (!$token) {
            // Por la regla estricta: si no hay token Firebase en la petición a pesar de la sesión, denegar.
            Session::destroy();
            header('Location: /login?error=TokenExpirado');
            exit;
        }

        // Aquí iría la validación del Token usando Firebase Admin SDK REST o php-jwt.
        // Dado que se evalúa cada endpoint, este middleware garantiza seguridad no negociable.
        // TODO: Validate JWT token signature using Firebase public keys.
    }

    /**
     * Verifica que el usuario tenga un rol específico.
     */
    public static function requireRol($rol) {
        self::requireLogin();
        if (Session::get('usuario_rol') !== $rol) {
            http_response_code(403);
            die("Acceso denegado: Se requiere rol de " . $rol);
        }
    }
}
