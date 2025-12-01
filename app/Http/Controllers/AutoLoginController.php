<?php

namespace App\Http\Controllers;

use App\Models\AutoLoginToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AutoLoginController extends Controller
{
    /**
     * Procesar auto-login desde sistema principal
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processAutoLogin(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            Log::warning('Auto-login: Token no proporcionado');
            return redirect()->route('login')->withErrors([
                'email' => 'Token de auto-login no válido. Por favor, inicie sesión manualmente.',
            ]);
        }

        // Intentar consumir el token
        $user = AutoLoginToken::consumeToken(
            $token,
            $request->ip(),
            $request->userAgent()
        );

        if (!$user) {
            Log::warning('Auto-login: Token inválido o expirado', ['token' => substr($token, 0, 10) . '...']);
            return redirect()->route('login')->withErrors([
                'email' => 'Token de auto-login expirado o inválido. Por favor, inicie sesión manualmente.',
            ]);
        }

        // Verificar que el usuario esté activo
        if ($user->id_estado != 1) {
            Log::warning('Auto-login: Usuario inactivo', ['user_id' => $user->id]);
            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ]);
        }

        // Autenticar al usuario
        Auth::login($user, true); // true = remember me
        $request->session()->regenerate();

        Log::info('Auto-login exitoso', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Redirigir al m\xf3dulo SIGE
        return redirect()->intended(route('sige.index'));
    }

    /**
     * Generar token para auto-login (llamado desde sistema principal)
     * Este endpoint debe estar protegido con una API key compartida
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateToken(Request $request)
    {
        // Validar API key compartida (seguridad)
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');
        
        if ($apiKey !== config('app.sso_api_key')) {
            Log::warning('Auto-login: API key inválida');
            return response()->json([
                'error' => 'No autorizado'
            ], 401);
        }

        // Validar parámetros
        $request->validate([
            'user_id' => 'required|integer|exists:cms_users,id',
        ]);

        $userId = $request->input('user_id');
        $ipAddress = $request->input('ip_address') ?? $request->ip();
        $userAgent = $request->input('user_agent') ?? $request->userAgent();

        // Generar token
        $token = AutoLoginToken::generateToken($userId, $ipAddress, $userAgent);

        Log::info('Token de auto-login generado', [
            'user_id' => $userId,
            'ip' => $ipAddress,
        ]);

        // Retornar URL de auto-login
        $autoLoginUrl = url('/auto-login?token=' . $token);

        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => $autoLoginUrl,
            'expires_in' => 60, // segundos
        ]);
    }
}
