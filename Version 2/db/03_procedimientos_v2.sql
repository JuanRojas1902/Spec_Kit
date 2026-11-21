-- ================================================================
-- ARCHIVO: procedimientos_v2.sql
-- PROYECTO: Sistema de Gestion de Investigacion
-- VERSION: 2
--
-- QUE ES:
-- Script que amplía la base de datos de la version 1.
--
-- POR QUE ESTA AQUI:
-- Permite crear las relaciones entre las tablas y administrar
-- grupos de investigacion con sus semilleros mediante transacciones.
--
-- QUE NO HACE:
-- No administra pantallas, sesiones ni peticiones HTTP.
-- No reemplaza las seis tablas originales de la version 1.
--
-- CAMBIOS AL ESQUEMA (Articulo 5):
-- C1: Columna activo en docente, grupo_investigacion y semillero.
-- C2: Las siete tablas puente no tienen columna activo.
-- C3: No se utilizan disparadores ni contadores automaticos.
-- ================================================================

USE investigacion;


-- ================================================================
-- 1. RETIRAR ELEMENTOS DEL SCRIPT ANTERIOR QUE NO PIDE LA V2
-- ================================================================

DROP TRIGGER IF EXISTS trg_grupo_ai;
DROP TRIGGER IF EXISTS trg_grupo_au;
DROP TRIGGER IF EXISTS trg_grupo_ad;

DROP PROCEDURE IF EXISTS sp_recontar_grupos;

ALTER TABLE universidad
DROP COLUMN IF EXISTS total_grupos;


-- ================================================================
-- 2. TABLA DOCENTE
-- Almacena los datos de los docentes investigadores.
-- La cedula es su llave primaria.
-- ================================================================

