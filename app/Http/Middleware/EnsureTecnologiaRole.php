<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EnsureTecnologiaRole
{
    /**
     * Permite el acceso solo al rol de Tecnologías de Información.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Normalizamos para tolerar acentos o variantes de escritura.
        $role = Str::ascii(Str::lower((string) optional(Auth::user()->role)->role));
        $isTecnologia = in_array($role, [
            'tecnologias de informacion',
            'tecnologia de informacion',
        ], true);

        if (!$isTecnologia) {
            abort(403, 'Solo el área de Tecnologías de Información puede acceder.');
        }

        return $next($request);
    }
}
