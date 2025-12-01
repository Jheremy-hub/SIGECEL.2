<?php

if (!function_exists('normalize_role')) {
    /**
     * Normaliza un nombre de rol eliminando tildes y convirtiendo a minúsculas
     * para comparaciones consistentes.
     * 
     * Ejemplo:
     * - "Tecnologías de Información" → "tecnologias de informacion"
     * - "Tecnologias de Informacion" → "tecnologias de informacion"
     * 
     * @param string|null $role
     * @return string
     */
    function normalize_role(?string $role): string
    {
        if (empty($role)) {
            return '';
        }

        // Convertir a minúsculas
        $normalized = mb_strtolower($role, 'UTF-8');

        // Eliminar tildes y caracteres especiales
        $normalized = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $normalized
        );

        // Eliminar espacios extras
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        return $normalized;
    }
}

if (!function_exists('roles_are_equal')) {
    /**
     * Compara dos roles ignorando tildes y mayúsculas/minúsculas
     * 
     * @param string|null $role1
     * @param string|null $role2
     * @return bool
     */
    function roles_are_equal(?string $role1, ?string $role2): bool
    {
        return normalize_role($role1) === normalize_role($role2);
    }
}
