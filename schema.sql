-- GOMISHOTS 2.0 - Esquema de Base de Datos
-- Compatible con MySQL y PostgreSQL

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol VARCHAR(20) DEFAULT 'operador',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de productos
CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de ingresos
CREATE TABLE IF NOT EXISTS ingresos (
    id_ingreso INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    fecha DATE NOT NULL,
    id_usuario INT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- Tabla de salidas
CREATE TABLE IF NOT EXISTS salidas (
    id_salida INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    fecha DATE NOT NULL,
    id_usuario INT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- Tabla de modificaciones (auditoría)
CREATE TABLE IF NOT EXISTS modificaciones (
    id_modificacion INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    cantidad_anterior INT NOT NULL,
    cantidad_nueva INT NOT NULL,
    justificacion TEXT,
    id_usuario INT,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- Tabla de logs del sistema
CREATE TABLE IF NOT EXISTS logs_sistema (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    evento VARCHAR(100) NOT NULL,
    detalle TEXT,
    fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
);

-- Insertar productos predefinidos
INSERT INTO productos (codigo, nombre) VALUES
('JAG001', 'Jagger'),
('WHS001', 'Whisky Sour'),
('ALG001', 'Algarrobina'),
('APD001', 'Apple Drunk'),
('CUB001', 'Cuba Libre')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Insertar usuario de prueba (contraseña: admin123)
-- Hash generado con: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO usuarios (username, password_hash, rol) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- Índices para mejorar el rendimiento
CREATE INDEX idx_ingresos_codigo ON ingresos(codigo);
CREATE INDEX idx_ingresos_fecha ON ingresos(fecha);
CREATE INDEX idx_salidas_codigo ON salidas(codigo);
CREATE INDEX idx_salidas_fecha ON salidas(fecha);
CREATE INDEX idx_logs_fecha ON logs_sistema(fecha_evento);
