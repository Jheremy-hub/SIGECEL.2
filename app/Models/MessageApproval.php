<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageApproval extends Model
{
    protected $table = 'cms_message_approvals';

    protected $fillable = [
        'message_id',
        'approver_id',
        'decision',
        'note',
        'decided_at',
    ];

    public $timestamps = true;

    public function message()
    {
        return $this->belongsTo(UserMessage::class, 'message_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
