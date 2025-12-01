<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserDocument extends Model
{
    protected $table = 'cms_user_documents';

    // ✅ Añadido 'meta' a $fillable
   protected $fillable = [
    'user_id',
    'document_type',
    'sender',
    'institution',
    'subject',
    'content',
    'file_path',
    'file_name',
    'file_type',
    'file_size',
    'document_code',
    'meta' // ✅ Correcto
];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}