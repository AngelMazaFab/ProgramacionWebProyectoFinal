<?php
namespace App\Controllers;

use App\Models\Estudio;
use App\Models\Cita;
use App\Middleware\AuthMiddleware;
use App\Core\Session;

class EstudioController {
    public function __construct() {
        AuthMiddleware::requireLogin();
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['estudio_file'])) {
            die("Petición inválida");
        }

        $id_cita = (int) $_POST['id_cita'];
        
        $citaModel = new Cita();
        $cita = $citaModel->getById($id_cita);
        if (!$cita) die("Cita no encontrada");

        $file = $_FILES['estudio_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/consultas/atender?id_cita=' . $id_cita . '&error=Error al subir el archivo');
            return;
        }

        $mime = mime_content_type($file['tmp_name']);
        $allowed = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];
        
        if (!array_key_exists($mime, $allowed)) {
            $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/consultas/atender?id_cita=' . $id_cita . '&error=Tipo de archivo no permitido (solo PDF, JPG, PNG)');
            return;
        }

        $extension = $allowed[$mime];
        $uuid = bin2hex(random_bytes(16));
        $physicalName = $uuid . '.' . $extension;
        $destination = __DIR__ . '/../../storage/uploads/' . $physicalName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $estudioModel = new Estudio();
            $estudioModel->crear([
                'id_paciente' => $cita['id_paciente'],
                'id_cita' => $id_cita,
                'nombre_archivo' => $file['name'],
                'ruta_archivo' => $physicalName,
                'tipo_archivo' => $extension
            ]);
            $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/consultas/atender?id_cita=' . $id_cita . '&msg=Estudio subido correctamente');
        } else {
            $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/consultas/atender?id_cita=' . $id_cita . '&error=Error guardando el archivo fisicamente');
        }
    }

    public function download() {
        $id_estudio = (int) ($_GET['id'] ?? 0);
        $estudioModel = new Estudio();
        $estudio = $estudioModel->getById($id_estudio);

        if (!$estudio) {
            http_response_code(404);
            die("Archivo no encontrado");
        }

        $rol = Session::get('usuario_rol');
        $usuario_id = Session::get('usuario_id');
        if ($rol === 'paciente' && $estudio['id_paciente'] != $usuario_id) {
            http_response_code(403);
            die("Acceso denegado: el estudio no le pertenece");
        }

        $filepath = __DIR__ . '/../../storage/uploads/' . $estudio['ruta_archivo'];
        if (!file_exists($filepath)) {
            http_response_code(404);
            die("El archivo físico no existe en el servidor");
        }

        $content_types = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'png' => 'image/png'
        ];

        header('Content-Type: ' . $content_types[$estudio['tipo_archivo']]);
        header('Content-Disposition: inline; filename="' . $estudio['nombre_archivo'] . '"');
        readfile($filepath);
    }
}
