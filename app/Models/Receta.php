<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Receta {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByConsulta(int $id_consulta) {
        $sql = "SELECT * FROM Recetas WHERE id_consulta = :id_consulta ORDER BY id_receta ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_consulta', $id_consulta, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function crear(array $datos): int {
        $sql = "INSERT INTO Recetas (id_consulta, medicamento, dosis, indicaciones) 
                VALUES (:id_consulta, :medicamento, :dosis, :indicaciones)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_consulta' => $datos['id_consulta'],
            ':medicamento' => $datos['medicamento'],
            ':dosis' => $datos['dosis'],
            ':indicaciones' => $datos['indicaciones'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function eliminar(int $id_receta): bool {
        $sql = "DELETE FROM Recetas WHERE id_receta = :id_receta";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id_receta' => $id_receta]);
    }
}
