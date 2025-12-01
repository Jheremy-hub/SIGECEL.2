<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login()
    {
        // Crear un usuario de prueba
        $user = User::factory()->create([
            'email' => 'jtaquiredo@ucvvirtual.edu.pe',
            'password' => bcrypt('123456789'),
        ]);

        // Intentar iniciar sesión
        $response = $this->post('/login', [
            'email' => 'jtaquiredo@ucvvirtual.edu.pe',
            'password' => '123456789',
        ]);

        // Verificar redirección al dashboard
        $response->assertRedirect('/dashboard');

        // Confirmar que el usuario está autenticado
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function user_can_access_dashboard_with_correct_role()
    {
        // Crear un usuario con rol específico
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'email' => 'jtaquiredo@ucvvirtual.edu.pe',
            'password' => bcrypt('123456789'),
        ]);

        // Simular inicio de sesión
        $this->actingAs($user);

        // Acceder al dashboard
        $response = $this->get('/dashboard');

        // Verificar acceso permitido
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_send_documents()
    {
        // Crear un usuario
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Simular inicio de sesión
        $this->actingAs($user);

        // Enviar un documento
        $response = $this->post('/documents/send', [
            'document_type' => 'Oficio',
            'subject' => 'Prueba de documento',
            'content' => 'Contenido de prueba',
            'file_path' => 'documents/test.pdf',
        ]);

        // Verificar respuesta exitosa
        $response->assertStatus(200);

        // Confirmar que el documento está en la base de datos
        $this->assertDatabaseHas('cms_user_documents', [
            'subject' => 'Prueba de documento',
        ]);
    }
    /** @test */
    public function user_can_send_birthday_greetings()
    {
        // Crear un usuario
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Simular inicio de sesión
        $this->actingAs($user);
        $this->actingAs($user);

        // Enviar saludo de cumpleaños
        $response = $this->post('/reporte-cumpleanos/saludar', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        // Verificar respuesta exitosa
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_track_documents()
    {
        // Crear un usuario
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // Simular inicio de sesión
        $this->actingAs($user);
        // Simular inicio de sesión
        $this->actingAs($user);

        // Buscar un documento
        $response = $this->get('/tracking?code=0001');

        // Verificar respuesta exitosa
        $response->assertStatus(200);
    }
}