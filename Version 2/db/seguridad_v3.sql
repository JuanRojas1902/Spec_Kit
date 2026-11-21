
-- =====================================================
-- PROYECTO DE INVESTIGACION - VERSION 3
-- SEGURIDAD: USUARIOS, ROLES Y ASIGNACIONES
--
-- QUE ES:
-- Propuesta de tres tablas de seguridad para V3.
--
-- POR QUE:
-- La API necesita autenticar usuarios y consultar roles.
--
-- QUE NO HACE:
-- No modifica las 16 tablas de V1/V2.
-- No crea contrasenas ni usuarios administradores.
-- No implementa por si solo JWT ni permisos.
-- =====================================================

-- Ejecutar con la base investigacion seleccionada.

CREATE TABLE IF NOT EXISTS usuario (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    nombre_completo VARCHAR(200) NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,

    PRIMARY KEY (id),
    UNIQUE KEY uq_usuario_username (username),
    UNIQUE KEY uq_usuario_email (email)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS rol (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(200) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,

    PRIMARY KEY (id),
    UNIQUE KEY uq_rol_nombre (nombre)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS rol_usuario (
    usuario_id INT NOT NULL,
    rol_id INT NOT NULL,

    PRIMARY KEY (usuario_id, rol_id),

    CONSTRAINT fk_rol_usuario_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuario(id)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT,

    CONSTRAINT fk_rol_usuario_rol
        FOREIGN KEY (rol_id)
        REFERENCES rol(id)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- Roles iniciales: no suponemos IDs fijos.

INSERT INTO rol (nombre, descripcion)
VALUES
    ('admin', 'Administracion del sistema'),
    ('consulta', 'Consulta de informacion')
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre);
