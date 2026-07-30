<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class PlanPago {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function crearPlan(int $id_cobro, float $monto_total, int $no_pagos, string $frecuencia): int {
        try {
            $this->db->beginTransaction();

            // Insertar Plan
            $sqlPlan = "INSERT INTO Planes_Pago (id_cobro, no_pagos, frecuencia) VALUES (:id_cobro, :no_pagos, :frecuencia)";
            $stmtPlan = $this->db->prepare($sqlPlan);
            $stmtPlan->execute([
                ':id_cobro' => $id_cobro,
                ':no_pagos' => $no_pagos,
                ':frecuencia' => $frecuencia
            ]);
            $id_plan = (int) $this->db->lastInsertId();

            // Calcular amortizaciones
            $pago_fijo = round($monto_total / $no_pagos, 2);
            $deuda_actual = $monto_total;

            $sqlAmort = "INSERT INTO Amortizaciones (id_plan, numero_pago, deuda_inicial, monto_pago, adeudo_restante) 
                         VALUES (:id_plan, :np, :di, :mp, :ar)";
            $stmtAmort = $this->db->prepare($sqlAmort);

            for ($i = 1; $i <= $no_pagos; $i++) {
                $pago = $pago_fijo;
                // Ajuste para el último pago para evitar problemas de redondeo
                if ($i === $no_pagos) {
                    $pago = $deuda_actual;
                }
                
                $adeudo = $deuda_actual - $pago;
                
                $stmtAmort->execute([
                    ':id_plan' => $id_plan,
                    ':np' => $i,
                    ':di' => $deuda_actual,
                    ':mp' => $pago,
                    ':ar' => max(0, $adeudo)
                ]);

                $deuda_actual = max(0, $adeudo);
            }

            $this->db->commit();
            return $id_plan;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getPlanYAmortizacionesByCobro(int $id_cobro) {
        $sqlPlan = "SELECT * FROM Planes_Pago WHERE id_cobro = :id_cobro LIMIT 1";
        $stmtPlan = $this->db->prepare($sqlPlan);
        $stmtPlan->execute([':id_cobro' => $id_cobro]);
        $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);

        if (!$plan) return null;

        $sqlAmort = "SELECT * FROM Amortizaciones WHERE id_plan = :id_plan ORDER BY numero_pago ASC";
        $stmtAmort = $this->db->prepare($sqlAmort);
        $stmtAmort->execute([':id_plan' => $plan['id_plan']]);
        $plan['amortizaciones'] = $stmtAmort->fetchAll(PDO::FETCH_ASSOC);

        return $plan;
    }

    public function toggleEstadoAmortizacion(int $id_amortizacion, string $nuevo_estado) {
        $sql = "UPDATE Amortizaciones SET estado = :estado WHERE id_amortizacion = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':estado' => $nuevo_estado,
            ':id' => $id_amortizacion
        ]);
    }
}
