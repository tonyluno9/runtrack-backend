<?php

class SesionController {
    private $db;
    private $sesionModel;

    public function __construct($db) {
        $this->db = $db;
        $this->sesionModel = new Sesion($db);
    }

    // Nota: Le llamé 'crear' en lugar de 'guardar' para que coincida exactamente con el index.php que hicimos
    public function crear() {
        $data = json_decode(file_get_contents('php://input'), true);

        $camposRequeridos = [
            'id_usuario', 'tipo_carrera', 'fecha_hora_inicio', 'fecha_hora_fin', 
            'distancia_total_km', 'duracion_total_segundos', 'ritmo_promedio_min_km', 
            'calorias_estimadas', 'coordenadas'
        ];

        // Validar que existan todos los campos
        foreach ($camposRequeridos as $campo) {
            if (!isset($data[$campo])) {
                http_response_code(400); // Bad Request
                echo json_encode([
                    'success' => false,
                    'message' => "Falta el campo requerido: $campo"
                ]);
                return;
            }
        }

        // Validar que coordenadas sea un array no vacío
        if (!is_array($data['coordenadas']) || count($data['coordenadas']) === 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "El arreglo de coordenadas está vacío o es inválido"
            ]);
            return;
        }

        // Llamar al modelo para guardar
        $resultado = $this->sesionModel->guardarSesion($data);

        if ($resultado['success']) {
            http_response_code(201); // Created
            echo json_encode($resultado);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode($resultado);
        }
    }

    // Nota: Le llamé 'listar' en lugar de 'getPorUsuario' por la misma razón (compatibilidad con index.php)
    public function listar($id_usuario) {
        // Validar que sea numérico
        if (!is_numeric($id_usuario)) {
            http_response_code(400); // Bad Request
            echo json_encode([
                'success' => false,
                'message' => 'El ID de usuario debe ser numérico'
            ]);
            return;
        }

        // Llamar al modelo
        $sesiones = $this->sesionModel->getSesionesPorUsuario((int)$id_usuario);

        // Responder con HTTP 200 y el JSON del arreglo directamente
        http_response_code(200);
        echo json_encode($sesiones);
    }
}
