-- ============================================================================
-- Script: Crear TODAS las tablas del proyecto SIGECEL
-- Proyecto: Sistema de Gestión de Trámite Documentario (SIGECEL)
-- Fecha: 2025-11-22
-- Autor: CEL - Colegio de Economistas de Lima
-- Descripción: Script maestro para crear la estructura completa de la BD
-- ============================================================================

-- Configuración inicial
SET NAMES 'utf8mb4';
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- SECCIÓN 1: TABLAS DE USUARIOS Y ROLES
-- ============================================================================

-- Tabla: cms_users (Usuarios del sistema)
CREATE TABLE IF NOT EXISTS `cms_users` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL COMMENT 'Nombre del usuario',
    `apellidos` VARCHAR(255) NOT NULL COMMENT 'Apellidos del usuario',
    `cargo` VARCHAR(255) NULL COMMENT 'Cargo del usuario',
    `email` VARCHAR(255) UNIQUE NOT NULL COMMENT 'Email único del usuario',
    `password` VARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada',
    `photo` VARCHAR(255) NULL COMMENT 'Ruta de la foto de perfil',
    `celular` VARCHAR(20) NULL COMMENT 'Número de celular',
    `fecha_nacimiento` DATE NULL COMMENT 'Fecha de nacimiento (para cumpleaños)',
    `dni` VARCHAR(20) NULL COMMENT 'Documento de identidad',
    `direccion` TEXT NULL COMMENT 'Dirección del usuario',
    `id_cms_privileges` INT NULL COMMENT 'ID de privilegios (legacy)',
    `id_cargo` INT NULL COMMENT 'ID del cargo (legacy)',
    `id_sede` INT NULL COMMENT 'ID de la sede (legacy)',
    `id_estado` TINYINT DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_email` (`email`),
    INDEX `idx_estado` (`id_estado`),
    INDEX `idx_nombre` (`name`, `apellidos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Usuarios del sistema SIGECEL';

-- Tabla: cms_user_roles (Roles y jerarquía de usuarios)
CREATE TABLE IF NOT EXISTS `cms_user_roles` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID del usuario',
    `role` VARCHAR(255) NOT NULL COMMENT 'Nombre del rol',
    `hierarchy_level` INT DEFAULT 5 COMMENT 'Nivel jerárquico (1=más alto, 5=más bajo)',
    `parent_role_id` BIGINT UNSIGNED NULL COMMENT 'ID del rol padre (jefe)',
    `assigned_at` TIMESTAMP NULL COMMENT 'Fecha de asignación del rol',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `cms_users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_role_id`) REFERENCES `cms_user_roles`(`id`) ON DELETE SET NULL,
    
    INDEX `idx_role` (`role`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_hierarchy` (`hierarchy_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Roles y jerarquía organizacional';

-- ============================================================================
-- SECCIÓN 2: TABLAS DE GESTIÓN DOCUMENTAL
-- ============================================================================

-- Tabla: cms_user_documents (Documentos oficiales generados)
CREATE TABLE IF NOT EXISTS `cms_user_documents` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario que creó el documento',
    `document_type` VARCHAR(255) NOT NULL COMMENT 'Tipo: Memo, Carta, Oficio',
    `sender` VARCHAR(255) NOT NULL COMMENT 'Remitente del documento',
    `institution` VARCHAR(255) NOT NULL COMMENT 'Institución emisora',
    `subject` VARCHAR(255) NOT NULL COMMENT 'Asunto del documento',
    `content` TEXT NULL COMMENT 'Contenido textual del documento',
    `file_path` VARCHAR(255) NOT NULL COMMENT 'Ruta del archivo en storage',
    `file_name` VARCHAR(255) NOT NULL COMMENT 'Nombre original del archivo',
    `file_type` VARCHAR(255) NOT NULL COMMENT 'Tipo MIME del archivo',
    `file_size` INT NOT NULL COMMENT 'Tamaño en bytes',
    `document_code` VARCHAR(255) UNIQUE NOT NULL COMMENT 'Código correlativo único',
    `meta` TEXT NULL COMMENT 'Metadatos adicionales en JSON',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `cms_users`(`id`) ON DELETE CASCADE,
    
    INDEX `idx_user` (`user_id`),
    INDEX `idx_type` (`document_type`),
    INDEX `idx_code` (`document_code`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Documentos oficiales (Memorándums, Cartas, Oficios)';

-- ============================================================================
-- SECCIÓN 3: TABLAS DE MENSAJERÍA INTERNA
-- ============================================================================

-- Tabla: cms_user_messages (Mensajes internos del sistema)
CREATE TABLE IF NOT EXISTS `cms_user_messages` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `sender_id` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario remitente',
    `receiver_id` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario receptor actual',
    `intended_receiver_id` BIGINT UNSIGNED NULL COMMENT 'Destinatario final previsto (si requiere aprobación)',
    `approver_id` BIGINT UNSIGNED NULL COMMENT 'Jefe que debe aprobar el mensaje',
    `subject` VARCHAR(500) NOT NULL COMMENT 'Asunto del mensaje',
    `message` TEXT NOT NULL COMMENT 'Cuerpo del mensaje',
    `file_path` VARCHAR(500) NULL COMMENT 'Ruta del archivo adjunto',
    `file_name` VARCHAR(255) NULL COMMENT 'Nombre del archivo adjunto',
    `file_type` VARCHAR(255) NULL COMMENT 'Tipo MIME del adjunto',
    `file_size` INT NULL COMMENT 'Tamaño del adjunto en bytes',
    `is_read` TINYINT(1) DEFAULT 0 COMMENT '0=No leído, 1=Leído',
    `status` VARCHAR(40) DEFAULT 'sent' COMMENT 'Estado: sent, pendiente_aprobacion_jefe, aprobado_por_jefe, archivado_por_jefe',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`sender_id`) REFERENCES `cms_users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`receiver_id`) REFERENCES `cms_users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`intended_receiver_id`) REFERENCES `cms_users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`approver_id`) REFERENCES `cms_users`(`id`) ON DELETE SET NULL,
    
    INDEX `idx_sender` (`sender_id`),
    INDEX `idx_receiver` (`receiver_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Mensajes internos con flujo de aprobación jerárquica';

