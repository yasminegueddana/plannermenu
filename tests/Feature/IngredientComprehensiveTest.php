<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests complets pour la fonctionnalité de gestion des ingrédients
 * Couvre tous les cas de succès et d'erreurs possibles
 */
class IngredientComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un utilisateur normal
        $this->user = User::factory()->create(['role' => 'user']);
        
        // Créer un administrateur
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * ✅ CAS DE SUCCÈS - Création d'un ingrédient valide
     */
    public function test_user_can_create_ingredient()
    {
        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->set('name', 'Tomate')
            ->set('unite', 'kg')
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ingredients', [
            'name' => 'Tomate',
            'unite' => 'kg',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * ❌ ERREUR - Création d'ingrédient sans nom
     */
    public function test_user_cannot_create_ingredient_without_name()
    {
        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->set('name', '')
            ->set('unite', 'kg')
            ->call('store')
            ->assertHasErrors(['name']);
    }

    /**
     * ❌ ERREUR - Création d'ingrédient sans unité
     */
    public function test_user_cannot_create_ingredient_without_unit()
    {
        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->set('name', 'Tomate')
            ->set('unite', '')
            ->call('store')
            ->assertHasErrors(['unite']);
    }

    /**
     * ❌ ERREUR - Nom d'ingrédient trop long
     */
    public function test_user_cannot_create_ingredient_with_very_long_name()
    {
        $longName = str_repeat('a', 256); // Plus de 255 caractères

        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->set('name', $longName)
            ->set('unite', 'kg')
            ->call('store')
            ->assertHasErrors(['name']);
    }

    /**
     * ❌ ERREUR - Unité trop longue
     */
    public function test_user_cannot_create_ingredient_with_very_long_unit()
    {
        $longUnit = str_repeat('a', 51); // Plus de 50 caractères

        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->set('name', 'Tomate')
            ->set('unite', $longUnit)
            ->call('store')
            ->assertHasErrors(['unite']);
    }

    /**
     * ✅ CAS DE SUCCÈS - Admin peut créer un ingrédient global
     */
    public function test_admin_can_create_global_ingredient()
    {
        Livewire::actingAs($this->admin)
            ->test('ingredient-manager')
            ->set('name', 'Sel')
            ->set('unite', 'g')
            ->set('isGlobal', true)
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ingredients', [
            'name' => 'Sel',
            'unite' => 'g',
            'user_id' => null, // Ingrédient global
        ]);
    }

    /**
     * ❌ ERREUR - Utilisateur normal ne peut pas créer d'ingrédient global
     */
    public function test_regular_user_cannot_create_global_ingredient()
    {
        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->set('name', 'Poivre')
            ->set('unite', 'g')
            ->set('isGlobal', true)
            ->call('store')
            ->assertHasNoErrors();

        // L'ingrédient devrait être créé mais pas global
        $this->assertDatabaseHas('ingredients', [
            'name' => 'Poivre',
            'unite' => 'g',
            'user_id' => $this->user->id, // Pas global
        ]);
    }

    /**
     * ✅ CAS DE SUCCÈS - Modification d'un ingrédient propre
     */
    public function test_user_can_edit_own_ingredient()
    {
        $ingredient = Ingredient::factory()->create([
            'name' => 'Tomate',
            'unite' => 'kg',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->call('edit', $ingredient->id)
            ->set('name', 'Tomate Modifiée')
            ->set('unite', 'pièce')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'name' => 'Tomate Modifiée',
            'unite' => 'pièce',
        ]);
    }

    /**
     * ❌ ERREUR - Modification d'un ingrédient d'un autre utilisateur
     */
    public function test_user_cannot_edit_other_user_ingredient()
    {
        $otherUser = User::factory()->create();
        $ingredient = Ingredient::factory()->create([
            'name' => 'Oignon',
            'user_id' => $otherUser->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->call('edit', $ingredient->id)
            ->assertSet('editingId', null); // Ne devrait pas pouvoir éditer
    }

    /**
     * ✅ CAS DE SUCCÈS - Admin peut modifier n'importe quel ingrédient
     */
    public function test_admin_can_edit_any_ingredient()
    {
        $ingredient = Ingredient::factory()->create([
            'name' => 'Carotte',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test('ingredient-manager')
            ->call('edit', $ingredient->id)
            ->set('name', 'Carotte Admin')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'name' => 'Carotte Admin',
        ]);
    }

    /**
     * ✅ CAS DE SUCCÈS - Suppression d'un ingrédient propre
     */
    public function test_user_can_delete_own_ingredient()
    {
        $ingredient = Ingredient::factory()->create([
            'name' => 'Basilic',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->call('delete', $ingredient->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('ingredients', [
            'id' => $ingredient->id,
        ]);
    }

    /**
     * ❌ ERREUR - Suppression d'un ingrédient d'un autre utilisateur
     */
    public function test_user_cannot_delete_other_user_ingredient()
    {
        $otherUser = User::factory()->create();
        $ingredient = Ingredient::factory()->create([
            'name' => 'Persil',
            'user_id' => $otherUser->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->call('delete', $ingredient->id);

        // L'ingrédient devrait toujours exister
        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * ✅ CAS DE SUCCÈS - Recherche d'ingrédients
     */
    public function test_user_can_search_ingredients()
    {
        Ingredient::factory()->create([
            'name' => 'Tomate Rouge',
            'user_id' => $this->user->id,
        ]);
        
        Ingredient::factory()->create([
            'name' => 'Oignon Blanc',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->set('search', 'Tomate')
            ->assertSee('Tomate Rouge')
            ->assertDontSee('Oignon Blanc');
    }

    /**
     * ❌ ERREUR - Création d'ingrédient avec caractères malveillants
     */
    public function test_user_cannot_create_ingredient_with_malicious_content()
    {
        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->set('name', '<script>alert("hack")</script>')
            ->set('unite', '<script>alert("hack")</script>')
            ->call('store');

        // Vérifier que le script n'est pas stocké tel quel
        $ingredient = Ingredient::where('user_id', $this->user->id)->first();
        if ($ingredient) {
            $this->assertStringNotContainsString('<script>', $ingredient->name);
            $this->assertStringNotContainsString('<script>', $ingredient->unite);
        }
    }

    /**
     * ❌ ERREUR - Accès aux ingrédients sans authentification
     */
    public function test_guest_cannot_access_ingredients_page()
    {
        $response = $this->get('/ingredients');
        
        $response->assertRedirect('/login');
    }

    /**
     * ✅ CAS DE SUCCÈS - Création d'ingrédient avec unités standards
     */
    public function test_user_can_create_ingredient_with_standard_units()
    {
        $standardUnits = ['g', 'kg', 'ml', 'l', 'pièce', 'cuillère'];

        foreach ($standardUnits as $unit) {
            Livewire::actingAs($this->user)
                ->test('ingredient-manager')
                ->set('name', 'Ingrédient ' . $unit)
                ->set('unite', $unit)
                ->call('store')
                ->assertHasNoErrors();

            $this->assertDatabaseHas('ingredients', [
                'name' => 'Ingrédient ' . $unit,
                'unite' => $unit,
            ]);
        }
    }

    /**
     * ❌ ERREUR - Création d'ingrédient en double (même nom, même utilisateur)
     */
    public function test_user_cannot_create_duplicate_ingredient()
    {
        // Créer un premier ingrédient
        Ingredient::factory()->create([
            'name' => 'Tomate',
            'user_id' => $this->user->id,
        ]);

        // Tenter de créer le même
        Livewire::actingAs($this->user)
            ->test('ingredient-manager')
            ->set('name', 'Tomate')
            ->set('unite', 'kg')
            ->call('store');

        // Vérifier qu'il n'y a qu'un seul ingrédient "Tomate" pour cet utilisateur
        $count = Ingredient::where('name', 'Tomate')
                           ->where('user_id', $this->user->id)
                           ->count();
        $this->assertEquals(1, $count);
    }
}
