<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;
use Tests\TestCase;

class UserDeactivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivated_user_cannot_login()
    {
        // Créer un utilisateur désactivé
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);

        // Tenter de se connecter
        $component = Volt::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password');

        $component->call('login');

        // Vérifier que la connexion a échoué avec le bon message
        $component->assertHasErrors(['form.email']);
        $this->assertGuest();
    }

    public function test_active_user_can_login()
    {
        // Créer un utilisateur actif
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // Se connecter
        $component = Volt::test('pages.auth.login')
            ->set('form.email', 'test@example.com')
            ->set('form.password', 'password');

        $component->call('login');

        // Vérifier que la connexion a réussi
        $component->assertHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_is_logged_out_when_deactivated_during_session()
    {
        // Créer un utilisateur actif
        $user = User::factory()->create([
            'is_active' => true,
        ]);

        // Se connecter
        $this->actingAs($user);
        $this->assertAuthenticated();

        // Désactiver l'utilisateur
        $user->update(['is_active' => false]);

        // Tenter d'accéder à une page protégée
        $response = $this->get('/dashboard');

        // Vérifier que l'utilisateur est redirigé vers la page de connexion
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_admin_can_toggle_user_status()
    {
        // Créer un admin
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Créer un utilisateur normal
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
        ]);

        // Se connecter en tant qu'admin
        $this->actingAs($admin);

        // Désactiver l'utilisateur via le UserManager
        $component = \Livewire\Livewire::test(\App\Livewire\UserManager::class);
        $component->call('toggleUserStatus', $user->id);

        // Vérifier que l'utilisateur a été désactivé
        $this->assertFalse($user->fresh()->is_active);

        // Réactiver l'utilisateur
        $component->call('toggleUserStatus', $user->id);

        // Vérifier que l'utilisateur a été réactivé
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_themselves()
    {
        // Créer un admin
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Se connecter en tant qu'admin
        $this->actingAs($admin);

        // Tenter de se désactiver
        $component = \Livewire\Livewire::test(\App\Livewire\UserManager::class);
        $component->call('toggleUserStatus', $admin->id);

        // Vérifier que l'admin est toujours actif
        $this->assertTrue($admin->fresh()->is_active);

        // Vérifier qu'un message d'erreur a été affiché
        $component->assertSet('error', 'Vous ne pouvez pas modifier votre propre statut.');
    }
}