CREATE TABLE IF NOT EXISTS docente (
    cedula INT NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,

    PRIMARY KEY (cedula)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ================================================================
-- 3. TABLA GRUPO DE INVESTIGACION
-- Es el maestro de la relacion maestro-detalle.
-- Cada grupo pertenece a una universidad.
-- ================================================================

CREATE TABLE IF NOT EXISTS grupo_investigacion (
    id INT NOT NULL AUTO_INCREMENT,
    gruplac VARCHAR(255) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    convocatoria VARCHAR(255) NOT NULL,
    ambito VARCHAR(255) NOT NULL,
    universidad_id INT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,

    PRIMARY KEY (id),

    CONSTRAINT fk_grupo_universidad
        FOREIGN KEY (universidad_id)
        REFERENCES universidad(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ================================================================
-- 4. TABLA SEMILLERO
-- Es el detalle de un grupo de investigacion.
-- Un semillero no puede existir sin su grupo.
-- ================================================================

CREATE TABLE IF NOT EXISTS semillero (
    id INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    grupo_id INT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,

    PRIMARY KEY (id),

    CONSTRAINT fk_semillero_grupo
        FOREIGN KEY (grupo_id)
        REFERENCES grupo_investigacion(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ================================================================
-- 5. TABLA PUENTE: PARTICIPA_GRUPO
-- Relaciona docentes con grupos de investigacion.
-- Su llave primaria esta formada por ambas llaves foraneas.
-- ================================================================

CREATE TABLE IF NOT EXISTS participa_grupo (
    docente_cedula INT NOT NULL,
    grupo_id INT NOT NULL,

    PRIMARY KEY (docente_cedula, grupo_id),

    CONSTRAINT fk_pg_docente
        FOREIGN KEY (docente_cedula)
        REFERENCES docente(cedula),

    CONSTRAINT fk_pg_grupo
        FOREIGN KEY (grupo_id)
        REFERENCES grupo_investigacion(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ================================================================
-- 6. TABLA PUENTE: PARTICIPA_SEMILLERO
-- Relaciona docentes con semilleros.
-- ================================================================

CREATE TABLE IF NOT EXISTS participa_semillero (
    docente_cedula INT NOT NULL,
    semillero_id INT NOT NULL,

    PRIMARY KEY (docente_cedula, semillero_id),

    CONSTRAINT fk_ps_docente
        FOREIGN KEY (docente_cedula)
        REFERENCES docente(cedula),

    CONSTRAINT fk_ps_semillero
        FOREIGN KEY (semillero_id)
        REFERENCES semillero(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ================================================================
-- 7. TABLA PUENTE: GRUPO_LINEA
-- Relaciona grupos con lineas de investigacion.
-- ================================================================

CREATE TABLE IF NOT EXISTS grupo_linea (
    grupo_id INT NOT NULL,
    linea_id INT NOT NULL,

    PRIMARY KEY (grupo_id, linea_id),

    CONSTRAINT fk_gl_grupo
        FOREIGN KEY (grupo_id)
        REFERENCES grupo_investigacion(id),

    CONSTRAINT fk_gl_linea
        FOREIGN KEY (linea_id)
        REFERENCES linea_investigacion(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ================================================================
-- 8. TABLA PUENTE: SEMILLERO_LINEA
-- Relaciona semilleros con lineas de investigacion.
-- ================================================================

CREATE TABLE IF NOT EXISTS semillero_linea (
    semillero_id INT NOT NULL,
    linea_id INT NOT NULL,

    PRIMARY KEY (semillero_id, linea_id),

    CONSTRAINT fk_sl_semillero
        FOREIGN KEY (semillero_id)
        REFERENCES semillero(id),

    CONSTRAINT fk_sl_linea
        FOREIGN KEY (linea_id)
        REFERENCES linea_investigacion(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ================================================================
-- 9. TABLA PUENTE: AC_LINEA
-- Relaciona areas de conocimiento con lineas de investigacion.
-- ================================================================

CREATE TABLE IF NOT EXISTS ac_linea (
    area_conocimiento_id INT NOT NULL,
    linea_id INT NOT NULL,

    PRIMARY KEY (area_conocimiento_id, linea_id),

    CONSTRAINT fk_acl_area
        FOREIGN KEY (area_conocimiento_id)
        REFERENCES area_conocimiento(id),

    CONSTRAINT fk_acl_linea
        FOREIGN KEY (linea_id)
        REFERENCES linea_investigacion(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ================================================================
-- 10. TABLA PUENTE: ODS_LINEA
-- Relaciona objetivos de desarrollo sostenible con lineas.
-- ================================================================

CREATE TABLE IF NOT EXISTS ods_linea (
    ods_id INT NOT NULL,
    linea_id INT NOT NULL,

    PRIMARY KEY (ods_id, linea_id),

    CONSTRAINT fk_odsl_ods
        FOREIGN KEY (ods_id)
        REFERENCES objetivo_desarrollo_sostenible(id),

    CONSTRAINT fk_odsl_linea
        FOREIGN KEY (linea_id)
        REFERENCES linea_investigacion(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ================================================================
-- 11. TABLA PUENTE: AA_LINEA
-- Relaciona areas de aplicacion con lineas de investigacion.
-- ================================================================

CREATE TABLE IF NOT EXISTS aa_linea (
    area_aplicacion_id INT NOT NULL,
    linea_id INT NOT NULL,

    PRIMARY KEY (area_aplicacion_id, linea_id),

    CONSTRAINT fk_aal_area
        FOREIGN KEY (area_aplicacion_id)
        REFERENCES area_aplicacion(id),

    CONSTRAINT fk_aal_linea
        FOREIGN KEY (linea_id)
        REFERENCES linea_investigacion(id)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ================================================================
-- 12. PROCEDIMIENTOS ALMACENADOS
-- El delimitador permite escribir varias instrucciones SQL
-- dentro de un mismo procedimiento.
-- ================================================================

DELIMITER $$


-- ================================================================
-- PROCEDIMIENTO 1: LISTAR GRUPOS
-- Devuelve todos los grupos activos.
-- ================================================================

CREATE OR REPLACE PROCEDURE
sp_listar_grupo_investigacion()
BEGIN

    SELECT
        id,
        gruplac,
        categoria,
        convocatoria,
        ambito,
        universidad_id
    FROM grupo_investigacion
    WHERE activo = 1
    ORDER BY id;

END$$


-- ================================================================
-- PROCEDIMIENTO 2: CONSULTAR GRUPO
-- Devuelve el encabezado y sus semilleros activos.
-- ================================================================

CREATE OR REPLACE PROCEDURE
sp_consultar_grupo_investigacion(
    IN p_id INT
)
BEGIN

    -- Si el grupo no existe o esta retirado,
    -- se genera una señal que la API traduce a 404.

    IF NOT EXISTS (
        SELECT 1
        FROM grupo_investigacion
        WHERE id = p_id
          AND activo = 1
    ) THEN

        SIGNAL SQLSTATE '45001'
        SET MESSAGE_TEXT = 'Grupo no encontrado.';

    END IF;


    -- Primer resultado: encabezado del grupo.

    SELECT
        id,
        gruplac,
        categoria,
        convocatoria,
        ambito,
        universidad_id
    FROM grupo_investigacion
    WHERE id = p_id
      AND activo = 1;


    -- Segundo resultado: semilleros del grupo.

    SELECT
        id,
        nombre
    FROM semillero
    WHERE grupo_id = p_id
      AND activo = 1
    ORDER BY id;

END$$


-- ================================================================
-- PROCEDIMIENTO 3: CREAR GRUPO CON SEMILLEROS
-- Utiliza una transaccion para guardar todo o nada.
-- ================================================================

CREATE OR REPLACE PROCEDURE
sp_crear_grupo_investigacion(
    IN p_gruplac VARCHAR(255),
    IN p_categoria VARCHAR(50),
    IN p_convocatoria VARCHAR(255),
    IN p_ambito VARCHAR(255),
    IN p_universidad_id INT,
    IN p_detalle JSON
)
BEGIN

    -- Variable que almacena el ID generado por MariaDB.

    DECLARE v_grupo_id INT;


    -- Si falla alguna escritura, se deshace toda la operacion.

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN

        ROLLBACK;
        RESIGNAL;

    END;


    START TRANSACTION;


    -- Registrar el grupo.
    -- MariaDB verifica que la universidad exista.

    INSERT INTO grupo_investigacion (
        gruplac,
        categoria,
        convocatoria,
        ambito,
        universidad_id,
        activo
    )
    VALUES (
        p_gruplac,
        p_categoria,
        p_convocatoria,
        p_ambito,
        p_universidad_id,
        1
    );


    -- Obtener el ID del grupo creado.

    SET v_grupo_id = LAST_INSERT_ID();


    -- Convertir el arreglo JSON en filas de semilleros.
    -- Una lista vacia tambien es valida.

    INSERT INTO semillero (
        nombre,
        grupo_id,
        activo
    )
    SELECT
        detalle.nombre,
        v_grupo_id,
        1
    FROM JSON_TABLE(
        p_detalle,
        '$[*]' COLUMNS (
            nombre VARCHAR(100)
                PATH '$.nombre'
                ERROR ON EMPTY ERROR ON ERROR
        )
    ) AS detalle;


    -- Confirmar las escrituras.

    COMMIT;


    -- Devolver el grupo completo con sus semilleros.

    CALL sp_consultar_grupo_investigacion(v_grupo_id);

END$$


-- ================================================================
-- PROCEDIMIENTO 4: ACTUALIZAR GRUPO
-- Reemplaza el encabezado y todo el detalle.
-- No admite actualizaciones parciales.
-- ================================================================

CREATE OR REPLACE PROCEDURE
sp_actualizar_grupo_investigacion(
    IN p_id INT,
    IN p_gruplac VARCHAR(255),
    IN p_categoria VARCHAR(50),
    IN p_convocatoria VARCHAR(255),
    IN p_ambito VARCHAR(255),
    IN p_universidad_id INT,
    IN p_detalle JSON
)
BEGIN

    DECLARE v_activo TINYINT DEFAULT NULL;

    DECLARE CONTINUE HANDLER FOR NOT FOUND
        SET v_activo = NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN

        ROLLBACK;
        RESIGNAL;

    END;


    START TRANSACTION;


    -- Bloquear el registro durante la modificacion.

    SELECT activo
    INTO v_activo
    FROM grupo_investigacion
    WHERE id = p_id
    FOR UPDATE;


    -- Verificar que exista y este activo.

    IF v_activo IS NULL OR v_activo <> 1 THEN

        SIGNAL SQLSTATE '45001'
        SET MESSAGE_TEXT = 'Grupo no encontrado.';

    END IF;


    -- Actualizar el encabezado.

    UPDATE grupo_investigacion
    SET
        gruplac = p_gruplac,
        categoria = p_categoria,
        convocatoria = p_convocatoria,
        ambito = p_ambito,
        universidad_id = p_universidad_id
    WHERE id = p_id;


    -- Retirar los semilleros anteriores sin borrar sus filas.

    UPDATE semillero
    SET activo = 0
    WHERE grupo_id = p_id
      AND activo = 1;


    -- Registrar el nuevo detalle.

    INSERT INTO semillero (
        nombre,
        grupo_id,
        activo
    )
    SELECT
        detalle.nombre,
        p_id,
        1
    FROM JSON_TABLE(
        p_detalle,
        '$[*]' COLUMNS (
            nombre VARCHAR(100)
                PATH '$.nombre'
                ERROR ON EMPTY ERROR ON ERROR
        )
    ) AS detalle;


    COMMIT;


    -- Devolver la informacion actualizada.

    CALL sp_consultar_grupo_investigacion(p_id);

END$$


-- ================================================================
-- PROCEDIMIENTO 5: RETIRAR GRUPO
-- Aplica borrado logico y protege los semilleros activos.
-- ================================================================

CREATE OR REPLACE PROCEDURE
sp_eliminar_grupo_investigacion(
    IN p_id INT
)
BEGIN

    DECLARE v_activo TINYINT DEFAULT NULL;

    DECLARE CONTINUE HANDLER FOR NOT FOUND
        SET v_activo = NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN

        ROLLBACK;
        RESIGNAL;

    END;


    START TRANSACTION;


    -- Bloquear el grupo durante la operacion.

    SELECT activo
    INTO v_activo
    FROM grupo_investigacion
    WHERE id = p_id
    FOR UPDATE;


    -- Comprobar que el grupo exista.

    IF v_activo IS NULL OR v_activo <> 1 THEN

        SIGNAL SQLSTATE '45001'
        SET MESSAGE_TEXT = 'Grupo no encontrado.';

    END IF;


    -- Impedir el retiro si tiene semilleros activos.
    -- La API traducira esta señal a un conflicto 409.

    IF EXISTS (
        SELECT 1
        FROM semillero
        WHERE grupo_id = p_id
          AND activo = 1
    ) THEN

        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT =
            'Primero retire los semilleros de este grupo.';

    END IF;


    -- Retirar sin eliminar fisicamente.

    UPDATE grupo_investigacion
    SET activo = 0
    WHERE id = p_id;


    COMMIT;

END$$


-- Restaurar el delimitador original.

DELIMITER ;


-- ================================================================
-- 13. DATOS INICIALES DE LA VERSION 2
-- Permiten verificar los listados, relaciones y procedimientos.
-- INSERT IGNORE evita duplicar las llaves primarias.
-- ================================================================

INSERT IGNORE INTO docente (
    cedula,
    nombres,
    apellidos
)
VALUES
    (1017245, 'Ana', 'Torres Gómez'),
    (1023456, 'Carlos', 'Restrepo López');


INSERT IGNORE INTO grupo_investigacion (
    id,
    gruplac,
    categoria,
    convocatoria,
    ambito,
    universidad_id
)
VALUES (
    1,
    'COL0001',
    'A',
    'Convocatoria 2024',
    'Nacional',
    1
);


INSERT IGNORE INTO semillero (
    id,
    nombre,
    grupo_id
)
VALUES
    (1, 'Semillero de Visión Artificial', 1),
    (2, 'Semillero de Lenguaje Natural', 1);


INSERT IGNORE INTO grupo_linea (
    grupo_id,
    linea_id
)
VALUES (1, 1);


-- ================================================================
-- FIN DEL SCRIPT
-- ================================================================