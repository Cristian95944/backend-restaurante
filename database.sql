-- =============================================
-- BASE DE DATOS: ms-autenticacion
-- =============================================
CREATE DATABASE IF NOT EXISTS db_ms_autenticacion
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE db_ms_autenticacion;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'empleado') NOT NULL DEFAULT 'empleado',
    token VARCHAR(255) NULL,
    sesion_activa TINYINT(1) NOT NULL DEFAULT 0,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Usuario administrador por defecto
INSERT INTO usuarios (nombre, correo, usuario, contrasena, rol) VALUES
('Administrador', 'admin@restaurante.com', 'admin', '123456', 'admin'),
('Empleado Demo', 'empleado@restaurante.com', 'empleado', '123456', 'empleado');

-- =============================================
-- BASE DE DATOS: ms-reservas
-- =============================================
CREATE DATABASE IF NOT EXISTS db_ms_reservas
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE db_ms_reservas;

CREATE TABLE IF NOT EXISTS mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    capacidad INT NOT NULL,
    estado ENUM('disponible', 'reservada', 'ocupada', 'fuera_servicio') NOT NULL DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cliente VARCHAR(100) NOT NULL,
    telefono_cliente VARCHAR(20) NOT NULL,
    cantidad_personas INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    observaciones TEXT NULL,
    estado ENUM('pendiente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendiente',
    mesa_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO mesas (numero, capacidad) VALUES
(1, 2), (2, 4), (3, 4), (4, 6), (5, 8);

-- =============================================
-- BASE DE DATOS: ms-productos
-- =============================================
CREATE DATABASE IF NOT EXISTS db_ms_productos
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE db_ms_productos;

CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    precio DECIMAL(10,2) NOT NULL,
    disponible TINYINT(1) NOT NULL DEFAULT 1,
    categoria_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO categorias (nombre) VALUES
('Entradas'), ('Platos principales'), ('Bebidas'), ('Postres');

INSERT INTO productos (nombre, precio, categoria_id) VALUES
('Ensalada César', 15000, 1),
('Bandeja Paisa', 35000, 2),
('Jugo Natural', 8000, 3),
('Flan de Caramelo', 12000, 4);

-- =============================================
-- BASE DE DATOS: ms-pedidos
-- =============================================
CREATE DATABASE IF NOT