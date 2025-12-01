<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $roleOptions = $this->getUniqueRoleOptions();

        return view('auth.register', compact('roleOptions'));
    }

    public function register(Request $request)
{
    // Validación de datos con los nuevos roles
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'apellidos' => 'required|string|max:255',
        'cargo' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:cms_users',
        'password' => 'required|string|min:6|confirmed',
        'celular' => 'nullable|string|max:20',
        'role' => 'required|string|max:255',
        'id_estado' => 'required|in:0,1'
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    try {
        // Determinar nivel de jerarquía basado en el rol
        $hierarchyLevel = $this->getHierarchyLevel($request->role);

        // Crear el usuario
        $user = User::create([
            'name' => $request->name,
            'apellidos' => $request->apellidos,
            'cargo' => $request->cargo,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'celular' => $request->celular,
            'id_cms_privileges' => $hierarchyLevel,
            'id_estado' => $request->id_estado,
            'photo' => null,
            'id_cargo' => null,
            'id_sede' => null
        ]);

        // Crear el rol del usuario
        UserRole::create([
            'user_id' => $user->id,
            'role' => $request->role,
            'hierarchy_level' => $hierarchyLevel,
            'assigned_at' => now()
        ]);

        return redirect()->route('users.list')->with('success', 'Usuario registrado exitosamente!');
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error al registrar el usuario: ' . $e->getMessage())
            ->withInput();
    }
}

private function getHierarchyLevel($role)
{
    $levels = [
        'Decano' => 1,
        'Administración' => 2,
        'Asesoria Legal' => 2,
        'Tecnologias de Información' => 2,
        'Planificación' => 2,
        'Centro de Marketing' => 3,
        'Centro de Servicios al Colegiado' => 3,
        'Centro de Investigación y Desarrollo' => 3,
        'Comité Especializado' => 4,
        'Comisión Técnica' => 4,
        'Administrador' => 1,
        'Secretaría' => 3,
        'Recepción' => 4,
        'Usuario' => 5
    ];
    return $levels[$role] ?? 5;
}
}
