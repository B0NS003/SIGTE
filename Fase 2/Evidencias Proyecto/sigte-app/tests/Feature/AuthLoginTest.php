<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_shown(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_tecnico_can_login_and_see_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'tecnico@sigte.local',
            'password' => 'password',
            'role' => User::ROLE_TECNICO,
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('panel.resumen'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('panel.resumen'))->assertOk()->assertSee('Resumen operativo');
    }

    public function test_guest_cannot_see_panel(): void
    {
        $this->get(route('panel.resumen'))->assertRedirect(route('login'));
    }
}