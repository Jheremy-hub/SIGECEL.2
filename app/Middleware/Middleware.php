<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $userRole = Auth::user()->role->role ?? '';
        
        if ($userRole !== 'Administrador') {
            return redirect('/dashboard')->with('error', 'No tienes permisos de administrador.');
        }

        return $next($request);
    }
}