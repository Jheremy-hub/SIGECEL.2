-- ============================================================================
-- Script: Crear tabla cms_message_approvals
-- Proyecto: SIGECEL
-- Fecha: 2025-11-21
-- Descripción: Tabla para registrar historial de aprobaciones/archivados
-- ============================================================================

CREATE TABLE IF NOT EXISTS `cms_message_approvals` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `message_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID del mensaje aprobado/archivado',
    `approver_id` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario que tomó la decisión',
    `decision` ENUM('approve', 'archive') NOT NULL COMMENT 'Decisión: aprobar o archivar',
    `note` TEXT NULL COMMENT 'Nota u observación del aprobador',
    `decided_at` TIMESTAMP NULL COMMENT 'Fecha y hora de la decisión',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    
    -- Índices para optimizar búsquedas
    KEY `cms_message_approvals_message_id_index` (`message_id`),
    KEY `cms_message_approvals_approver_id_index` (`approver_id`)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de decisiones de aprobación de mensajes';

-- Verificación
SELECT 'Tabla cms_message_approvals creada exitosamente' AS status;
DESCRIBE cms_message_approvals;
