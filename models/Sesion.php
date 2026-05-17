<?php

class Sesion {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function guardarSesion($datos) {
        try {
            // Iniciar transacción
            $this->conn->beginTransaction();

            // Preparar inserción de la sesión
            $query_sesion = "INSERT INTO sesiones_entrenamiento 
                (id_usuario, tipo_carrera, fecha_hora_inicio, fecha_hora_fin, distancia_total_km, duracion_total_segundos, ritmo_promedio_min_km, calorias_estimadas, esta_sincronizado) 
                VALUES 
                (:id_usuario, :tipo_carrera, :fecha_hora_inicio, :fecha_hora_fin, :distancia_total_km, :duracion_total_segundos, :ritmo_promedio_min_km, :calorias_estimadas, :esta_sincronizado)";

            $stmt_sesion = $this->conn->prepare($query_sesion);

            $esta_sincronizado = 1; // True porque ya llegó al backend

            $stmt_sesion->bindParam(':id_usuario', $datos['id_usuario']);
            $stmt_sesion->bindParam(':tipo_carrera', $datos['tipo_carrera']);
            $stmt_sesion->bindParam(':fecha_hora_inicio', $datos['fecha_hora_inicio']);
            $stmt_sesion->bindParam(':fecha_hora_fin', $datos['fecha_hora_fin']);
            $stmt_sesion->bindParam(':distancia_total_km', $datos['distancia_total_km']);
            $stmt_sesion->bindParam(':duracion_total_segundos', $datos['duracion_total_segundos']);
            $stmt_sesion->bindParam(':ritmo_promedio_min_km', $datos['ritmo_promedio_min_km']);
            $stmt_sesion->bindParam(':calorias_estimadas', $datos['calorias_estimadas']);
            $stmt_sesion->bindParam(':esta_sincronizado', $esta_sincronizado, PDO::PARAM_INT);

            $stmt_sesion->execute();
            
            // Obtener el ID de la sesión recién insertada
            $id_sesion = $this->conn->lastInsertId();

            // Preparar e insertar cada punto GPS
            if (isset($datos['coordenadas']) && is_array($datos['coordenadas'])) {
                $query_gps = "INSERT INTO puntos_gps (id_sesion, latitud, longitud, altitud, timestamp) 
                              VALUES (:id_sesion, :latitud, :longitud, :altitud, :timestamp)";
                $stmt_gps = $this->conn->prepare($query_gps);

                foreach ($datos['coordenadas'] as $punto) {
                    $stmt_gps->bindParam(':id_sesion', $id_sesion);
                    $stmt_gps->bindParam(':latitud', $punto['latitud']);
                    $stmt_gps->bindParam(':longitud', $punto['longitud']);
                    $stmt_gps->bindParam(':altitud', $punto['altitud']);
                    $stmt_gps->bindParam(':timestamp', $punto['timestamp']);
                    $stmt_gps->execute();
                }
            }

            // Confirmar transacción
            $this->conn->commit();

            return [
                'success' => true,
                'id_sesion' => $id_sesion
            ];

        } catch (Exception $e) {
            // Deshacer cambios si ocurre un error
            $this->conn->rollBack();
            return [
                'success' => false,
                'message' => 'Error al guardar la sesión: ' . $e->getMessage()
            ];
        }
    }

    public function getSesionesPorUsuario($id_usuario) {
        $query = "SELECT * FROM sesiones_entrenamiento WHERE id_usuario = :id_usuario ORDER BY fecha_hora_inicio DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
