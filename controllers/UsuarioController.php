<?php

class UsuarioController {
    private $db;
    private $usuarioModel;

    // Utilizo el constructor en lugar de métodos estáticos para mantener compatibilidad 
    // con la estructura que ya definimos en index.php, donde instanciamos el controlador.
    public function __construct($db) {
        $this->db = $db;
        $this->usuarioModel = new Usuario($db);
    }

    public function registro() {
        // Leer el body JSON
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar campos requeridos
        if (!isset($data['nombre']) || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400); // Bad Request
            echo json_encode([
                'success' => false,
                'message' => 'Campos incompletos'
            ]);
            return;
        }

        // Validar formato de email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Formato de email inválido'
            ]);
            return;
        }

        // Llamar al modelo
        $resultado = $this->usuarioModel->registro(
            trim($data['nombre']), 
            trim($data['email']), 
            $data['password']
        );

        if ($resultado['success']) {
            http_response_code(201); // Created
            echo json_encode($resultado);
        } else {
            http_response_code(409); // Conflict (Email duplicado)
            echo json_encode($resultado);
        }
    }

    public function login() {
        // Leer el body JSON
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar campos requeridos
        if (!isset($data['email']) || !isset($data['password'])) {
            http_response_code(400); // Bad Request
            echo json_encode([
                'success' => false,
                'message' => 'Campos incompletos'
            ]);
            return;
        }

        // Llamar al modelo
        $resultado = $this->usuarioModel->login(
            trim($data['email']), 
            $data['password']
        );

        if ($resultado['success']) {
            http_response_code(200); // OK
            echo json_encode($resultado);
        } else {
            http_response_code(401); // Unauthorized
            echo json_encode($resultado);
        }
    }
}
