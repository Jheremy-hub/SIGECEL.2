# Documentación de Nuevas Tablas - SIGECEL

**Proyecto:** Sistema de Gestión de Trámite Documentario (SIGECEL)  
**Responsable:** CEL  
**Fecha:** 21 de Noviembre, 2025

---

## Resumen

Este documento detalla las nuevas tablas y modificaciones a implementar en la base de datos del sistema SIGECEL.

**Total de cambios:**
- 1 nueva tabla creada (`cms_user_documents`)
- 1 nueva tabla creada (`cms_message_approvals`)
- 3 nuevas columnas agregadas a tabla existente (`cms_user_messages`)

---

## 1. Tabla: `cms_user_documents`

**Propósito:** Almacenar documentos oficiales (Memorandums, Cartas y Oficios) creados por los usuarios del sistema.

**Fecha de creación:** 05/11/2025

### Estructura de la Tabla

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Identificador único del documento |
| `user_id` | BIGINT UNSIGNED | NOT NULL, FOREIGN KEY → `cms_users.id` | Usuario propietario del documento |
| `document_type` | VARCHAR(255) | NOT NULL | Tipo de documento: 'Memo', 'Carta', 'Oficio' |
| `sender` | VARCHAR(255) | NOT NULL | Nombre del remitente |
| `institution` | VARCHAR(255) | NOT NULL | Institución emisora |
| `subject` | VARCHAR(255) | NOT NULL | Asunto del documento |
| `content` | TEXT | NULLABLE | Contenido del documento |
| `file_path` | VARCHAR(255) | NOT NULL | Ruta donde se almacena el archivo |
| `file_name` | VARCHAR(255) | NOT NULL | Nombre original del archivo |
| `file_type` | VARCHAR(255) | NOT NULL | Tipo MIME del archivo (ej: application/pdf) |
| `file_size` | INTEGER | NOT NULL | Tamaño del archivo en bytes |
| `document_code` | VARCHAR(255) | UNIQUE, NOT NULL | Código único correlativo (0001, 0002, etc.) |
| `created_at` | TIMESTAMP | NULL | Fecha de creación del registro |
| `updated_at` | TIMESTAMP | NULL | Fecha de última actualización |

### Índices y Claves

- **Primary Key:** `id`
- **Foreign Key:** `user_id` → `cms_users(id)` ON DELETE CASCADE
- **Unique:** `document_code`

### Ejemplo de Datos

```
id: 1
user_id: 15
document_type: "Oficio"
sender: "ECON. Juan Pérez"
institution: "Colegio de Economistas de Lima"
subject: "Solicitud de Informe Trimestral"
content: "Se solicita el informe..."
file_path: "documents/2025/11/oficio_0001.pdf"
file_name: "oficio_0001.pdf"
file_type: "application/pdf"
file_size: 245678
document_code: "0001"
created_at: "2025-11-05 10:30:00"
updated_at: "2025-11-05 10:30:00"
```

---

## 2. Modificaciones a Tabla: `cms_user_messages`

**Propósito:** Agregar funcionalidad de aprobación jerárquica de mensajes.

**Fecha de modificación:** 21/11/2025

### Nuevas Columnas

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `status` | VARCHAR(40) | DEFAULT 'pendiente_aprobacion_jefe' | Estado del mensaje en el flujo de aprobación |
| `intended_receiver_id` | BIGINT UNSIGNED | NULLABLE | ID del destinatario final previsto |
| `approver_id` | BIGINT UNSIGNED | NULLABLE | ID del jefe que debe aprobar el mensaje |

### Posibles Valores de `status`

- `pendiente_aprobacion_jefe`: Mensaje esperando aprobación del jefe
- `aprobado`: Mensaje aprobado y enviado al destinatario
- `archivado`: Mensaje archivado por el aprobador

### Flujo de Aprobación

1. Usuario crea mensaje → `status = 'pendiente_aprobacion_jefe'`
2. `receiver_id` = jefe aprobador
3. `intended_receiver_id` = destinatario final
4. Jefe aprueba/archiva → registro en `cms_message_approvals`
5. Si aprueba → mensaje llega al destinatario final

---

## 3. Tabla: `cms_message_approvals`

**Propósito:** Registrar historial de decisiones de aprobación de mensajes.

**Fecha de creación:** 21/11/2025

### Estructura de la Tabla