-- Tabla: cms_message_approvals (Historial de aprobaciones/archivados)
CREATE TABLE IF NOT EXISTS `cms_message_approvals` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `message_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID del mensaje aprobado/archivado',
    `approver_id` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario que tomó la decisión',
    `decision` ENUM('approve', 'archive') NOT NULL COMMENT 'Decisión: aprobar o archivar',
    `note` TEXT NULL COMMENT 'Nota u observación del aprobador',
    `decided_at` TIMESTAMP NULL COMMENT 'Fecha y hora de la decisión',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`message_id`) REFERENCES `cms_user_messages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approver_id`) REFERENCES `cms_users`(`id`) ON DELETE CASCADE,
    
    INDEX `idx_message` (`message_id`),
    INDEX `idx_approver` (`approver_id`),
    INDEX `idx_decided` (`decided_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de decisiones de aprobación de mensajes';

-- Tabla: cms_user_message_forwards (Reenvíos de mensajes)
CREATE TABLE IF NOT EXISTS `cms_user_message_forwards` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `original_message_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID del mensaje original',
    `forwarded_message_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID del mensaje reenviado',
    `forwarded_by` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario que reenvió',
    `forwarded_to` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario destinatario del reenvío',
    `forwarded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha del reenvío',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`original_message_id`) REFERENCES `cms_user_messages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`forwarded_message_id`) REFERENCES `cms_user_messages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`forwarded_by`) REFERENCES `cms_users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`forwarded_to`) REFERENCES `cms_users`(`id`) ON DELETE CASCADE,
    
    INDEX `idx_original` (`original_message_id`),
    INDEX `idx_forwarded` (`forwarded_message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de mensajes reenviados';

-- Tabla: cms_user_message_logs (Auditoría de acciones sobre mensajes)
CREATE TABLE IF NOT EXISTS `cms_user_message_logs` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `message_id` BIGINT UNSIGNED NOT NULL COMMENT 'ID del mensaje',
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'Usuario que realizó la acción',
    `action` VARCHAR(50) NOT NULL COMMENT 'Acción: sent, read, downloaded, forwarded, in_review, approved, etc.',
    `details` TEXT NULL COMMENT 'Detalles adicionales en JSON',
    `ip_address` VARCHAR(50) NULL COMMENT 'Dirección IP del usuario',
    `user_agent` VARCHAR(500) NULL COMMENT 'User agent del navegador',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp de la acción',
    
    FOREIGN KEY (`message_id`) REFERENCES `cms_user_messages`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `cms_users`(`id`) ON DELETE CASCADE,
    
    INDEX `idx_message` (`message_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log de auditoría de todas las acciones sobre mensajes';

-- ============================================================================
-- SECCIÓN 4: TABLAS DE SISTEMA DE CUMPLEAÑOS
-- ============================================================================

-- Tabla: cumple_imagenes (Configuración de imágenes de cumpleaños)
CREATE TABLE IF NOT EXISTS `cumple_imagenes` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `ruta_imagen` VARCHAR(500) NOT NULL COMMENT 'Ruta de la imagen de fondo',
    `vigente_desde` DATE NOT NULL COMMENT 'Fecha desde la cual está activa',
    `vigente_hasta` DATE NULL COMMENT 'Fecha hasta la cual está activa (NULL = sin límite)',
    `activo` TINYINT(1) DEFAULT 1 COMMENT '1 = activa, 0 = inactiva',
    `titulo` VARCHAR(255) NULL COMMENT 'Título del saludo de cumpleaños',
    `mensaje` TEXT NULL COMMENT 'Mensaje del saludo de cumpleaños',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_vigencia` (`activo`, `vigente_desde`, `vigente_hasta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Configuración de imágenes y textos para tarjetas de cumpleaños';

-- Tabla: cumple_saludos (Registro de saludos de cumpleaños enviados)
CREATE TABLE IF NOT EXISTS `cumple_saludos` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL COMMENT 'Usuario del sistema (FK opcional)',
    `numero_colegiado` VARCHAR(50) NULL COMMENT 'Número de colegiado',
    `nombre_completo` VARCHAR(255) NOT NULL COMMENT 'Nombre completo del cumpleañero',
    `email` VARCHAR(255) NOT NULL COMMENT 'Email destino del saludo',
    `fecha_envio` DATETIME NOT NULL COMMENT 'Fecha y hora exacta del envío',
    `imagen_id` BIGINT UNSIGNED NULL COMMENT 'ID de la imagen usada para el saludo',
    `titulo_usado` VARCHAR(255) NULL COMMENT 'Título usado en ese saludo',
    `mensaje_usado` TEXT NULL COMMENT 'Mensaje usado en ese saludo',
    `estado_envio` ENUM('enviado', 'fallido') DEFAULT 'enviado' COMMENT 'Estado del envío',
    `ip_origen` VARCHAR(50) NULL COMMENT 'IP desde donde se envió',
    `usuario_envia_id` BIGINT UNSIGNED NULL COMMENT 'Usuario que envió el saludo',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `cms_users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`imagen_id`) REFERENCES `cumple_imagenes`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`usuario_envia_id`) REFERENCES `cms_users`(`id`) ON DELETE SET NULL,
    
    INDEX `idx_fecha` (`fecha_envio`),
    INDEX `idx_email` (`email`),
    INDEX `idx_colegiado` (`numero_colegiado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de todos los saludos de cumpleaños enviados';

-- Tabla: colegiados (Base de datos de colegiados para cumpleaños)
CREATE TABLE IF NOT EXISTS `colegiados` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `nro_colegiado` VARCHAR(50) UNIQUE NOT NULL COMMENT 'Número de colegiado único',
    `nombre_completo` VARCHAR(255) NOT NULL COMMENT 'Nombre completo del colegiado',
    `email` VARCHAR(255) NULL COMMENT 'Email del colegiado',
    `fecha_nacimiento` VARCHAR(50) NULL COMMENT 'Fecha de nacimiento (formato variable)',
    `direccion` TEXT NULL COMMENT 'Dirección del colegiado',
    `telefono` VARCHAR(50) NULL COMMENT 'Teléfono de contacto',
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo' COMMENT 'Estado del colegiado',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_colegiado` (`nro_colegiado`),
    INDEX `idx_nombre` (`nombre_completo`),
    INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Base de datos de colegiados del CEL';

-- ============================================================================
-- SECCIÓN 5: TABLAS DEL SISTEMA LARAVEL
-- ============================================================================

-- Tabla: sessions (Almacenamiento de sesiones de usuario)
-- 🔥 ¡ES IMPRESCINDIBLE para el funcionamiento del login!
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) NOT NULL PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `payload` TEXT NOT NULL,
    `last_activity` INT NOT NULL,
    
    INDEX `sessions_user_id_index` (`user_id`),
    INDEX `sessions_last_activity_index` (`last_activity`),
    
    FOREIGN KEY (`user_id`) REFERENCES `cms_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Sesiones de usuario gestionadas por Laravel';

-- Tabla: cache (Sistema de caché de Laravel)
CREATE TABLE IF NOT EXISTS `cache` (
    `key` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL PRIMARY KEY,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
    `key` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL PRIMARY KEY,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: jobs (Cola de trabajos)
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    
    INDEX `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: failed_jobs (Trabajos fallidos)
