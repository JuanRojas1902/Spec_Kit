-- ==========================================================
-- ARCHIVO: 01_init.sql
-- PROYECTO: Sistema de Investigacion
-- VERSION: 1
--
-- QUE ES:
-- Script de inicializacion de la base de datos.
--
-- POR QUE ESTA AQUI:
-- Crea los seis catalogos originales de la version 1.
--
-- QUE NO HACE:
-- No crea las tablas nuevas de la version 2.
-- No administra procedimientos almacenados ni sesiones.
-- ==========================================================

CREATE DATABASE IF NOT EXISTS investigacion;

USE investigacion;


-- ==========================================================
-- 1. AREA DE CONOCIMIENTO
-- ==========================================================

CREATE TABLE IF NOT EXISTS area_conocimiento (
    id INT NOT NULL,
    gran_area VARCHAR(60) NOT NULL,
    area VARCHAR(60) NOT NULL,
    disciplina VARCHAR(60) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);


-- ==========================================================
-- 2. OBJETIVO DE DESARROLLO SOSTENIBLE
-- ==========================================================

CREATE TABLE IF NOT EXISTS objetivo_desarrollo_sostenible (
    id INT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    categoria VARCHAR(45) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);


-- ==========================================================
-- 3. AREA DE APLICACION
-- ==========================================================

CREATE TABLE IF NOT EXISTS area_aplicacion (
    id INT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);


-- ==========================================================
-- 4. TERMINO CLAVE
-- Su llave primaria es un texto, no un numero.
-- ==========================================================

CREATE TABLE IF NOT EXISTS termino_clave (
    termino VARCHAR(30) NOT NULL,
    termino_ingles VARCHAR(30) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (termino)
);


-- ==========================================================
-- 5. UNIVERSIDAD
-- Sera utilizada en los desplegables de los grupos.
-- ==========================================================

CREATE TABLE IF NOT EXISTS universidad (
    id INT NOT NULL,
    nombre VARCHAR(60) NOT NULL,
    tipo VARCHAR(45) NOT NULL,
    ciudad VARCHAR(45) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);


-- ==========================================================
-- 6. LINEA DE INVESTIGACION
-- Su identificador se genera automaticamente.
-- ==========================================================

CREATE TABLE IF NOT EXISTS linea_investigacion (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(45) NOT NULL,
    descripcion VARCHAR(256) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
);


-- ==========================================================
-- 7. DATOS INICIALES DE LA VERSION 1
-- ==========================================================

INSERT IGNORE INTO area_conocimiento
(id, gran_area, area, disciplina)
VALUES
(1, 'Ingeniería y Tecnología', 'Ingeniería de Sistemas',
 'Ingeniería de software'),
(2, 'Ciencias Naturales', 'Matemáticas', 'Álgebra');


INSERT IGNORE INTO objetivo_desarrollo_sostenible
(id, nombre, categoria)
VALUES
(1, 'Fin de la pobreza', 'Social'),
(4, 'Educación de calidad', 'Social');


INSERT IGNORE INTO area_aplicacion
(id, nombre)
VALUES
(1, 'Salud pública'),
(2, 'Inteligencia Artificial');


INSERT IGNORE INTO termino_clave
(termino, termino_ingles)
VALUES
('Algoritmo', 'Algorithm'),
('Computación cuántica', 'Quantum computing');


INSERT IGNORE INTO universidad
(id, nombre, tipo, ciudad)
VALUES
(1, 'Universidad Monte Verde', 'Privada', 'Medellín'),
(2, 'Universidad Nacional', 'Pública', 'Bogotá');


INSERT IGNORE INTO linea_investigacion
(nombre, descripcion)
VALUES
('Desarrollo de Software',
 'Línea enfocada en metodologías ágiles.'),
('Ciberseguridad',
 'Protección de datos y redes.');


-- ==========================================================
-- FIN DEL SCRIPT DE LA VERSION 1
-- ==========================================================