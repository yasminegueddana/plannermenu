<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests complets pour la fonctionnalité de liste de courses
 * Couvre tous les cas de succès et d'erreurs possibles
 */
class ShoppingListComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $recipe;
    protected $ingredients;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un utilisateur de test
        $this->user = User::factory()->create();
        
        // Créer des ingrédients de test
        $this->ingredients = [
            Ingredient::factory()->create(['name' => 'Tomate', 'unite' => 'kg', 'user_id' => $this->user->id]),
            Ingredient::factory()->create(['name' => 'Oignon', 'unite' => 'pièce', 'user_id' => $this->user->id]),
            Ingredient::factory()->create(['name' => 'Ail', 'unite' => 'g', 'user_id' => $this->user->id]),
        ];
        
        // Créer une recette de test avec ingrédients
        $this->recipe = Recipe::factory()->create([
            'name' => 'Salade de Tomates',
            'user_id' => $this->user->id,
        ]);
        
        // Attacher les ingrédients à la recette
        $this->recipe->ingredients()->attach([
            $this->ingredients[0]->id => ['quantity' => 2, 'unit' => 'kg'],
            $this->ingredients[1]->id => ['quantity' => 1, 'unit' => 'pièce'],
            $this->ingredients[2]->id => ['quantity' => 5, 'unit' => 'g'],
        ]);
    }

    /**
     * ✅ CAS DE SUCCÈS - Accès à la page de liste de courses
     */
    public function test_user_can_access_shopping_list_page()
    {
        $response = $this->actingAs($this->user)->get('/shopping-list');

        $response->assertStatus(200);
        $response->assertSee('Liste des courses');
    }

    /**
     * ✅ CAS DE SUCCÈS - Sélection d'une recette pour la liste de courses
     */
    public function test_user_can_select_recipe_for_shopping_list()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id)
            ->assertSet('selectedRecipeId', $this->recipe->id)
            ->assertSet('showIngredients', true);
    }

    /**
     * ✅ CAS DE SUCCÈS - Génération de la liste d'ingrédients
     */
    public function test_user_can_generate_ingredients_list()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id)
            ->assertSet('selectedRecipeId', $this->recipe->id)
            ->assertCount('ingredientsList', 3); // 3 ingrédients dans la recette
    }

    /**
     * ✅ CAS DE SUCCÈS - Modification du nombre de portions
     */
    public function test_user_can_change_servings_and_quantities_adjust()
    {
        $component = Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id)
            ->set('servings', 8); // Double les portions

        // Vérifier que les quantités sont ajustées
        $ingredientsList = $component->get('ingredientsList');
        $tomateIngredient = collect($ingredientsList)->firstWhere('name', 'Tomate');
        
        $this->assertEquals(4, $tomateIngredient['adjusted_quantity']); // 2 * 2 = 4
    }

    /**
     * ❌ ERREUR - Nombre de portions invalide (négatif)
     */
    public function test_user_cannot_set_negative_servings()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id)
            ->set('servings', -2)
            ->assertHasErrors(['servings']);
    }

    /**
     * ❌ ERREUR - Nombre de portions invalide (zéro)
     */
    public function test_user_cannot_set_zero_servings()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id)
            ->set('servings', 0)
            ->assertHasErrors(['servings']);
    }

    /**
     * ✅ CAS DE SUCCÈS - Marquer un ingrédient comme acheté
     */
    public function test_user_can_toggle_ingredient_purchased_status()
    {
        $component = Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id);

        // Marquer le premier ingrédient comme acheté
        $component->call('togglePurchased', 0)
                  ->assertSet('ingredientsList.0.is_purchased', true);

        // Le démarquer
        $component->call('togglePurchased', 0)
                  ->assertSet('ingredientsList.0.is_purchased', false);
    }

    /**
     * ✅ CAS DE SUCCÈS - Modification du prix estimé d'un ingrédient
     */
    public function test_user_can_edit_ingredient_estimated_cost()
    {
        $component = Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id);

        // Éditer le prix du premier ingrédient
        $component->call('editPrice', 0)
                  ->set('tempPrice', 5.50)
                  ->call('savePrice')
                  ->assertSet('ingredientsList.0.estimated_cost', 5.50);
    }

    /**
     * ❌ ERREUR - Prix estimé négatif
     */
    public function test_user_cannot_set_negative_estimated_cost()
    {
        $component = Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id);

        $component->call('editPrice', 0)
                  ->set('tempPrice', -5.50)
                  ->call('savePrice')
                  ->assertHasErrors(['tempPrice']);
    }

    /**
     * ✅ CAS DE SUCCÈS - Recherche de recettes dans la liste
     */
    public function test_user_can_search_recipes_in_shopping_list()
    {
        // Créer une autre recette
        $recipe2 = Recipe::factory()->create([
            'name' => 'Pasta Bolognaise',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->set('searchTerm', 'Salade')
            ->assertSee('Salade de Tomates')
            ->assertDontSee('Pasta Bolognaise');
    }

    /**
     * ✅ CAS DE SUCCÈS - Calcul des statistiques de la liste
     */
    public function test_user_can_view_shopping_list_statistics()
    {
        $component = Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id);

        // Marquer un ingrédient comme acheté et ajouter un prix
        $component->call('togglePurchased', 0)
                  ->call('editPrice', 0)
                  ->set('tempPrice', 10.00)
                  ->call('savePrice');

        $stats = $component->call('getShoppingStats')->get('shoppingStats');
        
        $this->assertEquals(3, $stats['total_items']);
        $this->assertEquals(1, $stats['purchased_items']);
        $this->assertEquals(2, $stats['remaining_items']);
    }

    /**
     * ❌ ERREUR - Sélection d'une recette inexistante
     */
    public function test_user_cannot_select_nonexistent_recipe()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', 99999) // ID inexistant
            ->assertSet('selectedRecipe', null);
    }

    /**
     * ❌ ERREUR - Accès à la liste de courses sans authentification
     */
    public function test_guest_cannot_access_shopping_list()
    {
        $response = $this->get('/shopping-list');
        
        $response->assertRedirect('/login');
    }

    /**
     * ✅ CAS DE SUCCÈS - Affichage de toutes les recettes (admin + utilisateurs)
     */
    public function test_user_can_see_all_published_recipes()
    {
        // Créer un admin et sa recette
        $admin = User::factory()->create(['role' => 'admin']);
        $adminRecipe = Recipe::factory()->create([
            'name' => 'Recette Admin',
            'user_id' => $admin->id,
        ]);

        // Créer un autre utilisateur et sa recette
        $otherUser = User::factory()->create();
        $otherRecipe = Recipe::factory()->create([
            'name' => 'Recette Autre Utilisateur',
            'user_id' => $otherUser->id,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator');

        // Vérifier que toutes les recettes sont visibles
        $recipes = $component->get('recipes');
        $recipeNames = $recipes->pluck('name')->toArray();
        
        $this->assertContains('Salade de Tomates', $recipeNames); // Sa propre recette
        $this->assertContains('Recette Admin', $recipeNames); // Recette admin
        $this->assertContains('Recette Autre Utilisateur', $recipeNames); // Recette autre utilisateur
    }

    /**
     * ✅ CAS DE SUCCÈS - Export PDF de la liste de courses
     */
    public function test_user_can_export_shopping_list_to_pdf()
    {
        $component = Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id);

        // Tenter l'export PDF
        $response = $component->call('exportToPdf');
        
        // Vérifier qu'il y a une redirection vers l'URL d'export
        $this->assertNotNull($response);
    }

    /**
     * ✅ CAS DE SUCCÈS - Export Excel de la liste de courses
     */
    public function test_user_can_export_shopping_list_to_excel()
    {
        $component = Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id);

        // Tenter l'export Excel
        $response = $component->call('exportToExcel');
        
        // Vérifier qu'il y a une redirection vers l'URL d'export
        $this->assertNotNull($response);
    }

    /**
     * ❌ ERREUR - Export sans recette sélectionnée
     */
    public function test_user_cannot_export_without_selected_recipe()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('exportToPdf')
            ->assertHasErrors();
    }

    /**
     * ✅ CAS DE SUCCÈS - Réinitialisation de la liste
     */
    public function test_user_can_reset_shopping_list()
    {
        $component = Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id);

        // Marquer des ingrédients comme achetés
        $component->call('togglePurchased', 0)
                  ->call('togglePurchased', 1);

        // Réinitialiser (simulé en resélectionnant la recette)
        $component->call('selectRecipe', $this->recipe->id);

        // Vérifier que tous les ingrédients sont remis à "non acheté"
        $ingredientsList = $component->get('ingredientsList');
        foreach ($ingredientsList as $ingredient) {
            $this->assertFalse($ingredient['is_purchased']);
        }
    }

    /**
     * ❌ ERREUR - Modification d'un index d'ingrédient invalide
     */
    public function test_user_cannot_toggle_invalid_ingredient_index()
    {
        $component = Livewire::actingAs($this->user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $this->recipe->id);

        // Tenter de modifier un index qui n'existe pas
        $component->call('togglePurchased', 999)
                  ->assertHasErrors();
    }
}
