-- =========================================================================
-- 1. PREPARACIÓN DE TABLAS (ALTERS) (VERSION 2)
-- =========================================================================

-- Agregamos la columna para el Disparador (Propuesta A) en la tabla universidad
ALTER TABLE universidad ADD COLUMN IF NOT EXISTS total_grupos INT NOT NULL DEFAULT 0;

-- Agregamos la columna de borrado lógico al grupo de investigación
ALTER TABLE grupo_investigacion ADD COLUMN IF NOT EXISTS activo TINYINT(1) NOT NULL DEFAULT 1;

-- Calculamos el valor inicial de total_grupos para las universidades que ya existan
UPDATE universidad u
SET total_grupos = (SELECT COUNT(*) FROM grupo_investigacion g WHERE g.universidad_id = u.id AND g.activo = 1);

-- =========================================================================
-- 2. DISPARADORES Y RECUENTO (TRIGGERS)
-- =========================================================================
DELIMITER $$

-- Procedimiento común para recalcular los grupos
CREATE OR REPLACE PROCEDURE sp_recontar_grupos(IN p_universidad INT)
BEGIN
    IF p_universidad IS NOT NULL THEN
        UPDATE universidad
        SET total_grupos = (SELECT COUNT(*) FROM grupo_investigacion
                            WHERE universidad_id = p_universidad AND activo = 1)
        WHERE id = p_universidad;
    END IF;
END$$

-- Trigger 1: Al insertar un nuevo grupo
CREATE OR REPLACE TRIGGER trg_grupo_ai
AFTER INSERT ON grupo_investigacion
FOR EACH ROW
BEGIN
    CALL sp_recontar_grupos(NEW.universidad_id);
END$$

-- Trigger 2: Al actualizar un grupo (incluye cuando se hace UPDATE activo = 0)
CREATE OR REPLACE TRIGGER trg_grupo_au
AFTER UPDATE ON grupo_investigacion
FOR EACH ROW
BEGIN
    -- Recalcula el nuevo padre
    CALL sp_recontar_grupos(NEW.universidad_id);
    
    -- Si el padre cambió, también recalcula el padre anterior para restarle el grupo
    IF OLD.universidad_id <> NEW.universidad_id THEN
        CALL sp_recontar_grupos(OLD.universidad_id);
    END IF;
END$$

-- Trigger 3: Al eliminar físicamente (solo por si acaso, aunque usemos borrado lógico)
CREATE OR REPLACE TRIGGER trg_grupo_ad
AFTER DELETE ON grupo_investigacion
FOR EACH ROW
BEGIN
    CALL sp_recontar_grupos(OLD.universidad_id);
END$$

-- =========================================================================
-- 3. PROCEDIMIENTOS ALMACENADOS (CRUD: grupo_investigacion)
-- =========================================================================

-- A. Listar (filtra inactivos)
CREATE OR REPLACE PROCEDURE sp_listar_grupo_investigacion()
BEGIN
    SELECT id, gruplac, categoria, convocatoria, ambito, universidad_id
    FROM grupo_investigacion
    WHERE activo = 1;
END$$

-- B. Consultar uno solo
CREATE OR REPLACE PROCEDURE sp_consultar_grupo_investigacion(IN p_id INT)
BEGIN
    SELECT id, gruplac, categoria, convocatoria, ambito, universidad_id
    FROM grupo_investigacion
    WHERE id = p_id AND activo = 1;
END$$

-- C. Crear (devuelve la fila insertada)
CREATE OR REPLACE PROCEDURE sp_crear_grupo_investigacion(
    IN p_gruplac VARCHAR(255),
    IN p_categoria VARCHAR(50),
    IN p_convocatoria VARCHAR(255),
    IN p_ambito VARCHAR(255),
    IN p_universidad_id INT
)
BEGIN
    INSERT INTO grupo_investigacion (gruplac, categoria, convocatoria, ambito, universidad_id, activo)
    VALUES (p_gruplac, p_categoria, p_convocatoria, p_ambito, p_universidad_id, 1);

    -- MariaDB lee el ID recién generado con LAST_INSERT_ID()
    SELECT id, gruplac, categoria, convocatoria, ambito, universidad_id
    FROM grupo_investigacion
    WHERE id = LAST_INSERT_ID();
END$$

-- D. Actualizar (para PUT y PATCH)
CREATE OR REPLACE PROCEDURE sp_actualizar_grupo_investigacion(
    IN p_id INT,
    IN p_gruplac VARCHAR(255),
    IN p_categoria VARCHAR(50),
    IN p_convocatoria VARCHAR(255),
    IN p_ambito VARCHAR(255),
    IN p_universidad_id INT
)
BEGIN
    UPDATE grupo_investigacion
    SET gruplac = p_gruplac,
        categoria = p_categoria,
        convocatoria = p_convocatoria,
        ambito = p_ambito,
        universidad_id = p_universidad_id
    WHERE id = p_id AND activo = 1;
END$$

-- E. Eliminar (Regla de negocio: SIGNAL 45000 + Borrado lógico)
CREATE OR REPLACE PROCEDURE sp_eliminar_grupo_investigacion(IN p_id INT)
BEGIN
    -- 1. Validar la regla de negocio (que no tenga semilleros activos)
    -- Asumiendo que la columna de la FK en semillero se llama grupo_id
    IF EXISTS (SELECT 1 FROM semillero WHERE grupo_id = p_id AND activo = 1) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se puede eliminar el grupo porque tiene semilleros activos. Retire primero el detalle.';
    END IF;

    -- 2. Si pasa la validación, procedemos al borrado lógico
    UPDATE grupo_investigacion SET activo = 0 WHERE id = p_id AND activo = 1;
END$$

DELIMITER ;