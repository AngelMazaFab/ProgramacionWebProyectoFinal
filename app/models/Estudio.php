<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Estudio {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByCita(int $id_cita) {
        $sql = "SELECT * FROM Entregables_Estudios WHERE id_cita = :id_cita ORDER BY fecha_subida DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_cita', $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById(int $id_estudio) {
        $sql = "SELECT * FROM Entregables_Estudios WHERE id_estudio = :id_estudio LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_estudio', $id_estudio, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function crear(array $datos): int {
        $sql = "INSERT INTO Entregables_Estudios (id_paciente, id_cita, nombre_archivo, ruta_archivo, tipo_archivo) 
                VALUES (:id_paciente, :id_cita, :nombre_archivo, :ruta_archivo, :tipo_archivo)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_paciente' => $datos['id_paciente'],
            ':id_cita' => $datos['id_cita'],
            ':nombre_archivo' => $datos['nombre_archivo'],
            ':ruta_archivo' => $datos['ruta_archivo'],
            ':tipo_archivo' => $datos['tipo_archivo']
        ]);
        return (int) $this->db->lastInsertId();
    }
}
