<?php

// Headers CORS para permitir cualquier origen
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Manejo de preflight request (OPTIONS) para clientes como Android/Browsers
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Inclusión de archivos necesarios
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Usuario.php';
require_once __DIR__ . '/models/Sesion.php';
require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/SesionController.php';

// Obtener el método y la URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    // Instanciar la base de datos y obtener la conexión
    $database = new Database();
    $db = $database->getConnection();
    
    // Instanciar los controladores
    $usuarioController = new UsuarioController($db);
    $sesionController = new SesionController($db);

    // Sistema de Enrutamiento Básico
    if ($method === 'POST' && $uri === '/api/usuario/registro') {
        $usuarioController->registro();
    } 
    elseif ($method === 'POST' && $uri === '/api/usuario/login') {
        $usuarioController->login();
    } 
    elseif ($method === 'POST' && $uri === '/api/sesion') {
        $sesionController->crear();
    } 
    // Expresión regular para capturar el id_usuario en /api/sesiones/{id}
    elseif ($method === 'GET' && preg_match('/^\/api\/sesiones\/(\d+)$/', $uri, $matches)) {
        $id_usuario = (int)$matches[1];
        $sesionController->listar($id_usuario);
    } 
    else {
        // Respuesta 404 para rutas no definidas
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Ruta o método no encontrado"
        ]);
    }

} catch (Exception $e) {
    // Manejo global de errores (500)
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error interno del servidor",
        "error" => $e->getMessage() // Útil para debugging, se puede ocultar en prod
    ]);
}
