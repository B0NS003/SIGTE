<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolSeeder::class);
    }

    public function test_login_page_is_shown(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Iniciar sesión');
    }

    public function test_operador_can_login_and_see_own_panel(): void
    {
        $rolId = Rol::query()->where('nombre', Rol::OPERADOR)->value('id');
        $user = User::factory()->create([
            'name' => 'Operador Demo',
            'email' => 'operador@sigte.local',
            'password' => 'password',
            'rol_id' => $rolId,
            'role' => Rol::OPERADOR,
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('mockups.panel', Rol::OPERADOR));

        $this->assertAuthenticatedAs($user);
        $this->get(route('mockups.panel', Rol::OPERADOR))
            ->assertOk()
            ->assertSee('Operador Demo');
    }

    public function test_invalid_credentials_show_error(): void
    {
        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'operador@sigte.local',
                'password' => 'incorrecta',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guest_cannot_see_panel(): void
    {
        $this->get(route('mockups.panel', Rol::OPERADOR))->assertRedirect(route('login'));
    }
}