CREATE TABLE IF NOT EXISTS `failed_jobs` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `uuid` VARCHAR(255) UNIQUE NOT NULL,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: migrations (Control de migraciones)
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- DATOS INICIALES DE EJEMPLO
-- ============================================================================

-- Imagen de cumpleaños por defecto
INSERT INTO `cumple_imagenes` (`ruta_imagen`, `vigente_desde`, `vigente_hasta`, `activo`, `titulo`, `mensaje`) 
VALUES ('Backend/Style/Ima.Cumple.jpg', '2025-01-01', NULL, 1, 
        '¡Feliz Cumpleaños!', 
        'El Colegio de Economistas de Lima le desea muchos éxitos y que tenga un gran día.')
ON DUPLICATE KEY UPDATE `activo` = 1;

-- ============================================================================
-- RESTAURAR CONFIGURACIÓN
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- VERIFICACIÓN
-- ============================================================================

SELECT 'Script ejecutado exitosamente. Tablas creadas:' AS status;

SELECT 
    TABLE_NAME as 'Tabla Creada',
    TABLE_ROWS as 'Registros',
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024, 2) as 'Tamaño (KB)'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME IN (
        'cms_users',
        'cms_user_roles',
        'cms_user_documents',
        'cms_user_messages',
        'cms_message_approvals',
        'cms_user_message_forwards',
        'cms_user_message_logs',
        'cumple_imagenes',
        'cumple_saludos',
        'colegiados',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'failed_jobs',
        'migrations'
    )
ORDER BY TABLE_NAME;

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
