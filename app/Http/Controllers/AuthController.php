<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Intentar autenticar con las credenciales
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Verificar si el usuario está activo
            if (Auth::user()->id_estado == 1) {
                // CORRECCIÓN: Redirigir al DASHBOARD en lugar de register.user
                return redirect()->intended(route('sige.index'));
            } else {
                // Si el usuario no está activo, cerrar sesión y mostrar error
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
                ]);
            }
        }

        // Si las credenciales son incorrectas, redirigir con un error
        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas son incorrectas.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
