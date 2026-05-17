<?php

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registro($nombre, $email, $password) {
        // Verificar si el email ya existe
        $query_check = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':email', $email);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'Email ya registrado'
            ];
        }

        // Insertar el nuevo usuario
        $query = "INSERT INTO " . $this->table_name . " (nombre, email, password_hash) VALUES (:nombre, :email, :password_hash)";
        $stmt = $this->conn->prepare($query);

        // Hashear el password con BCRYPT
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Limpiar strings básicos (opcional pero buena práctica)
        $nombre = htmlspecialchars(strip_tags($nombre));
        $email = htmlspecialchars(strip_tags($email));

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $password_hash);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'id_usuario' => $this->conn->lastInsertId()
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al registrar el usuario'
        ];
    }

    public function login($email, $password) {
        $query = "SELECT id, nombre, password_hash FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar con password_verify
            if (password_verify($password, $row['password_hash'])) {
                return [
                    'success' => true,
                    'id_usuario' => $row['id'],
                    'nombre' => $row['nombre']
                ];
            }
        }

        // Si no existe el email o el password es incorrecto
        return [
            'success' => false,
            'message' => 'Credenciales inválidas'
        ];
    }
}
