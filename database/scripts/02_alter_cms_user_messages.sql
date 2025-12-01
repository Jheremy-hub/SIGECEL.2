-- ============================================================================
-- Script: Modificar tabla cms_user_messages
-- Proyecto: SIGECEL
-- Fecha: 2025-11-21
-- Descripción: Agregar columnas para flujo de aprobación jerárquica
-- ============================================================================

-- Verificar si las columnas ya existen antes de agregarlas
SET @dbname = DATABASE();
SET @tablename = 'cms_user_messages';

-- Agregar columna 'status'
SET @column_exists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'status'
);

SET @query = IF(@column_exists = 0,
    'ALTER TABLE cms_user_messages ADD COLUMN status VARCHAR(40) NOT NULL DEFAULT ''pendiente_aprobacion_jefe'' COMMENT ''Estado del mensaje en flujo de aprobación'' AFTER is_read',
    'SELECT ''Column status already exists'' AS message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columna 'intended_receiver_id'
SET @column_exists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'intended_receiver_id'
);

SET @query = IF(@column_exists = 0,
    'ALTER TABLE cms_user_messages ADD COLUMN intended_receiver_id BIGINT UNSIGNED NULL COMMENT ''Destinatario final previsto'' AFTER receiver_id',
    'SELECT ''Column intended_receiver_id already exists'' AS message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columna 'approver_id'
SET @column_exists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = @dbname 
    AND TABLE_NAME = @tablename 
    AND COLUMN_NAME = 'approver_id'
);

SET @query = IF(@column_exists = 0,
    'ALTER TABLE cms_user_messages ADD COLUMN approver_id BIGINT UNSIGNED NULL COMMENT ''Jefe que debe aprobar el mensaje'' AFTER intended_receiver_id',
    'SELECT ''Column approver_id already exists'' AS message'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verificación
SELECT 'Tabla cms_user_messages modificada exitosamente' AS status;
SHOW COLUMNS FROM cms_user_messages WHERE Field IN ('status', 'intended_receiver_id', 'approver_id');
