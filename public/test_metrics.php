<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Models\Cobro;

$db = Database::getInstance()->getConnection();
$id_medico = 1;
$mes_actual = date('Y-m');

// --- MÉTRICAS CLÍNICAS ---
$sqlCitas = "SELECT estado, COUNT(*) as total FROM Citas WHERE id_medico = :id GROUP BY estado";
$stmtCitas = $db->prepare($sqlCitas);
$stmtCitas->execute([':id' => $id_medico]);
$citasEstado = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);

$sqlConsultas = "SELECT DATE_FORMAT(con.fecha_atencion, '%Y-%m') as mes, COUNT(*) as total 
                 FROM Consultas con
                 JOIN Citas ci ON con.id_cita = ci.id_cita
                 WHERE ci.id_medico = :id
                 GROUP BY mes ORDER BY mes DESC LIMIT 6";
$stmtConsultas = $db->prepare($sqlConsultas);
$stmtConsultas->execute([':id' => $id_medico]);
$consultasMes = array_reverse($stmtConsultas->fetchAll(PDO::FETCH_ASSOC));

$sqlPacientes = "SELECT COUNT(DISTINCT id_paciente) as total FROM Citas WHERE id_medico = :id";
$stmtP = $db->prepare($sqlPacientes);
$stmtP->execute([':id' => $id_medico]);
$totalPacientes = (int) $stmtP->fetch()['total'];

$sqlCitasMes = "SELECT COUNT(*) as total FROM Citas 
                WHERE id_medico = :id AND DATE_FORMAT(fecha_hora, '%Y-%m') = :mes";
$stmtCM = $db->prepare($sqlCitasMes);
$stmtCM->execute([':id' => $id_medico, ':mes' => $mes_actual]);
$citasMes = (int) $stmtCM->fetch()['total'];

$sqlAtendidas = "SELECT COUNT(*) as total FROM Consultas con
                 JOIN Citas ci ON con.id_cita = ci.id_cita
                 WHERE ci.id_medico = :id AND DATE_FORMAT(con.fecha_atencion, '%Y-%m') = :mes";
$stmtA = $db->prepare($sqlAtendidas);
$stmtA->execute([':id' => $id_medico, ':mes' => $mes_actual]);
$atendidasMes = (int) $stmtA->fetch()['total'];

$sqlPendientes = "SELECT COUNT(*) as total FROM Citas 
                  WHERE id_medico = :id AND estado IN ('solicitada', 'confirmada')";
$stmtPend = $db->prepare($sqlPendientes);
$stmtPend->execute([':id' => $id_medico]);
$pendientes = (int) $stmtPend->fetch()['total'];

$sqlCanceladas = "SELECT COUNT(*) as total FROM Citas 
                  WHERE id_medico = :id AND estado = 'cancelada' 
                  AND DATE_FORMAT(fecha_hora, '%Y-%m') = :mes";
$stmtCanc = $db->prepare($sqlCanceladas);
$stmtCanc->execute([':id' => $id_medico, ':mes' => $mes_actual]);
$canceladasMes = (int) $stmtCanc->fetch()['total'];

$sqlProximas = "SELECT ci.id_cita, ci.fecha_hora, ci.motivo, u.nombre as paciente 
                FROM Citas ci 
                JOIN Usuarios u ON ci.id_paciente = u.id_usuario
                WHERE ci.id_medico = :id AND ci.estado IN ('solicitada','confirmada') 
                AND ci.fecha_hora >= NOW()
                ORDER BY ci.fecha_hora ASC LIMIT 5";
$stmtProx = $db->prepare($sqlProximas);
$stmtProx->execute([':id' => $id_medico]);
$proximasCitas = $stmtProx->fetchAll(PDO::FETCH_ASSOC);

$sqlTendencia = "SELECT DATE_FORMAT(fecha_hora, '%Y-%m') as mes, COUNT(*) as total
                 FROM Citas WHERE id_medico = :id
                 GROUP BY mes ORDER BY mes DESC LIMIT 6";
$stmtTend = $db->prepare($sqlTendencia);
$stmtTend->execute([':id' => $id_medico]);
$tendenciaCitas = array_reverse($stmtTend->fetchAll(PDO::FETCH_ASSOC));

$cobroModel = new Cobro();
$ingresosMes = $cobroModel->getIngresosMes($id_medico, $mes_actual);
$ticketPromedio = $cobroModel->getTicketPromedio($id_medico, $mes_actual);
$ingresosPorMes = $cobroModel->getIngresosPorMes($id_medico);

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
], JSON_PRETTY_PRINT);
