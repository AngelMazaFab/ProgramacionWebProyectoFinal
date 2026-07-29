<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Usuario {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByFirebaseUid(string $uid) {
        $sql = "SELECT * FROM Usuarios WHERE firebase_uid = :uid LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $uid, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create(array $data): int {
        $sql = "INSERT INTO Usuarios (nombre, correo, telefono, rol, firebase_uid) 
                VALUES (:nombre, :correo, :telefono, :rol, :firebase_uid)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':correo' => $data['correo'],
            ':telefono' => $data['telefono'] ?? null,
            ':rol' => $data['rol'] ?? 'paciente',
            ':firebase_uid' => $data['firebase_uid']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getAllPacientes() {
        $sql = "SELECT id_usuario, nombre, correo, telefono, fecha_registro FROM Usuarios WHERE rol = 'paciente' ORDER BY nombre ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getMedicos() {
        $sql = "SELECT id_usuario, nombre, correo FROM Usuarios WHERE rol = 'medico' ORDER BY nombre ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getById(int $id_usuario) {
        $sql = "SELECT * FROM Usuarios WHERE id_usuario = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
}
