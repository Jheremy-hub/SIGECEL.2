-- ============================================================================
-- Script: Crear tabla cms_user_documents
-- Proyecto: SIGECEL
-- Fecha: 2025-11-21
-- Descripción: Tabla para almacenar documentos oficiales (Memos, Cartas, Oficios)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `cms_user_documents` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario propietario del documento',
    `document_type` VARCHAR(255) NOT NULL COMMENT 'Tipo: Memo, Carta, Oficio',
    `sender` VARCHAR(255) NOT NULL COMMENT 'Nombre del remitente',
    `institution` VARCHAR(255) NOT NULL COMMENT 'Institución emisora',
    `subject` VARCHAR(255) NOT NULL COMMENT 'Asunto del documento',
    `content` TEXT NULL COMMENT 'Contenido del documento',
    `file_path` VARCHAR(255) NOT NULL COMMENT 'Ruta del archivo',
    `file_name` VARCHAR(255) NOT NULL COMMENT 'Nombre del archivo',
    `file_type` VARCHAR(255) NOT NULL COMMENT 'Tipo MIME',
    `file_size` INT NOT NULL COMMENT 'Tamaño en bytes',
    `document_code` VARCHAR(255) NOT NULL COMMENT 'Código único correlativo',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    
    -- Índices
    UNIQUE KEY `cms_user_documents_document_code_unique` (`document_code`),
    KEY `cms_user_documents_user_id_foreign` (`user_id`),
    
    -- Foreign Keys
    CONSTRAINT `cms_user_documents_user_id_foreign` 
        FOREIGN KEY (`user_id`) 
        REFERENCES `cms_users` (`id`) 
        ON DELETE CASCADE
        
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Almacena documentos oficiales generados por usuarios';

-- Verificación
SELECT 'Tabla cms_user_documents creada exitosamente' AS status;
DESCRIBE cms_user_documents;
