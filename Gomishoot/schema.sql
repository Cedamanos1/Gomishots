-- GOMISHOTS 2.0 - Esquema PostgreSQL para Neon
-- Compatible con PostgreSQL 12+

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol VARCHAR(20) DEFAULT 'operador',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de productos
CREATE TABLE IF NOT EXISTS productos (
    id_producto SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de ingresos
CREATE TABLE IF NOT EXISTS ingresos (
    id_ingreso SERIAL PRIMARY KEY,
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
    id_salida SERIAL PRIMARY KEY,
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
    id_modificacion SERIAL PRIMARY KEY,
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
    id_log SERIAL PRIMARY KEY,
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
ON CONFLICT (codigo) DO NOTHING;

-- Insertar usuario de prueba (contraseña: admin123)
-- Hash generado con: password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO usuarios (username, password_hash, rol) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador')
ON CONFLICT (username) DO NOTHING;

-- Índices para mejorar el rendimiento
CREATE INDEX IF NOT EXISTS idx_ingresos_codigo ON ingresos(codigo);
CREATE INDEX IF NOT EXISTS idx_ingresos_fecha ON ingresos(fecha);
CREATE INDEX IF NOT EXISTS idx_salidas_codigo ON salidas(codigo);
CREATE INDEX IF NOT EXISTS idx_salidas_fecha ON salidas(fecha);
CREATE INDEX IF NOT EXISTS idx_logs_fecha ON logs_sistema(fecha_evento);

-- Comentarios en las tablas (opcional, para documentación)
COMMENT ON TABLE usuarios IS 'Usuarios del sistema con autenticación';
COMMENT ON TABLE productos IS 'Catálogo de productos disponibles';
COMMENT ON TABLE ingresos IS 'Registro de ingresos de productos al almacén';
COMMENT ON TABLE salidas IS 'Registro de salidas de productos del almacén';
COMMENT ON TABLE modificaciones IS 'Auditoría de modificaciones de ingresos';
COMMENT ON TABLE logs_sistema IS 'Logs de eventos del sistema';