| Campo | Tipo | Restricciones | Descripción |
|-------|------|---------------|-------------|
| `id` | BIGINT UNSIGNED | PRIMARY KEY, AUTO_INCREMENT | Identificador único de la aprobación |
| `message_id` | BIGINT UNSIGNED | NOT NULL | ID del mensaje aprobado/archivado |
| `approver_id` | BIGINT UNSIGNED | NOT NULL | ID del usuario que tomó la decisión |
| `decision` | ENUM | NOT NULL, VALUES: 'approve', 'archive' | Decisión tomada |
| `note` | TEXT | NULLABLE | Nota u observación del aprobador |
| `decided_at` | TIMESTAMP | NULLABLE | Fecha y hora de la decisión |
| `created_at` | TIMESTAMP | NULL | Fecha de creación del registro |
| `updated_at` | TIMESTAMP | NULL | Fecha de última actualización |

### Índices y Claves

- **Primary Key:** `id`
- **Índices recomendados:** 
  - `message_id` (para búsquedas rápidas)
  - `approver_id` (para historial por aprobador)

### Ejemplo de Datos

```
id: 1
message_id: 125
approver_id: 3
decision: "approve"
note: "Aprobado para envío"
decided_at: "2025-11-21 15:30:00"
created_at: "2025-11-21 15:30:00"
updated_at: "2025-11-21 15:30:00"
```

---

## Scripts SQL de Implementación

### Script 1: Crear tabla `cms_user_documents`

```sql
CREATE TABLE `cms_user_documents` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `document_type` VARCHAR(255) NOT NULL,
    `sender` VARCHAR(255) NOT NULL,
    `institution` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `content` TEXT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(255) NOT NULL,
    `file_size` INT NOT NULL,
    `document_code` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    UNIQUE KEY `cms_user_documents_document_code_unique` (`document_code`),
    KEY `cms_user_documents_user_id_foreign` (`user_id`),
    CONSTRAINT `cms_user_documents_user_id_foreign` 
        FOREIGN KEY (`user_id`) 
        REFERENCES `cms_users` (`id`) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Script 2: Modificar tabla `cms_user_messages`

```sql
-- Agregar nuevas columnas a cms_user_messages
ALTER TABLE `cms_user_messages` 
ADD COLUMN `status` VARCHAR(40) NOT NULL DEFAULT 'pendiente_aprobacion_jefe' AFTER `is_read`,
ADD COLUMN `intended_receiver_id` BIGINT UNSIGNED NULL AFTER `receiver_id`,
ADD COLUMN `approver_id` BIGINT UNSIGNED NULL AFTER `intended_receiver_id`;
```

### Script 3: Crear tabla `cms_message_approvals`

```sql
CREATE TABLE `cms_message_approvals` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `message_id` BIGINT UNSIGNED NOT NULL,
    `approver_id` BIGINT UNSIGNED NOT NULL,
    `decision` ENUM('approve', 'archive') NOT NULL,
    `note` TEXT NULL,
    `decided_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    KEY `cms_message_approvals_message_id_index` (`message_id`),
    KEY `cms_message_approvals_approver_id_index` (`approver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Instrucciones de Implementación

### Usando Laravel Migrations (Recomendado)

```bash
# Ejecutar las migraciones
php artisan migrate
```

### Usando Script SQL Manual

1. Conectarse a la base de datos del proyecto
2. Ejecutar los scripts en el siguiente orden:
   - Script 1: Tabla `cms_user_documents`
   - Script 2: Modificaciones a `cms_user_messages`
   - Script 3: Tabla `cms_message_approvals`
3. Verificar la creación exitosa de las tablas

---

## Validación Post-Implementación

### Comandos de Verificación

```sql
-- Verificar estructura de cms_user_documents
DESCRIBE cms_user_documents;

-- Verificar estructura de cms_message_approvals
DESCRIBE cms_message_approvals;

-- Verificar nuevas columnas en cms_user_messages
SHOW COLUMNS FROM cms_user_messages 
WHERE Field IN ('status', 'intended_receiver_id', 'approver_id');

-- Verificar constraints
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'cms_user_documents';
```

---

## Notas Adicionales

### Dependencias

- La tabla `cms_user_documents` requiere que exista la tabla `cms_users`
- Las modificaciones a `cms_user_messages` requieren que la tabla ya exista

### Compatibilidad

- Laravel 11.x
- MySQL 5.7+ / MariaDB 10.2+
- PostgreSQL 10+ (con adaptaciones menores)

### Respaldo

**IMPORTANTE:** Se recomienda realizar un respaldo completo de la base de datos antes de ejecutar estos cambios.

```bash
# Ejemplo de respaldo con mysqldump
mysqldump -u usuario -p nombre_base_datos > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## Archivos de Migración de Laravel

Los archivos de migración originales se encuentran en:

1. `database/migrations/2025_11_05_101148_create_cms_user_documents_table.php`
2. `database/migrations/2025_11_21_150000_add_boss_approval_to_messages.php`

---

## Contacto

Para preguntas o aclaraciones sobre esta documentación:

**Responsable:** CEL  
**Proyecto:** SIGECEL  
**Repositorio:** https://github.com/Jheremy-hub/SIGECEL
