<?php
namespace App\Controllers;

use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Models\Usuario;
use App\Models\Cita;

class ComentarioController {

    public function index() {
        AuthMiddleware::requireRol('medico');
        
        $filepath = __DIR__ . '/../../storage/logs/comentarios.log';
        $comentarios = [];

        // RF07: Lectura secuencial de la bitácora
        if (file_exists($filepath)) {
            $handle = fopen($filepath, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $parts = explode('|', trim($line));
                    if (count($parts) === 4) {
                        $comentarios[] = [
                            'fecha' => $parts[0],
                            'id_paciente' => $parts[1],
                            'calificacion' => $parts[2],
                            'comentario' => $parts[3]
                        ];
                    }
                }
                fclose($handle);
            }
        }
        
        // Mapear los nombres de los pacientes para la vista
        $usuarioModel = new Usuario();
        foreach ($comentarios as &$com) {
            $p = $usuarioModel->getById((int)$com['id_paciente']);
            $com['paciente_nombre'] = $p ? $p['nombre'] : 'Desconocido';
        }

        // Mostrar los más recientes primero
        $comentarios = array_reverse($comentarios);
        
        require_once __DIR__ . '/../views/comentarios/index.php';
    }

    public function create() {
        AuthMiddleware::requireRol('paciente');
        
        $id_cita = (int)($_GET['id_cita'] ?? 0);
        $citaModel = new Cita();
        $cita = $citaModel->getById($id_cita);
        
        if (!$cita || $cita['id_paciente'] != Session::get('usuario_id') || $cita['estado'] !== 'atendida') {
            $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/citas?error=No puedes comentar esta cita');
            return;
        }

        require_once __DIR__ . '/../views/comentarios/create.php';
    }

    public function store() {
        AuthMiddleware::requireRol('paciente');
        
        $id_paciente = Session::get('usuario_id');
        $id_cita = (int) $_POST['id_cita'];
        $calificacion = (int) $_POST['calificacion'];
        
        // Sanitizar el texto evitando que el pipe o retornos de carro corrompan el formato (fecha|id|calificacion|comentario)
        $comentario_limpio = str_replace(["\r", "\n", "|"], " ", $_POST['comentario']); 
        $comentario = htmlspecialchars($comentario_limpio);
        
        $fecha = date('Y-m-d H:i:s');
        $linea = "{$fecha}|{$id_paciente}|{$calificacion}|{$comentario}" . PHP_EOL;
        
        $filepath = __DIR__ . '/../../storage/logs/comentarios.log';

        // RF06: Módulo de registro en archivos secuenciales (fopen, flock, fwrite)
        $handle = fopen($filepath, 'a');
        if ($handle) {
            // Prevenir concurrencia
            if (flock($handle, LOCK_EX)) {
                fwrite($handle, $linea);
                flock($handle, LOCK_UN);
            }
            fclose($handle);
        }

        $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/citas?msg=Gracias por tu retroalimentación');
    }
}
