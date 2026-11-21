-- ============================================================================
-- FASE 0: INICIALIZACIÓN DE LA BASE DE DATOS (Módulo de Investigación)
-- Qué hace: Crea las 6 tablas iniciales exigidas para la v1 y añade el borrado lógico.
-- Regla innegociable: Todas las tablas llevan el campo `activo` TINYINT(1) DEFAULT 1.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS investigacion;
USE investigacion;

-- 1. Tabla: area_conocimiento
-- Cambio según spec: id vuelve a ser INT, no VARCHAR[cite: 15].
CREATE TABLE IF NOT EXISTS area_conocimiento (
    id INT NOT NULL,
    gran_area VARCHAR(60) NOT NULL,
    area VARCHAR(60) NOT NULL,
    disciplina VARCHAR(60) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);

-- 2. Tabla: objetivo_desarrollo_sostenible
CREATE TABLE IF NOT EXISTS objetivo_desarrollo_sostenible (
    id INT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    categoria VARCHAR(45) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);

-- 3. Tabla: area_aplicacion
CREATE TABLE IF NOT EXISTS area_aplicacion (
    id INT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);

-- 4. Tabla: termino_clave
-- Nota especial: Esta tabla NO tiene 'id'. Su llave primaria es el texto[cite: 15].
CREATE TABLE IF NOT EXISTS termino_clave (
    termino VARCHAR(30) NOT NULL,
    termino_ingles VARCHAR(30) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (termino)
);

-- 5. Tabla: universidad
CREATE TABLE IF NOT EXISTS universidad (
    id INT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    tipo VARCHAR(45) NOT NULL,
    ciudad VARCHAR(45) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);

-- 6. Tabla: linea_investigacion
-- Nota especial: Es la única tabla de la v1 que usa AUTO_INCREMENT en su id[cite: 15].
CREATE TABLE IF NOT EXISTS linea_investigacion (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(45) NOT NULL,
    descripcion VARCHAR(256) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);

-- ============================================================================
-- SEMILLAS (Datos de ejemplo iniciales para que el Front no arranque vacío)
-- ============================================================================

INSERT IGNORE INTO area_conocimiento (id, gran_area, area, disciplina) VALUES 
(1, 'Ingeniería y Tecnología', 'Ingeniería de Sistemas', 'Ingeniería de software'),
(2, 'Ciencias Naturales', 'Matemáticas', 'Álgebra');

INSERT IGNORE INTO objetivo_desarrollo_sostenible (id, nombre, categoria) VALUES 
(1, 'Fin de la pobreza', 'Social'),
(4, 'Educación de calidad', 'Social');

INSERT IGNORE INTO area_aplicacion (id, nombre) VALUES 
(1, 'Salud pública'),
(2, 'Inteligencia Artificial');

INSERT IGNORE INTO termino_clave (termino, termino_ingles) VALUES 
('Algoritmo', 'Algorithm'),
('Computación cuántica', 'Quantum computing');

INSERT IGNORE INTO universidad (id, nombre, tipo, ciudad) VALUES 
(1, 'Universidad Monte Verde', 'Privada', 'Medellín'),
(2, 'Universidad Nacional', 'Pública', 'Bogotá');

INSERT IGNORE INTO linea_investigacion (nombre, descripcion) VALUES 
('Desarrollo de Software', 'Línea enfocada en metodologías ágiles.'),
('Ciberseguridad', 'Protección de datos y redes.');