<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AutoLoginToken extends Model
{
    protected $table = 'cms_auto_login_tokens';
    
    public $timestamps = false;
    
    protected $fillable = [
        'token',
        'user_id',
        'ip_address',
        'user_agent',
        'expires_at',
        'used_at',
    ];

    protected $dates = [
        'expires_at',
        'used_at',
        'created_at',
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Generar un nuevo token de auto-login
     * 
     * @param int $userId ID del usuario
     * @param string|null $ipAddress IP del cliente
     * @param string|null $userAgent User agent del cliente
     * @return string Token generado
     */
    public static function generateToken($userId, $ipAddress = null, $userAgent = null)
    {
        // Limpiar tokens expirados antes de crear uno nuevo
        static::cleanupExpiredTokens();

        // Generar token único
        $token = hash('sha256', Str::random(40) . $userId . microtime());

        // Crear el registro
        static::create([
            'token' => $token,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? substr($userAgent, 0, 255) : null,
            'expires_at' => Carbon::now()->addMinutes(1), // Expira en 1 minuto
        ]);

        return $token;
    }

    /**
     * Validar y consumir un token
     * 
     * @param string $token Token a validar
     * @param string|null $ipAddress IP del cliente para validación
     * @param string|null $userAgent User agent para validación
     * @return User|null Usuario autenticado o null si el token es inválido
     */
    public static function consumeToken($token, $ipAddress = null, $userAgent = null)
    {
        $tokenRecord = static::where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$tokenRecord) {
            return null;
        }

        // Validación opcional de IP y User Agent para mayor seguridad
        // (comentado para compatibilidad con proxies/load balancers)
        /*
        if ($tokenRecord->ip_address && $tokenRecord->ip_address !== $ipAddress) {
            return null;
        }
        */

        // Marcar token como usado
        $tokenRecord->used_at = Carbon::now();
        $tokenRecord->save();

        // Retornar el usuario
        return $tokenRecord->user;
    }

    /**
     * Limpiar tokens expirados o usados (antiguos de más de 1 hora)
     */
    public static function cleanupExpiredTokens()
    {
        static::where(function ($query) {
            $query->where('expires_at', '<', Carbon::now())
                  ->orWhereNotNull('used_at');
        })
        ->where('created_at', '<', Carbon::now()->subHour())
        ->delete();
    }

    /**
     * Verificar si un token es válido (sin consumirlo)
     * 
     * @param string $token Token a verificar
     * @return bool
     */
    public static function isValid($token)
    {
        return static::where('token', $token)
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }
}
