# Scripts SQL - SIGECEL

Este directorio contiene los scripts SQL para implementar las nuevas tablas del sistema SIGECEL.

## 📋 Orden de Ejecución

Los scripts deben ejecutarse en el siguiente orden:

1. **01_create_cms_user_documents.sql** - Crea tabla de documentos
2. **02_alter_cms_user_messages.sql** - Modifica tabla de mensajes
3. **03_create_cms_message_approvals.sql** - Crea tabla de aprobaciones

## ⚙️ Métodos de Ejecución

### Opción 1: Usando Laravel Migrations (Recomendado)

```bash
php artisan migrate
```

### Opción 2: Ejecución Manual con MySQL CLI

```bash
# Conectar a la base de datos
mysql -u usuario -p nombre_base_datos

# Ejecutar scripts en orden
source database/scripts/01_create_cms_user_documents.sql
source database/scripts/02_alter_cms_user_messages.sql
source database/scripts/03_create_cms_message_approvals.sql
```

### Opción 3: Ejecución con MySQL Workbench

1. Abrir MySQL Workbench
2. Conectar a la base de datos
3. Abrir cada script (File → Open SQL Script)
4. Ejecutar en orden con el botón "Execute" (⚡)

### Opción 4: Ejecución por línea de comandos

```bash
mysql -u usuario -p nombre_base_datos < database/scripts/01_create_cms_user_documents.sql
mysql -u usuario -p nombre_base_datos < database/scripts/02_alter_cms_user_messages.sql
mysql -u usuario -p nombre_base_datos < database/scripts/03_create_cms_message_approvals.sql
```

## ✅ Verificación Post-Ejecución

Después de ejecutar los scripts, verifica que todo se haya creado correctamente:

```sql
-- Verificar tablas creadas
SHOW TABLES LIKE 'cms_%';

-- Verificar estructura de cms_user_documents
DESCRIBE cms_user_documents;

-- Verificar estructura de cms_message_approvals
DESCRIBE cms_message_approvals;

-- Verificar nuevas columnas en cms_user_messages
SHOW COLUMNS FROM cms_user_messages 
WHERE Field IN ('status', 'intended_receiver_id', 'approver_id');
```

## ⚠️ Importante

- **Realizar backup** antes de ejecutar estos scripts
- Los scripts están diseñados para ser **idempotentes** (se pueden ejecutar múltiples veces)
- El script 02 verifica la existencia de columnas antes de agregarlas
- Si usas PostgreSQL, los scripts necesitarán adaptaciones menores

## 📚 Documentación Completa

Ver archivo `DOCUMENTACION_TABLAS_BD.md` en la raíz del proyecto para documentación detallada.

## 🔗 Dependencias

- MySQL 5.7+ o MariaDB 10.2+
- La tabla `cms_users` debe existir previamente
- La tabla `cms_user_messages` debe existir previamente

## 📞 Soporte

Para preguntas o problemas:
- Repositorio: https://github.com/Jheremy-hub/SIGECEL
- Responsable: CEL
