<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function listUsers(Request $request)
    {
        $rolesList = $this->getUniqueRoleOptions();

        $query = User::with('role')->orderBy('id', 'desc');

        if ($request->filled('name')) {
            $name = trim($request->input('name'));
            $query->where(function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%")
                  ->orWhere('apellidos', 'like', "%{$name}%");
            });
        }

        if ($request->filled('email')) {
            $email = trim($request->input('email'));
            $query->where('email', 'like', "%{$email}%");
        }

        if ($request->filled('role')) {
            $role = trim($request->input('role'));
            $query->whereHas('role', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        if ($request->filled('estado')) {
            $estado = (int) $request->input('estado');
            $query->where('id_estado', $estado);
        }

        $showAll = $request->boolean('all');

        if ($showAll) {
            $users = $query->get();
        } else {
            $users = $query->paginate(20)->appends($request->query());
        }

        return view('users.list', compact('users', 'rolesList', 'showAll'));
    }

    public function editUser($id)
    {
        $user = User::with('role')->findOrFail($id);
        $roleOptions = $this->getUniqueRoleOptions();
        return view('users.edit', compact('user', 'roleOptions'));
    }
public function updateUser(Request $request, $id)
{
    $user = User::findOrFail($id);
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'apellidos' => 'required|string|max:255',
        'cargo' => 'required|string|max:255',
        'email' => 'required|email|unique:cms_users,email,' . $user->id,
        'celular' => 'nullable|string|max:20',
        'role' => 'required|string|max:255',
        'id_estado' => 'required|in:0,1'
    ]);

    $user->update($validated);

    // Actualizar rol si existe
    if ($user->role) {
        $user->role->update([
            'role' => $request->role,
        'hierarchy_level' => $this->getHierarchyLevel($request->role)
        ]);
    } else {
        // Crear rol si no existe
        UserRole::create([
            'user_id' => $user->id,
            'role' => $request->role,
            'hierarchy_level' => $this->getHierarchyLevel($request->role),
            'assigned_at' => now()
        ]);
    }

    return redirect()->route('users.list')->with('success', 'Usuario actualizado exitosamente!');
}

    private function getHierarchyLevel($role)
    {
        // Normalizar el rol recibido para comparación
        $normalizedRole = normalize_role($role);
        
        $levels = [
            normalize_role('Decano') => 1,
            normalize_role('Administración') => 2,
            normalize_role('Asesoria Legal') => 2,
            normalize_role('Tecnologias de Información') => 2,  // Con o sin tilde, mismo nivel
            normalize_role('Planificación') => 2,
            normalize_role('Centro de Marketing') => 3,
            normalize_role('Centro de Servicios al Colegiado') => 3,
            normalize_role('Centro de Investigación y Desarrollo') => 3,
            normalize_role('Comité Especializado') => 4,
            normalize_role('Comisión Técnica') => 4,
            normalize_role('Administrador') => 1,
            normalize_role('Secretaría') => 3,
            normalize_role('Recepción') => 4,
            normalize_role('Usuario') => 5
        ];
        
        return $levels[$normalizedRole] ?? 5;
    }

    public function roles()
    {
        $roles = UserRole::whereNotNull('role')
            ->select(
                DB::raw('MIN(id) as id'),
                'role',
                DB::raw('MIN(hierarchy_level) as hierarchy_level'),
                DB::raw('MAX(assigned_at) as assigned_at'),
                DB::raw('SUM(CASE WHEN user_id IS NULL THEN 1 ELSE 0 END) as catalog_count'),
                DB::raw('SUM(CASE WHEN user_id IS NOT NULL THEN 1 ELSE 0 END) as assigned_count')
            )
            ->groupBy('role')
            ->orderBy('role')
            ->get();

        return view('users.roles', compact('roles'));
    }

    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|string|max:255',
            'hierarchy_level' => 'nullable|integer|min:1|max:10',
        ]);

        $roleName = trim($data['role']);
        $level = $data['hierarchy_level'] ?? 5;

        $exists = UserRole::where('role', $roleName)->exists();
        if ($exists) {
            return back()->with('success', 'El rol ya estaba registrado.');
        }

        UserRole::create([
            'user_id' => Auth::id(), // se asigna al usuario actual para cumplir NOT NULL
            'role' => $roleName,
            'hierarchy_level' => $level,
            'assigned_at' => now(),
        ]);

        return back()->with('success', 'Rol creado correctamente.');
    }

    public function deleteRole(Request $request)
    {
        $data = $request->validate([
            'role' => 'required|string|max:255',
        ]);

        $roleName = trim($data['role']);

        $deleted = UserRole::where('role', $roleName)->delete();

        if ($deleted === 0) {
            return back()->with('error', 'No se encontró el rol para eliminar.');
        }

        return back()->with('success', 'Rol eliminado correctamente.');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Borrar rol asociado si existe
        if ($user->role) {
            $user->role->delete();
        }

        $user->delete();

        return redirect()->route('users.list')->with('success', 'Usuario eliminado correctamente.');
    }

    public function roleHierarchy()
    {
        $roles = UserRole::with('user')
            ->whereNotNull('role')
            ->orderBy('role')
            ->get();

        return view('users.roles_hierarchy', compact('roles'));
    }

    public function updateRoleHierarchy(Request $request)
    {
        $data = $request->validate([
            'role_id'        => 'required|integer|exists:cms_user_roles,id',
            'parent_role_id' => 'nullable|integer|exists:cms_user_roles,id',
        ]);

        if (!empty($data['parent_role_id']) && (int) $data['parent_role_id'] === (int) $data['role_id']) {
            return back()->with('error', 'Un rol no puede ser su propio jefe.');
        }

        $role = UserRole::findOrFail($data['role_id']);
        $role->parent_role_id = $data['parent_role_id'] ?? null;
        $role->save();

        return back()->with('success', 'Jerarquía actualizada.');
    }
}
