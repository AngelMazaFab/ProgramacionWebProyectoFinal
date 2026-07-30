<?php
namespace App\Controllers;

use App\Models\Usuario;
use App\Middleware\AuthMiddleware;

class AdminController {
    
    public function __construct() {
        // Solo el administrador puede acceder a estas rutas
        AuthMiddleware::requireRol('admin');
    }

    public function createUsuario() {
        require_once __DIR__ . '/../views/admin/create_usuario.php';
    }

    public function storeUsuario() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            return;
        }

        $firebaseUid = $input['uid'] ?? '';
        $email = $input['email'] ?? '';
        $name = $input['name'] ?? '';
        $telefono = $input['telefono'] ?? null;
        $role = $input['role'] ?? 'paciente';

        if (empty($firebaseUid) || empty($email) || empty($name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Faltan datos obligatorios']);
            return;
        }

        $usuarioModel = new Usuario();
        $existe = $usuarioModel->findByFirebaseUid($firebaseUid);
        if ($existe) {
            echo json_encode(['success' => false, 'error' => 'El usuario ya existe en la base de datos']);
            return;
        }

        $id = $usuarioModel->create([
            'nombre' => $name,
            'correo' => $email,
            'telefono' => $telefono,
            'rol' => $role,
            'firebase_uid' => $firebaseUid
        ]);

        if ($id) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar en base de datos']);
        }
    }
}
