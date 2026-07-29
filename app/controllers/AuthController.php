<?php
namespace App\Controllers;

use App\Models\Usuario;
use App\Core\Session;

class AuthController {
    
    public function showLogin() {
        // Si ya está logueado, redirigir
        if (Session::get('usuario_id')) {
            $rol = Session::get('usuario_rol');
            $baseUrl = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            if ($baseUrl === '/') $baseUrl = '';
            $dest = '/citas';
            if ($rol === 'medico') $dest = '/dashboard';
            if ($rol === 'admin') $dest = '/admin/usuarios/nuevo';
            header('Location: ' . $baseUrl . $dest);
            exit;
        }
        // Render view
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function login() {
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? null;

        if (!$token) {
            http_response_code(400);
            echo json_encode(['error' => 'No se proporcionó token de Firebase']);
            return;
        }

        // Firebase Auth envía el token y la info decodificada temporalmente por ahora
        // ya que el proyecto firebase real lo conectará el equipo.
        $firebaseUid = $input['uid'] ?? 'TEST_UID_' . rand(1000, 9999);
        $correo = $input['email'] ?? 'test@test.com';
        $nombre = $input['name'] ?? 'Usuario Web';
        
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findByFirebaseUid($firebaseUid);

        if (!$usuario) {
            // Se registra en MySQL automáticamente con rol paciente (CU-01 Flujo alternativo)
            $id = $usuarioModel->create([
                'nombre' => $nombre,
                'correo' => $correo,
                'rol' => 'paciente',
                'firebase_uid' => $firebaseUid
            ]);
            $usuario = $usuarioModel->findByFirebaseUid($firebaseUid);
        }

        // Establecer sesión
        Session::set('usuario_id', $usuario['id_usuario']);
        Session::set('usuario_rol', $usuario['rol']);
        Session::set('usuario_nombre', $usuario['nombre']);

        // Setear cookie con token para que el middleware lo valide en cada endpoint (regla estricta)
        setcookie('firebase_token', $token, time() + 3600, '/', "", false, true); // HttpOnly flag

        $dest = '/citas';
        if ($usuario['rol'] === 'medico') $dest = '/dashboard';
        if ($usuario['rol'] === 'admin') $dest = '/admin/usuarios/nuevo';

        echo json_encode([
            'success' => true, 
            'rol' => $usuario['rol'], 
            'redirect' => $dest
        ]);
    }

    public function logout() {
        Session::destroy();
        setcookie('firebase_token', '', time() - 3600, '/');
        echo json_encode(['success' => true, 'redirect' => '/login']);
    }
}
