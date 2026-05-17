<?php

class Database {
    private $conn;

    public function getConnection() {
        $this->conn = null;

        // Lectura de variables de entorno (Railway o locales fallback)
        $host = $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST') ?? 'localhost';
        $port = $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? '3306';
        $db_name = $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?? 'runtrack';
        $username = $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER') ?? 'root';
        $password = $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?? '';

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$db_name};charset=utf8mb4";
            
            // Opciones de PDO (Excepciones activadas, UTF-8 y arrays asociativos)
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            $this->conn = new PDO($dsn, $username, $password, $options);
            
        } catch (PDOException $exception) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => "Error de conexión a la base de datos: " . $exception->getMessage()
            ]);
            exit;
        }

        return $this->conn;
    }
}
