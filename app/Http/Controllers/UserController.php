<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserMessage;

class UserController extends Controller
{
    public function dashboard()
    {
        // Obtener el usuario autenticado con sus relaciones
        $user = User::with([
            'role', 
            'documents',
            'receivedMessages' => function($query) {
                $query->where('is_read', 0);
            }
        ])->find(Auth::id());
        
        if (!$user) {
            Auth::logout();
            return redirect('/login')->withErrors(['error' => 'Usuario no encontrado']);
        }

        // Calcular estadísticas optimizadas
        $stats = [
            'total_users' => User::where('id_estado', 1)->count(),
            'unread_messages' => $user->receivedMessages->count(),
            'sent_messages' => $user->sentMessages()->count(),
            'documents_count' => $user->documents->count()
        ];

        return view('dashboard', compact('user', 'stats'));
    }
}