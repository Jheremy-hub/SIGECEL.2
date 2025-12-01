<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            // Si no está autenticado, redirigir al sistema principal CEL
            return redirect('https://administrativo.cel.org.pe/admin')
                ->with('error', 'Por favor, acceda a SIGECEL desde el sistema principal CEL.');
        }

        return $next($request);
    }
}
