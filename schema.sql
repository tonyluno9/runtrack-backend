CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sesiones_entrenamiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo_carrera VARCHAR(50) NOT NULL,
    fecha_hora_inicio DATETIME NOT NULL,
    fecha_hora_fin DATETIME NOT NULL,
    distancia_total_km FLOAT NOT NULL,
    duracion_total_segundos INT NOT NULL,
    ritmo_promedio_min_km FLOAT NOT NULL,
    calorias_estimadas INT NOT NULL,
    ruta_foto_local VARCHAR(255) NULL,
    esta_sincronizado BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS puntos_gps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_sesion INT NOT NULL,
    latitud DOUBLE NOT NULL,
    longitud DOUBLE NOT NULL,
    altitud DOUBLE NOT NULL,
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (id_sesion) REFERENCES sesiones_entrenamiento(id) ON DELETE CASCADE
);
