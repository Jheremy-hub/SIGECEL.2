<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Obtiene lista única de roles eliminando duplicados por normalización
     * Ejemplo: "Tecnologías de Información" y "Tecnologias de Informacion" → solo uno
     * 
     * @return \Illuminate\Support\Collection
     */
    protected function getUniqueRoleOptions()
    {
        $roles = \App\Models\UserRole::whereNotNull('role')
            ->pluck('role')
            ->unique();
        
        // Agrupar por nombre normalizado y tomar el primero de cada grupo
        $grouped = $roles->groupBy(function ($role) {
            return normalize_role($role);
        });
        
        // Tomar el primer rol de cada grupo (preferir el que tenga tildes)
        $uniqueRoles = $grouped->map(function ($group) {
            // Ordenar para preferir nombres con tildes (caracteres especiales)
            return $group->sortByDesc(function ($role) {
                return strlen($role) - strlen(normalize_role($role));
            })->first();
        })->values()->sort()->values();
        
        return $uniqueRoles;
    }
}

