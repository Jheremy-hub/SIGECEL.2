<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMessageForward extends Model
{
    protected $table = 'cms_user_message_forwards';
    protected $fillable = ['original_message_id', 'forwarded_message_id', 'forwarded_by', 'forwarded_to'];

    // Relación con el usuario que reenvió
    public function forwardedBy()
    {
        return $this->belongsTo(User::class, 'forwarded_by');
    }

    // Relación con el usuario a quien se reenvió
    public function forwardedTo()
    {
        return $this->belongsTo(User::class, 'forwarded_to');
    }
}