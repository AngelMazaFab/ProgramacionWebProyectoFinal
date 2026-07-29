<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Core\Database;
use App\Models\Consulta;
use App\Models\Receta;
use Dompdf\Dompdf;
use PDO;

class DashboardController {
    public function __construct() {
        AuthMiddleware::requireLogin();
    }

    public function index() {
        AuthMiddleware::requireRol('medico');
        require_once __DIR__ . '/../views/dashboard/index.php';
    }

    public function metrics() {
        AuthMiddleware::requireRol('medico');
        
        $db = Database::getInstance()->getConnection();
        
        // RF12: Métricas - Citas por estado
        $sqlCitas = "SELECT estado, COUNT(*) as total FROM Citas GROUP BY estado";
        $stmtCitas = $db->query($sqlCitas);
        $citasEstado = $stmtCitas->fetchAll();
        
        // RF12: Métricas - Consultas por mes (últimos 6 meses)
        $sqlConsultas = "SELECT DATE_FORMAT(fecha_atencion, '%Y-%m') as mes, COUNT(*) as total 
                         FROM Consultas 
                         GROUP BY mes ORDER BY mes DESC LIMIT 6";
        $stmtConsultas = $db->query($sqlConsultas);
        $consultasMes = $stmtConsultas->fetchAll();
        
        echo json_encode([
            'citas' => $citasEstado,
            'consultas' => array_reverse($consultasMes)
        ]);
    }

    public function reporte() {
        AuthMiddleware::requireRol('medico');

        if (!class_exists('Dompdf\Dompdf')) {
            die("Error: La librería Dompdf no está instalada. Por favor ejecute 'composer install' en el servidor.");
        }

        $db = Database::getInstance()->getConnection();
        $mes_actual = date('Y-m');
        
        $sql = "SELECT c.id_consulta, c.fecha_atencion, ci.motivo, u.nombre as paciente 
                FROM Consultas c
                JOIN Citas ci ON c.id_cita = ci.id_cita
                JOIN Usuarios u ON ci.id_paciente = u.id_usuario
                WHERE DATE_FORMAT(c.fecha_atencion, '%Y-%m') = :mes";
        $stmt = $db->prepare($sql);
        $stmt->execute([':mes' => $mes_actual]);
        $consultas = $stmt->fetchAll();

        // Construir la plantilla HTML (RF15)
        $html = "<h1>Reporte Ejecutivo Mensual - " . date('m/Y') . "</h1>";
        $html .= "<p>Total de consultas atendidas este mes: " . count($consultas) . "</p>";
        $html .= "<table border='1' cellpadding='5' cellspacing='0' width='100%' style='border-collapse: collapse; font-family: sans-serif;'>";
        $html .= "<tr style='background-color:#f0f0f0;'><th>Fecha</th><th>Paciente</th><th>Motivo</th></tr>";
        foreach ($consultas as $c) {
            $html .= "<tr>
                        <td>{$c['fecha_atencion']}</td>
                        <td>{$c['paciente']}</td>
                        <td>{$c['motivo']}</td>
                      </tr>";
        }
        $html .= "</table>";

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        $filepath = __DIR__ . '/../../storage/pdf/reporte_' . date('Y_m') . '.pdf';
        file_put_contents($filepath, $output);

        $dompdf->stream("Reporte_Mensual.pdf", ["Attachment" => true]);
    }

    public function receta() {
        $id_consulta = (int) $_GET['id_consulta'];
        
        $consultaModel = new Consulta();
        $consulta = $consultaModel->getById($id_consulta);
        if (!$consulta) die("Consulta no encontrada");

        if (!class_exists('Dompdf\Dompdf')) {
            die("Error: La librería Dompdf no está instalada. Por favor ejecute 'composer install' en el servidor.");
        }

        $recetaModel = new Receta();
        $recetas = $recetaModel->getByConsulta($id_consulta);

        // Construir la plantilla HTML para la Receta (RF14)
        $html = "<div style='font-family: sans-serif;'>";
        $html .= "<h1 style='color: #1F4E79;'>MediControl - Receta Médica</h1>";
        $html .= "<p><strong>Fecha de atención:</strong> {$consulta['fecha_atencion']}</p>";
        $html .= "<h2>Diagnóstico:</h2><p>" . nl2br($consulta['diagnostico']) . "</p>";
        
        if (count($recetas) > 0) {
            $html .= "<h2>Medicamentos Prescritos:</h2><ul>";
            foreach ($recetas as $r) {
                $html .= "<li><strong>{$r['medicamento']}</strong>: {$r['dosis']}. <br><em>{$r['indicaciones']}</em></li><br>";
            }
            $html .= "</ul>";
        } else {
            $html .= "<p>No se prescribieron medicamentos.</p>";
        }
        
        $html .= "<br><br><br><hr><p style='text-align:center;'>Firma del Médico</p></div>";

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'landscape');
        $dompdf->render();

        $output = $dompdf->output();
        $filepath = __DIR__ . '/../../storage/pdf/receta_' . $id_consulta . '.pdf';
        file_put_contents($filepath, $output);

        $dompdf->stream("Receta_$id_consulta.pdf", ["Attachment" => true]);
    }
}
