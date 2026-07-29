<?php
namespace App\Controllers;

use App\Models\Receta;
use App\Middleware\AuthMiddleware;

class RecetaController {
    public function __construct() {
        AuthMiddleware::requireRol('medico');
    }

    public function store() {
        $datos = [
            'id_consulta' => (int) $_POST['id_consulta'],
            'medicamento' => htmlspecialchars($_POST['medicamento']),
            'dosis' => htmlspecialchars($_POST['dosis']),
            'indicaciones' => htmlspecialchars($_POST['indicaciones'] ?? '')
        ];

        $recetaModel = new Receta();
        $recetaModel->crear($datos);

        $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/consultas/atender?id_cita=' . (int)$_POST['id_cita'] . '&msg=Medicamento agregado');
    }

    public function delete() {
        $id_receta = (int) $_POST['id_receta'];
        $id_cita = (int) $_POST['id_cita'];

        $recetaModel = new Receta();
        $recetaModel->eliminar($id_receta);

        $bU = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"])); if($bU === "/") $bU = ""; header('Location: ' . $bU . '/consultas/atender?id_cita=' . $id_cita . '&msg=Medicamento eliminado');
    }
}
