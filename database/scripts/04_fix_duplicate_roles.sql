-- ============================================================================
-- Script: Unificar roles duplicados en cms_user_roles
-- Proyecto: SIGECEL
-- Fecha: 2025-11-22
-- Descripción: Eliminar registros duplicados de "Tecnologías de Información"
--              dejando solo UN registro por rol que puedan compartir múltiples usuarios
-- ============================================================================

-- PASO 1: Ver roles duplicados (solo consulta)
SELECT 
    role,
    COUNT(*) as total_registros,
    GROUP_CONCAT(id) as ids,
    GROUP_CONCAT(user_id) as user_ids
FROM cms_user_roles
WHERE role = 'Tecnologias de Información'
GROUP BY role;

-- PASO 2: Eliminar duplicados MANTENIENDO el primer registro
-- Esto eliminará los registros duplicados y mantendrá solo el primero
DELETE FROM cms_user_roles
WHERE role = 'Tecnologias de Información'
  AND id NOT IN (
      SELECT * FROM (
          SELECT MIN(id) 
          FROM cms_user_roles 
          WHERE role = 'Tecnologias de Información'
      ) AS temp
  );

-- PASO 3: Verificar que solo quede 1 registro
SELECT 
    id,
    role,
    user_id,
    hierarchy_level,
    parent_role_id
FROM cms_user_roles
WHERE role = 'Tecnologias de Información';

-- RESULTADO ESPERADO: Solo 1 fila

-- ============================================================================
-- NOTA IMPORTANTE:
-- Después de ejecutar este script, los 3 usuarios de TI compartirán
-- el MISMO registro de rol. NO necesitas crear registros separados por usuario.
-- 
-- El sistema está diseñado para que:
-- - La tabla cms_users tenga los usuarios
-- - La tabla cms_user_roles tenga los NOMBRES de roles (catálogo)
-- - NO debe haber un registro por cada usuario, sino uno por ROL
-- ============================================================================
