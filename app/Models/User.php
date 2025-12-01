<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $table = 'cms_users';

    protected $fillable = [
        'name', 
        'apellidos', 
        'cargo',
        'email', 
        'password',
        'photo',
        'celular',
        'id_cms_privileges',
        'id_cargo',
        'id_sede',
        'id_estado'
    ];

    protected $hidden = [
        'password', 
        'remember_token'
    ];

    // Relación con roles
    public function role()
    {
        return $this->hasOne(UserRole::class, 'user_id', 'id');
    }
    
    /**
     * Obtiene el rol del usuario o crea uno por defecto "Usuario" si no existe
     * 
     * @return UserRole
     */
    public function getRoleAttribute()
    {
        // Si ya existe la relación role cargada, usarla
        if ($this->relationLoaded('role') && $this->getRelation('role')) {
            return $this->getRelation('role');
        }
        
        // Cargar la relación si no está cargada
        if (!$this->relationLoaded('role')) {
            $this->load('role');
        }
        
        $existingRole = $this->getRelation('role');
        
        // Si existe, retornarlo
        if ($existingRole) {
            return $existingRole;
        }
        
        // Si no existe, crear un objeto virtual de rol "Usuario" (sin guardar en BD)
        $defaultRole = new UserRole();
        $defaultRole->role = 'Usuario';
        $defaultRole->user_id = $this->id;
        $defaultRole->hierarchy_level = 5;
        $defaultRole->exists = false; // Marcar como no persistido
        
        return $defaultRole;
    }
    
    // Relación con documentos
    public function documents()
    {
        return $this->hasMany(UserDocument::class, 'user_id', 'id');
    }

    // Nuevas relaciones para mensajes
    public function sentMessages()
    {
        return $this->hasMany(UserMessage::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(UserMessage::class, 'receiver_id');
    }

    public function unreadMessages()
    {
        return $this->receivedMessages()->where('is_read', 0);
    }

    // Método para verificar si es administrador
    public function isAdmin()
    {
        return $this->role && $this->role->role === 'Administrador';
    }

    // Método para verificar si está activo
    public function isActive()
    {
        return $this->id_estado == 1;
    }
}