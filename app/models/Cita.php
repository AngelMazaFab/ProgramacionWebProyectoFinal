<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Cita {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crear(array $datos): int {
        $sql = 'INSERT INTO Citas (id_paciente, id_medico, fecha_hora, motivo, estado)
                VALUES (:id_paciente, :id_medico, :fecha_hora, :motivo, :estado)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_paciente' => $datos['id_paciente'],
            ':id_medico'   => $datos['id_medico'],
            ':fecha_hora'  => $datos['fecha_hora'],
            ':motivo'      => $datos['motivo'] ?? null,
            ':estado'      => 'solicitada'
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getByPaciente(int $id_paciente) {
        $sql = "SELECT c.*, u.nombre as medico_nombre 
                FROM Citas c
                JOIN Usuarios u ON c.id_medico = u.id_usuario
                WHERE c.id_paciente = :id_paciente
                ORDER BY c.fecha_hora DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_paciente', $id_paciente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByMedico(int $id_medico) {
        $sql = "SELECT c.*, u.nombre as paciente_nombre 
                FROM Citas c
                JOIN Usuarios u ON c.id_paciente = u.id_usuario
                WHERE c.id_medico = :id_medico
                ORDER BY c.fecha_hora DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_medico', $id_medico, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id_cita) {
        $sql = "SELECT * FROM Citas WHERE id_cita = :id_cita LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_cita', $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateEstado(int $id_cita, string $estado): bool {
        $sql = "UPDATE Citas SET estado = :estado WHERE id_cita = :id_cita";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':estado'  => $estado,
            ':id_cita' => $id_cita
        ]);
    }

    public function reprogramar(int $id_cita, string $fecha_hora): bool {
        $sql = "UPDATE Citas SET fecha_hora = :fecha_hora WHERE id_cita = :id_cita";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':fecha_hora' => $fecha_hora,
            ':id_cita'    => $id_cita
        ]);
    }
}
