<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Cobro {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crear(array $datos): int {
        $sql = "INSERT INTO Cobros (id_consulta, monto, metodo_pago, notas) 
                VALUES (:id_consulta, :monto, :metodo_pago, :notas)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_consulta' => $datos['id_consulta'],
            ':monto' => $datos['monto'],
            ':metodo_pago' => $datos['metodo_pago'] ?? 'efectivo',
            ':notas' => $datos['notas'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getByConsulta(int $id_consulta) {
        $sql = "SELECT * FROM Cobros WHERE id_consulta = :id_consulta LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_consulta', $id_consulta, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getIngresosMes(int $id_medico, string $mes) {
        $sql = "SELECT COALESCE(SUM(co.monto), 0) as total
                FROM Cobros co
                JOIN Consultas con ON co.id_consulta = con.id_consulta
                JOIN Citas ci ON con.id_cita = ci.id_cita
                WHERE ci.id_medico = :id_medico
                AND DATE_FORMAT(co.fecha_cobro, '%Y-%m') = :mes";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_medico' => $id_medico, ':mes' => $mes]);
        $row = $stmt->fetch();
        return $row ? (float) $row['total'] : 0;
    }

    public function getIngresosPorMes(int $id_medico, int $limit = 6) {
        $sql = "SELECT DATE_FORMAT(co.fecha_cobro, '%Y-%m') as mes, SUM(co.monto) as total
                FROM Cobros co
                JOIN Consultas con ON co.id_consulta = con.id_consulta
                JOIN Citas ci ON con.id_cita = ci.id_cita
                WHERE ci.id_medico = :id_medico
                GROUP BY mes ORDER BY mes DESC LIMIT :lim";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_medico', $id_medico, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return array_reverse($stmt->fetchAll());
    }

    public function getTicketPromedio(int $id_medico, string $mes) {
        $sql = "SELECT COALESCE(AVG(co.monto), 0) as promedio
                FROM Cobros co
                JOIN Consultas con ON co.id_consulta = con.id_consulta
                JOIN Citas ci ON con.id_cita = ci.id_cita
                WHERE ci.id_medico = :id_medico
                AND DATE_FORMAT(co.fecha_cobro, '%Y-%m') = :mes";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_medico' => $id_medico, ':mes' => $mes]);
        $row = $stmt->fetch();
        return $row ? round((float) $row['promedio'], 2) : 0;
    }
}
