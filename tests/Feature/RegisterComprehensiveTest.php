<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests complets pour la fonctionnalité d'inscription (Register)
 * Couvre tous les cas de succès et d'erreurs possibles
 */
class RegisterComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ CAS DE SUCCÈS - Inscription avec des données valides
     */
    public function test_user_can_register_with_valid_data()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        $user = User::where('email', 'john@example.com')->first();
        $this->assertAuthenticatedAs($user);
    }

    /**
     * ❌ ERREUR - Email déjà existant
     */
    public function test_user_cannot_register_with_existing_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Format d'email invalide
     */
    public function test_user_cannot_register_with_invalid_email_format()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Mot de passe trop court
     */
    public function test_user_cannot_register_with_short_password()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Confirmation de mot de passe différente
     */
    public function test_user_cannot_register_with_mismatched_password_confirmation()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Champs obligatoires vides
     */
    public function test_user_cannot_register_with_empty_required_fields()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Nom vide
     */
    public function test_user_cannot_register_with_empty_name()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Email vide
     */
    public function test_user_cannot_register_with_empty_email()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Mot de passe vide
     */
    public function test_user_cannot_register_with_empty_password()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Nom trop long
     */
    public function test_user_cannot_register_with_very_long_name()
    {
        $longName = str_repeat('a', 256); // Plus de 255 caractères

        $response = $this->post('/register', [
            'name' => $longName,
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Email avec caractères spéciaux malveillants
     */
    public function test_user_cannot_register_with_malicious_email()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => '<script>alert("hack")</script>@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Nom avec caractères spéciaux malveillants
     */
    public function test_user_cannot_register_with_malicious_name()
    {
        $response = $this->post('/register', [
            'name' => '<script>alert("hack")</script>',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Le nom peut être accepté mais échappé, ou rejeté selon la validation
        // On vérifie qu'il n'y a pas d'injection de script
        if ($response->isRedirect()) {
            $user = User::where('email', 'john@example.com')->first();
            $this->assertNotNull($user);
            $this->assertStringNotContainsString('<script>', $user->name);
        } else {
            $response->assertSessionHasErrors(['name']);
        }
    }

    /**
     * ❌ ERREUR - Email avec domaine invalide
     */
    public function test_user_cannot_register_with_invalid_email_domain()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@invalid-domain',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Mot de passe sans confirmation
     */
    public function test_user_cannot_register_without_password_confirmation()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            // password_confirmation manquant
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    /**
     * ✅ CAS DE SUCCÈS - Page d'inscription accessible
     */
    public function test_register_page_is_accessible()
    {
        $response = $this->get('/register');
        
        $response->assertStatus(200);
        $response->assertSee('S\'inscrire');
    }

    /**
     * ✅ CAS DE SUCCÈS - Nom avec caractères accentués valides
     */
    public function test_user_can_register_with_accented_name()
    {
        $response = $this->post('/register', [
            'name' => 'François Müller',
            'email' => 'francois@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        
        $this->assertDatabaseHas('users', [
            'name' => 'François Müller',
            'email' => 'francois@example.com',
        ]);
    }

    /**
     * ✅ CAS DE SUCCÈS - Email avec sous-domaine
     */
    public function test_user_can_register_with_subdomain_email()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@mail.example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@mail.example.com',
        ]);
    }

    /**
     * ❌ ERREUR - Tentative d'inscription avec des espaces dans l'email
     */
    public function test_user_cannot_register_with_spaces_in_email()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}
