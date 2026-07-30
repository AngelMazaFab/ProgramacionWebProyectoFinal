<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Consulta {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByCita(int $id_cita) {
        $sql = "SELECT * FROM Consultas WHERE id_cita = :id_cita LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_cita', $id_cita, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getById(int $id_consulta) {
        $sql = "SELECT * FROM Consultas WHERE id_consulta = :id_consulta LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_consulta', $id_consulta, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateCanvas(int $id_consulta, string $ruta_archivo): bool {
        $sql = "UPDATE Consultas SET anotaciones_canvas = :ruta WHERE id_consulta = :id_consulta";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':ruta' => $ruta_archivo,
            ':id_consulta' => $id_consulta
        ]);
    }

    public function crear(array $datos): int {
        $sql = "INSERT INTO Consultas (id_cita, diagnostico, tratamiento) 
                VALUES (:id_cita, :diagnostico, :tratamiento)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_cita' => $datos['id_cita'],
            ':diagnostico' => $datos['diagnostico'],
            ':tratamiento' => $datos['tratamiento'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }
}
