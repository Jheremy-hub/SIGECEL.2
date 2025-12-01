<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMessageLog extends Model
{
    protected $table = 'cms_user_message_logs';
    protected $fillable = ['message_id', 'user_id', 'action', 'details'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role()
    {
        return $this->hasOneThrough(UserRole::class, User::class, 'id', 'user_id', 'user_id', 'id');
    }
    
    
}
