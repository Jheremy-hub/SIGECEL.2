<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutoLoginController;
use App\Http\Controllers\BirthdayReportController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Entrada p\xfablica: redirige al m\xf3dulo p\xfablico de SIGECEL (seguimiento)
Route::get('/', function () {
    return redirect()->route('tracking.public');
});

// Autenticacion
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth');

// Auto-Login SSO (para sistema principal)
Route::get('/auto-login', [AutoLoginController::class, 'processAutoLogin'])->name('auto-login');
Route::post('/api/generate-auto-login-token', [AutoLoginController::class, 'generateToken'])->name('api.generate-token');

Route::middleware(['auth'])->group(function () {
    Route::prefix('documents')->group(function () {
        Route::get('/create', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('/', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('/{id}', [DocumentController::class, 'show'])->name('documents.show');
        Route::get('/{id}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');
        Route::post('/get-by-type', [DocumentController::class, 'getDocumentsByType'])->name('documents.getDocumentsByType');
    });

    // Mensajeria
    Route::prefix('messages')->group(function () {
        Route::get('/create', [MessageController::class, 'create'])->name('messages.create');
        Route::post('/', [MessageController::class, 'store'])->name('messages.store');
        Route::get('/inbox', [MessageController::class, 'inbox'])->name('messages.inbox');
        Route::get('/sent', [MessageController::class, 'sent'])->name('messages.sent');
        Route::get('/{id}', [MessageController::class, 'show'])->name('messages.show');
        Route::get('/{id}/report', [MessageController::class, 'report'])->name('messages.report');
        Route::get('/{id}/download', [MessageController::class, 'download'])->name('messages.download');
        Route::delete('/{id}', [MessageController::class, 'destroy'])->name('messages.destroy');

        Route::post('/{id}/reply', [MessageController::class, 'reply'])->name('messages.reply');
        Route::get('/{id}/reply/{logId}/download', [MessageController::class, 'downloadReply'])->name('messages.reply.download');

        Route::post('/{id}/read', [MessageController::class, 'markAsRead'])->name('messages.read');
        Route::post('/{id}/forward', [MessageController::class, 'forward'])->name('messages.forward');
        Route::post('/{id}/status', [MessageController::class, 'updateStatus'])->name('messages.status');
        Route::post('/{id}/boss/approve', [MessageController::class, 'approveAsBoss'])->name('messages.boss.approve');
        Route::post('/{id}/boss/archive', [MessageController::class, 'archiveAsBoss'])->name('messages.boss.archive');
    });

    // Usuarios y roles (solo TI)
    Route::prefix('users')->middleware('ti-only')->group(function () {
        Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
        Route::post('/register', [RegisterController::class, 'register'])->name('register');

        Route::get('/roles', [UserManagementController::class, 'roles'])->name('users.roles');
        Route::post('/roles', [UserManagementController::class, 'storeRole'])->name('users.roles.store');
        Route::delete('/roles', [UserManagementController::class, 'deleteRole'])->name('users.roles.delete');
        Route::get('/roles/hierarchy', [UserManagementController::class, 'roleHierarchy'])->name('users.roles.hierarchy');
        Route::post('/roles/hierarchy', [UserManagementController::class, 'updateRoleHierarchy'])->name('users.roles.hierarchy.update');

        Route::get('/', [UserManagementController::class, 'listUsers'])->name('users.list');
        Route::get('/{id}/edit', [UserManagementController::class, 'editUser'])->name('users.edit');
        Route::put('/{id}', [UserManagementController::class, 'updateUser'])->name('users.update');
        Route::delete('/{id}', [UserManagementController::class, 'deleteUser'])->name('users.delete');
    });

    // SIGE unificado
    Route::get('/sige', [MessageController::class, 'sige'])->name('sige.index');
    Route::get('/sige/compose-form', [MessageController::class, 'getComposeForm'])->name('sige.compose.form');
});

// Dashboard (solo para Tecnologías de Información)
Route::get('/dashboard', [UserController::class, 'dashboard'])
    ->middleware(['auth', 'ti-only'])
    ->name('dashboard');

// Reporte cumpleaños (solo para Tecnologías de Información)
Route::get('/reporte-cumpleanos', [BirthdayReportController::class, 'index'])
    ->middleware(['auth', 'ti-only'])
    ->name('reports.birthdays');
Route::post('/reporte-cumpleanos/saludar', [BirthdayReportController::class, 'sendGreeting'])
    ->middleware(['auth', 'ti-only'])
    ->name('reports.birthdays.greet');
Route::post('/reporte-cumpleanos/imagen', [BirthdayReportController::class, 'updateBackgroundImage'])
    ->middleware(['auth', 'ti-only'])
    ->name('reports.birthdays.image');
Route::post('/reporte-cumpleanos/texto', [BirthdayReportController::class, 'updateGreetingText'])
    ->middleware(['auth', 'ti-only'])
    ->name('reports.birthdays.text');

// Seguimiento publico
Route::get('/seguimiento', [MessageController::class, 'publicTracking'])->name('tracking.public');

// Debug rutas
Route::get('/debug-routes', function () {
    $routes = [];
    foreach (Route::getRoutes() as $route) {
        $routes[] = [
            'method' => implode('|', $route->methods()),
            'uri' => $route->uri(),
            'name' => $route->getName(),
            'action' => $route->getActionName(),
        ];
    }
    return response()->json($routes);
});

Route::get('/test-mail', function () {
    try {
        \Mail::raw('Prueba de correo desde SIGECEL', function ($message) {
            $message->to('tu-correo@ejemplo.com') // Cambia esto por tu correo de prueba
                    ->subject('Prueba de correo');
        });

        return response()->json(['ok' => true, 'message' => 'Correo enviado exitosamente']);
    } catch (\Exception $e) {
        return response()->json([
            'ok' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});
