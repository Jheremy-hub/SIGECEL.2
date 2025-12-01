<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'cms_user_roles';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role',
        'hierarchy_level',
        'parent_role_id',
        'assigned_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function parentRole()
    {
        return $this->belongsTo(self::class, 'parent_role_id');
    }

    public function childRoles()
    {
        return $this->hasMany(self::class, 'parent_role_id');
    }
}
