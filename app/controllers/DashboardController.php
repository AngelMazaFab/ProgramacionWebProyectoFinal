<?php
namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Core\Database;
use App\Core\Session;
use App\Models\Consulta;
use App\Models\Receta;
use App\Models\Cobro;
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
        $id_medico = Session::get('usuario_id');
        $mes_actual = date('Y-m');
        
        // --- MÉTRICAS CLÍNICAS ---
        
        // Citas por estado (global del médico)
        $sqlCitas = "SELECT estado, COUNT(*) as total FROM Citas WHERE id_medico = :id GROUP BY estado";
        $stmtCitas = $db->prepare($sqlCitas);
        $stmtCitas->execute([':id' => $id_medico]);
        $citasEstado = $stmtCitas->fetchAll();
        
        // Generar los últimos 6 meses para rellenar vacíos
        $ultimos6Meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $ultimos6Meses[date('Y-m', strtotime("-$i months"))] = 0;
        }
        
        // Consultas por mes (últimos 6 meses)
        $sqlConsultas = "SELECT DATE_FORMAT(con.fecha_atencion, '%Y-%m') as mes, COUNT(*) as total 
                         FROM Consultas con
                         JOIN Citas ci ON con.id_cita = ci.id_cita
                         WHERE ci.id_medico = :id
                         AND con.fecha_atencion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                         GROUP BY mes";
        $stmtConsultas = $db->prepare($sqlConsultas);
        $stmtConsultas->execute([':id' => $id_medico]);
        $consultasData = $stmtConsultas->fetchAll(PDO::FETCH_ASSOC);
        
        $consultasMesAssoc = $ultimos6Meses;
        foreach ($consultasData as $row) {
            if (isset($consultasMesAssoc[$row['mes']])) {
                $consultasMesAssoc[$row['mes']] = (int)$row['total'];
            }
        }
        $consultasMes = [];
        foreach ($consultasMesAssoc as $m => $t) {
            $consultasMes[] = ['mes' => $m, 'total' => $t];
        }
        
        // Total de pacientes únicos del doctor
        $sqlPacientes = "SELECT COUNT(DISTINCT id_paciente) as total FROM Citas WHERE id_medico = :id";
        $stmtP = $db->prepare($sqlPacientes);
        $stmtP->execute([':id' => $id_medico]);
        $totalPacientes = (int) $stmtP->fetch()['total'];
        
        // Citas del mes actual
        $sqlCitasMes = "SELECT COUNT(*) as total FROM Citas 
                        WHERE id_medico = :id AND DATE_FORMAT(fecha_hora, '%Y-%m') = :mes";
        $stmtCM = $db->prepare($sqlCitasMes);
        $stmtCM->execute([':id' => $id_medico, ':mes' => $mes_actual]);
        $citasMes = (int) $stmtCM->fetch()['total'];
        
        // Consultas atendidas del mes
        $sqlAtendidas = "SELECT COUNT(*) as total FROM Consultas con
                         JOIN Citas ci ON con.id_cita = ci.id_cita
                         WHERE ci.id_medico = :id AND DATE_FORMAT(con.fecha_atencion, '%Y-%m') = :mes";
        $stmtA = $db->prepare($sqlAtendidas);
        $stmtA->execute([':id' => $id_medico, ':mes' => $mes_actual]);
        $atendidasMes = (int) $stmtA->fetch()['total'];
        
        // Citas pendientes (solicitadas + confirmadas)
        $sqlPendientes = "SELECT COUNT(*) as total FROM Citas 
                          WHERE id_medico = :id AND estado IN ('solicitada', 'confirmada')";
        $stmtPend = $db->prepare($sqlPendientes);
        $stmtPend->execute([':id' => $id_medico]);
        $pendientes = (int) $stmtPend->fetch()['total'];
        
        // Citas canceladas del mes
        $sqlCanceladas = "SELECT COUNT(*) as total FROM Citas 
                          WHERE id_medico = :id AND estado = 'cancelada' 
                          AND DATE_FORMAT(fecha_hora, '%Y-%m') = :mes";
        $stmtCanc = $db->prepare($sqlCanceladas);
        $stmtCanc->execute([':id' => $id_medico, ':mes' => $mes_actual]);
        $canceladasMes = (int) $stmtCanc->fetch()['total'];
        
        // Próximas 5 citas confirmadas
        $sqlProximas = "SELECT ci.id_cita, ci.fecha_hora, ci.motivo, u.nombre as paciente 
                        FROM Citas ci 
                        JOIN Usuarios u ON ci.id_paciente = u.id_usuario
                        WHERE ci.id_medico = :id AND ci.estado IN ('solicitada','confirmada') 
                        AND ci.fecha_hora >= NOW()
                        ORDER BY ci.fecha_hora ASC LIMIT 5";
        $stmtProx = $db->prepare($sqlProximas);
        $stmtProx->execute([':id' => $id_medico]);
        $proximasCitas = $stmtProx->fetchAll();
        
        // Tendencia de citas creadas por mes
        $sqlTendencia = "SELECT DATE_FORMAT(fecha_hora, '%Y-%m') as mes, COUNT(*) as total
                         FROM Citas WHERE id_medico = :id
                         AND fecha_hora >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                         GROUP BY mes";
        $stmtTend = $db->prepare($sqlTendencia);
        $stmtTend->execute([':id' => $id_medico]);
        $tendenciaData = $stmtTend->fetchAll(PDO::FETCH_ASSOC);
        
        $tendenciaMesAssoc = $ultimos6Meses;
        foreach ($tendenciaData as $row) {
            if (isset($tendenciaMesAssoc[$row['mes']])) {
                $tendenciaMesAssoc[$row['mes']] = (int)$row['total'];
            }
        }
        $tendenciaCitas = [];
        foreach ($tendenciaMesAssoc as $m => $t) {
            $tendenciaCitas[] = ['mes' => $m, 'total' => $t];
        }
        
        // --- MÉTRICAS FINANCIERAS ---
        
        $cobroModel = new Cobro();
        $ingresosMes = $cobroModel->getIngresosMes($id_medico, $mes_actual);
        $ticketPromedio = $cobroModel->getTicketPromedio($id_medico, $mes_actual);
        $ingresosDataRaw = $cobroModel->getIngresosPorMes($id_medico, 6);
        
        $ingresosMesAssoc = $ultimos6Meses;
        foreach ($ingresosDataRaw as $row) {
            if (isset($ingresosMesAssoc[$row['mes']])) {
                $ingresosMesAssoc[$row['mes']] = (float)$row['total'];
            }
        }
        $ingresosPorMes = [];
        foreach ($ingresosMesAssoc as $m => $t) {
            $ingresosPorMes[] = ['mes' => $m, 'total' => $t];
        }
        
        // Tasas
        $tasaCancelacion = $citasMes > 0 ? round(($canceladasMes / $citasMes) * 100, 1) : 0;
        $tasaAtencion = $citasMes > 0 ? round(($atendidasMes / $citasMes) * 100, 1) : 0;
        
        echo json_encode([
            'citas' => $citasEstado,
            'consultas' => $consultasMes,
            'tendencia_citas' => $tendenciaCitas,
            'total_pacientes' => $totalPacientes,
            'citas_mes' => $citasMes,
            'atendidas_mes' => $atendidasMes,
            'pendientes' => $pendientes,
            'canceladas_mes' => $canceladasMes,
            'proximas_citas' => $proximasCitas,
            'ingresos_mes' => $ingresosMes,
            'ticket_promedio' => $ticketPromedio,
            'ingresos_por_mes' => $ingresosPorMes,
            'tasa_cancelacion' => $tasaCancelacion,
            'tasa_atencion' => $tasaAtencion
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
