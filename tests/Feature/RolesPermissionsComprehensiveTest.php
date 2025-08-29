<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests complets pour la gestion des rôles et permissions
 * Critères d'acceptation :
 * ■ Un utilisateur standard ne peut pas accéder aux pages admin
 * ■ Un admin modifie les données globales
 */
class RolesPermissionsComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un administrateur
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        // Créer un utilisateur standard
        $this->user = User::factory()->create(['role' => 'user']);
    }

    /**
     * ✅ CAS DE SUCCÈS - Admin peut accéder aux pages admin
     */
    public function test_admin_can_access_admin_pages()
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        
        $response->assertStatus(200);
    }

    /**
     * ❌ ERREUR - Utilisateur standard ne peut pas accéder aux pages admin
     */
    public function test_regular_user_cannot_access_admin_pages()
    {
        $response = $this->actingAs($this->user)->get('/admin');
        
        // Devrait être redirigé ou recevoir une erreur 403
        $this->assertTrue(
            $response->status() === 403 || 
            $response->isRedirect()
        );
    }

    /**
     * ❌ ERREUR - Invité ne peut pas accéder aux pages admin
     */
    public function test_guest_cannot_access_admin_pages()
    {
        $response = $this->get('/admin');
        
        $response->assertRedirect('/login');
    }

    /**
     * ✅ CAS DE SUCCÈS - Admin peut modifier les données globales (ingrédients)
     */
    public function test_admin_can_modify_global_ingredients()
    {
        $globalIngredient = Ingredient::factory()->create([
            'name' => 'Sel Global',
            'user_id' => null, // Ingrédient global
        ]);

        $this->assertTrue($globalIngredient->canEdit($this->admin));
    }

    /**
     * ❌ ERREUR - Utilisateur ne peut pas modifier les données globales
     */
    public function test_regular_user_cannot_modify_global_ingredients()
    {
        $globalIngredient = Ingredient::factory()->create([
            'name' => 'Poivre Global',
            'user_id' => null, // Ingrédient global
        ]);

        $this->assertFalse($globalIngredient->canEdit($this->user));
    }

    /**
     * ✅ CAS DE SUCCÈS - Admin peut modifier les recettes de tous les utilisateurs
     */
    public function test_admin_can_modify_any_user_recipe()
    {
        $userRecipe = Recipe::factory()->create([
            'name' => 'Recette Utilisateur',
            'user_id' => $this->user->id,
        ]);

        $this->assertTrue($userRecipe->canEdit($this->admin));
    }

    /**
     * ❌ ERREUR - Utilisateur ne peut pas modifier les recettes d'autrui
     */
    public function test_regular_user_cannot_modify_other_user_recipe()
    {
        $otherUser = User::factory()->create();
        $otherRecipe = Recipe::factory()->create([
            'name' => 'Recette Autre',
            'user_id' => $otherUser->id,
        ]);

        $this->assertFalse($otherRecipe->canEdit($this->user));
    }

    /**
     * ✅ CAS DE SUCCÈS - Utilisateur peut modifier ses propres données
     */
    public function test_user_can_modify_own_data()
    {
        $ownRecipe = Recipe::factory()->create([
            'name' => 'Ma Recette',
            'user_id' => $this->user->id,
        ]);

        $this->assertTrue($ownRecipe->canEdit($this->user));
    }

    /**
     * ✅ CAS DE SUCCÈS - Admin peut supprimer les données de tous
     */
    public function test_admin_can_delete_any_user_data()
    {
        $userRecipe = Recipe::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->assertTrue($userRecipe->canDelete($this->admin));
    }

    /**
     * ❌ ERREUR - Utilisateur ne peut pas supprimer les données d'autrui
     */
    public function test_regular_user_cannot_delete_other_user_data()
    {
        $otherUser = User::factory()->create();
        $otherRecipe = Recipe::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $this->assertFalse($otherRecipe->canDelete($this->user));
    }

    /**
     * ✅ CAS DE SUCCÈS - Vérification du rôle admin
     */
    public function test_admin_role_is_correctly_identified()
    {
        $this->assertTrue($this->admin->isAdmin());
        $this->assertFalse($this->user->isAdmin());
    }

    /**
     * ❌ ERREUR - Utilisateur standard ne peut pas accéder à la gestion des utilisateurs
     */
    public function test_regular_user_cannot_access_user_management()
    {
        $response = $this->actingAs($this->user)->get('/admin/users');
        
        $this->assertTrue(
            $response->status() === 403 || 
            $response->isRedirect()
        );
    }

    /**
     * ✅ CAS DE SUCCÈS - Admin peut accéder à la gestion des utilisateurs
     */
    public function test_admin_can_access_user_management()
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');
        
        $response->assertStatus(200);
    }

    /**
     * ❌ ERREUR - Utilisateur ne peut pas changer le rôle d'autres utilisateurs
     */
    public function test_regular_user_cannot_change_user_roles()
    {
        $otherUser = User::factory()->create(['role' => 'user']);

        // Simuler une tentative de changement de rôle
        $response = $this->actingAs($this->user)
                         ->patch("/admin/users/{$otherUser->id}/role", [
                             'role' => 'admin'
                         ]);

        $this->assertTrue(
            $response->status() === 403 || 
            $response->status() === 404 ||
            $response->isRedirect()
        );
    }

    /**
     * ✅ CAS DE SUCCÈS - Admin peut changer le rôle des utilisateurs
     */
    public function test_admin_can_change_user_roles()
    {
        $targetUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($this->admin)
                         ->patch("/admin/users/{$targetUser->id}/role", [
                             'role' => 'admin'
                         ]);

        // Vérifier que la requête est acceptée (200, 302, etc.)
        $this->assertTrue($response->status() < 400);
    }

    /**
     * ❌ ERREUR - Utilisateur ne peut pas désactiver d'autres comptes
     */
    public function test_regular_user_cannot_deactivate_other_accounts()
    {
        $otherUser = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->user)
                         ->patch("/admin/users/{$otherUser->id}/status", [
                             'status' => 'inactive'
                         ]);

        $this->assertTrue(
            $response->status() === 403 || 
            $response->status() === 404 ||
            $response->isRedirect()
        );
    }

    /**
     * ✅ CAS DE SUCCÈS - Admin peut désactiver des comptes
     */
    public function test_admin_can_deactivate_user_accounts()
    {
        $targetUser = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
                         ->patch("/admin/users/{$targetUser->id}/status", [
                             'status' => 'inactive'
                         ]);

        $this->assertTrue($response->status() < 400);
    }

    /**
     * ❌ ERREUR - Compte inactif ne peut pas se connecter
     */
    public function test_inactive_user_cannot_login()
    {
        $inactiveUser = User::factory()->create([
            'status' => 'inactive',
            'email' => 'inactive@example.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * ✅ CAS DE SUCCÈS - Middleware protège correctement les routes admin
     */
    public function test_admin_middleware_protects_routes()
    {
        $adminRoutes = [
            '/admin',
            '/admin/users',
            '/admin/dashboard',
        ];

        foreach ($adminRoutes as $route) {
            // Test avec utilisateur standard
            $response = $this->actingAs($this->user)->get($route);
            $this->assertTrue(
                $response->status() === 403 || 
                $response->isRedirect(),
                "Route {$route} should be protected from regular users"
            );

            // Test avec admin
            $response = $this->actingAs($this->admin)->get($route);
            $this->assertTrue(
                $response->status() === 200,
                "Route {$route} should be accessible to admin"
            );
        }
    }

    /**
     * ✅ CAS DE SUCCÈS - Permissions en cascade (admin hérite de toutes les permissions)
     */
    public function test_admin_inherits_all_permissions()
    {
        // Admin peut faire tout ce qu'un utilisateur peut faire
        $adminRecipe = Recipe::factory()->create(['user_id' => $this->admin->id]);
        $adminIngredient = Ingredient::factory()->create(['user_id' => $this->admin->id]);

        $this->assertTrue($adminRecipe->canEdit($this->admin));
        $this->assertTrue($adminRecipe->canDelete($this->admin));
        $this->assertTrue($adminIngredient->canEdit($this->admin));
        $this->assertTrue($adminIngredient->canDelete($this->admin));
    }
}
