<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

$email = 'jheremitaquire@gmail.com';
$password = '12345678';
$name = 'Jheremy';
$apellidos = 'Taquiredo';

// Check if user exists
$user = User::where('email', $email)->first();

if (!$user) {
    echo "User not found. Creating new user...\n";
    $user = new User();
    $user->email = $email;
    $user->name = $name;
    $user->apellidos = $apellidos;
    $user->id_estado = 1;
} else {
    echo "User found. Updating password...\n";
}

$user->password = Hash::make($password);
$user->save();

// Assign Role
$role = UserRole::where('user_id', $user->id)->first();
if (!$role) {
    $role = new UserRole();
    $role->user_id = $user->id;
}
$role->role = 'Tecnologias de Informacion';
$role->hierarchy_level = 2;
$role->assigned_at = now();
$role->save();

echo "SUCCESS: User '$email' is ready with password '$password'.\n";
