<?php
namespace App\Controllers;

use App\Models\Cita;
use App\Models\Usuario;
use App\Core\Session;
use App\Middleware\AuthMiddleware;

class CitaController {
    
    public function __construct() {
        // Todas las peticiones a CitaController requieren estar logueado
        AuthMiddleware::requireLogin();
    }

    public function index() {
        $rol = Session::get('usuario_rol');
        $usuario_id = Session::get('usuario_id');
        $citaModel = new Cita();
        
        if ($rol === 'medico') {
            $citas = $citaModel->getByMedico($usuario_id);
        } else {
            $citas = $citaModel->getByPaciente($usuario_id);
        }

        require_once __DIR__ . '/../views/citas/index.php';
    }

    public function pacientes() {
        AuthMiddleware::requireRol('medico');
        $usuarioModel = new Usuario();
        $id_medico = Session::get('usuario_id');
        $pacientes = $usuarioModel->getPacientesByMedico($id_medico);
        require_once __DIR__ . '/../views/citas/pacientes.php';
    }

    public function historial() {
        AuthMiddleware::requireRol('medico');
        if (!isset($_GET['id'])) {
            die("ID de paciente no proporcionado.");
        }
        $id_paciente = (int) $_GET['id'];
        $id_medico = Session::get('usuario_id');

        $usuarioModel = new Usuario();
        $paciente = $usuarioModel->getById($id_paciente);
        
        if (!$paciente) {
            die("Paciente no encontrado.");
        }
        
        $citaModel = new Cita();
        $historial = $citaModel->getHistorialByPaciente($id_paciente, $id_medico);
        
        require_once __DIR__ . '/../views/citas/historial.php';
    }

    public function create() {
        AuthMiddleware::requireRol('medico');
        $usuarioModel = new Usuario();
        $pacientes = $usuarioModel->getAllPacientes();
        require_once __DIR__ . '/../views/citas/create.php';
    }

    public function store() {
        AuthMiddleware::requireRol('medico');
        $id_medico = Session::get('usuario_id');
        $id_paciente = (int) $_POST['id_paciente'];

        $datos = [
            'id_paciente' => $id_paciente,
            'id_medico'   => $id_medico,
            'fecha_hora'  => $_POST['fecha_hora'],
            'motivo'      => htmlspecialchars($_POST['motivo'] ?? '')
        ];

        $citaModel = new Cita();
        $citaModel->crear($datos);

        $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/citas?msg=Cita agendada con éxito');
    }

    public function update() {
        $id_cita = (int) $_POST['id_cita'];
        $citaModel = new Cita();
        $cita = $citaModel->getById($id_cita);

        if (!$cita) {
            die("Cita no encontrada");
        }

        // Si se envía fecha_hora, es reprogramación
        if (!empty($_POST['fecha_hora'])) {
            $citaModel->reprogramar($id_cita, $_POST['fecha_hora']);
        }
        
        // Si el médico confirma la cita
        if (!empty($_POST['estado'])) {
            AuthMiddleware::requireRol('medico'); // solo médico cambia el estado
            $estado = $_POST['estado'];
            // Validar enum de estado
            if (in_array($estado, ['solicitada', 'confirmada', 'atendida', 'cancelada'])) {
                $citaModel->updateEstado($id_cita, $estado);
            }
        }

        $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/citas?msg=Cita actualizada');
    }

    public function cancel() {
        $id_cita = (int) $_POST['id_cita'];
        $citaModel = new Cita();
        
        // TODO: Validar que la cita pertenezca al usuario en sesión
        $citaModel->updateEstado($id_cita, 'cancelada');
        
        $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/citas?msg=Cita cancelada');
    }
}
