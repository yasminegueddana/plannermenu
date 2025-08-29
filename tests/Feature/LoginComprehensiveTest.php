<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Tests complets pour la fonctionnalité de connexion (Login)
 * Couvre tous les cas de succès et d'erreurs possibles
 */
class LoginComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ✅ CAS DE SUCCÈS - Login avec des identifiants valides
     */
    public function test_user_can_login_with_valid_credentials()
    {
        // Créer un utilisateur de test
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // Tenter de se connecter
        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        // Vérifications
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * ❌ ERREUR - Email inexistant
     */
    public function test_user_cannot_login_with_nonexistent_email()
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Mot de passe incorrect
     */
    public function test_user_cannot_login_with_wrong_password()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Champs vides
     */
    public function test_user_cannot_login_with_empty_fields()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Email vide seulement
     */
    public function test_user_cannot_login_with_empty_email()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Mot de passe vide seulement
     */
    public function test_user_cannot_login_with_empty_password()
    {
        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Format d'email invalide
     */
    public function test_user_cannot_login_with_invalid_email_format()
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email-format',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Email avec espaces
     */
    public function test_user_cannot_login_with_email_containing_spaces()
    {
        $response = $this->post('/login', [
            'email' => 'john @example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Tentative d'accès au dashboard sans authentification
     */
    public function test_user_cannot_access_dashboard_without_authentication()
    {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Tentative d'accès aux recettes sans authentification
     */
    public function test_user_cannot_access_recipes_without_authentication()
    {
        $response = $this->get('/recipes');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Tentative d'accès aux ingrédients sans authentification
     */
    public function test_user_cannot_access_ingredients_without_authentication()
    {
        $response = $this->get('/ingredients');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Tentative d'accès au menu planner sans authentification
     */
    public function test_user_cannot_access_menu_planner_without_authentication()
    {
        $response = $this->get('/menu-planner');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Tentative d'accès à la liste de courses sans authentification
     */
    public function test_user_cannot_access_shopping_list_without_authentication()
    {
        $response = $this->get('/shopping-list');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * ✅ CAS DE SUCCÈS - Page de login accessible
     */
    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');
        
        $response->assertStatus(200);
        $response->assertSee('Se connecter');
    }

    /**
     * ✅ CAS DE SUCCÈS - Redirection après login réussi
     */
    public function test_user_redirected_to_dashboard_after_successful_login()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
    }

    /**
     * ❌ ERREUR - Mot de passe trop court (si validation côté serveur)
     */
    public function test_user_cannot_login_with_very_short_password()
    {
        $response = $this->post('/login', [
            'email' => 'john@example.com',
            'password' => '12',
        ]);

        // Peut avoir une erreur de validation ou d'authentification
        $this->assertTrue(
            $response->getSession()->hasErrors('password') || 
            $response->getSession()->hasErrors('email')
        );
        $this->assertGuest();
    }

    /**
     * ❌ ERREUR - Tentative de login avec des caractères spéciaux malveillants
     */
    public function test_user_cannot_login_with_malicious_input()
    {
        $response = $this->post('/login', [
            'email' => '<script>alert("hack")</script>@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}
