<?php
namespace App\Controllers;

use App\Models\Cita;
use App\Models\Consulta;
use App\Models\Receta;
use App\Models\Cobro;
use App\Models\PlanPago;
use App\Middleware\AuthMiddleware;

class ConsultaController {
    public function __construct() {
        AuthMiddleware::requireLogin();
    }

    public function atender() {
        $id_cita = (int) ($_GET['id_cita'] ?? 0);
        if (!$id_cita) die("ID de cita requerido");

        $citaModel = new Cita();
        $cita = $citaModel->getById($id_cita);

        if (!$cita) die("Cita no encontrada");

        $consultaModel = new Consulta();
        $consulta = $consultaModel->getByCita($id_cita);

        $recetas = [];
        $estudios = [];
        $cobro = null;
        if ($consulta) {
            $recetaModel = new Receta();
            $recetas = $recetaModel->getByConsulta($consulta['id_consulta']);
            
            // Requerimiento RF09: Visualizar archivos y estudios adjuntos vinculados a una cita
            $estudioModel = new \App\Models\Estudio();
            $estudios = $estudioModel->getByCita($id_cita);

            // Cargar datos de cobro si existen
            $cobroModel = new Cobro();
            $cobro = $cobroModel->getByConsulta($consulta['id_consulta']);
            
            $planPagoData = null;
            if ($cobro && $cobro['metodo_pago'] === 'meses') {
                $planPagoModel = new PlanPago();
                $planPagoData = $planPagoModel->getPlanYAmortizacionesByCobro($cobro['id_cobro']);
            }
        }

        require_once __DIR__ . '/../views/consultas/atender.php';
    }

    public function guardarCanvas() {
        AuthMiddleware::requireRol('medico');
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['image']) || empty($input['id_consulta'])) {
            http_response_code(400);
            return false;
        }

        $base64_string = $input['image'];
        list($type, $data) = explode(';', $base64_string);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);

        $uuid = bin2hex(random_bytes(16));
        $filename = $uuid . '.png';
        $path = __DIR__ . '/../../storage/canvas/' . $filename;

        if (file_put_contents($path, $data)) {
            $consultaModel = new Consulta();
            $consultaModel->updateCanvas((int)$input['id_consulta'], $filename);
            echo json_encode(['success' => true, 'filename' => $filename]);
            return true;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar la imagen en el servidor']);
            return false;
        }
    }

    public function viewCanvas() {
        AuthMiddleware::requireLogin();
        $id_consulta = (int) ($_GET['id'] ?? 0);
        $consultaModel = new Consulta();
        $consulta = $consultaModel->getById($id_consulta);
        if (!$consulta || !$consulta['anotaciones_canvas']) die("No hay imagen");
        
        $path = __DIR__ . '/../../storage/canvas/' . $consulta['anotaciones_canvas'];
        if (file_exists($path)) {
            header('Content-Type: image/png');
            readfile($path);
        } else {
            http_response_code(404);
            die("Imagen no encontrada físicamente");
        }
    }

    public function store() {
        AuthMiddleware::requireRol('medico');
        
        $id_cita = (int) $_POST['id_cita'];
        $datos = [
            'id_cita' => $id_cita,
            'diagnostico' => htmlspecialchars($_POST['diagnostico']),
            'tratamiento' => htmlspecialchars($_POST['tratamiento'] ?? '')
        ];

        $consultaModel = new Consulta();
        // Verificar si ya existe
        $existe = $consultaModel->getByCita($id_cita);
        if (!$existe) {
            $consultaModel->crear($datos);
            // Actualizar estado de cita a atendida
            $citaModel = new Cita();
            $citaModel->updateEstado($id_cita, 'atendida');
        }

        $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/consultas/atender?id_cita=' . $id_cita . '&msg=Consulta guardada');
    }

    public function storeCobro() {
        AuthMiddleware::requireRol('medico');

        $id_consulta = (int) $_POST['id_consulta'];
        $id_cita = (int) $_POST['id_cita'];

        // Verificar que no exista ya un cobro para esta consulta
        $cobroModel = new Cobro();
        $existente = $cobroModel->getByConsulta($id_consulta);
        
        if (!$existente) {
            $monto = (float) $_POST['monto'];
            $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
            
            $id_cobro = $cobroModel->crear([
                'id_consulta' => $id_consulta,
                'monto' => $monto,
                'metodo_pago' => $metodo_pago,
                'notas' => htmlspecialchars($_POST['notas'] ?? '')
            ]);

            if ($metodo_pago === 'meses') {
                $no_pagos = (int) ($_POST['no_pagos'] ?? 1);
                $frecuencia = $_POST['frecuencia'] ?? 'mensual';
                
                if ($no_pagos > 0) {
                    $planPagoModel = new PlanPago();
                    $planPagoModel->crearPlan($id_cobro, $monto, $no_pagos, $frecuencia);
                }
            }
        }

        $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/consultas/atender?id_cita=' . $id_cita . '&msg=Cobro registrado exitosamente');
    }

    public function toggleAmortizacion() {
        AuthMiddleware::requireRol('medico');
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id_amortizacion']) || empty($input['estado'])) {
            http_response_code(400);
            return;
        }

        $planPagoModel = new PlanPago();
        $res = $planPagoModel->toggleEstadoAmortizacion((int)$input['id_amortizacion'], $input['estado']);
        echo json_encode(['success' => $res]);
    }
}
