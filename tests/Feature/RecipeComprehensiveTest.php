<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests complets pour la fonctionnalité de gestion des recettes
 * Couvre tous les cas de succès et d'erreurs possibles
 */
class RecipeComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
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
    }

    /**
     * ✅ CAS DE SUCCÈS - Création d'une recette avec ingrédients
     */
    public function test_user_can_create_recipe_with_ingredients()
    {
        Storage::fake('public');

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', 'Salade de Tomates')
            ->set('description', 'Une délicieuse salade fraîche')
            ->set('instructions', 'Couper les tomates, ajouter l\'oignon...')
            ->set('selectedIngredients', [$this->ingredients[0]->id, $this->ingredients[1]->id])
            ->set('ingredientQuantities', [
                $this->ingredients[0]->id => 2,
                $this->ingredients[1]->id => 1,
            ])
            ->set('ingredientUnits', [
                $this->ingredients[0]->id => 'kg',
                $this->ingredients[1]->id => 'pièce',
            ])
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recipes', [
            'name' => 'Salade de Tomates',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * ❌ ERREUR - Création de recette sans nom
     */
    public function test_user_cannot_create_recipe_without_name()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', '')
            ->set('description', 'Une description')
            ->set('instructions', 'Des instructions')
            ->call('store')
            ->assertHasErrors(['name']);
    }

    /**
     * ❌ ERREUR - Création de recette sans description
     */
    public function test_user_cannot_create_recipe_without_description()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', 'Ma Recette')
            ->set('description', '')
            ->set('instructions', 'Des instructions')
            ->call('store')
            ->assertHasErrors(['description']);
    }

    /**
     * ❌ ERREUR - Création de recette sans instructions
     */
    public function test_user_cannot_create_recipe_without_instructions()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', 'Ma Recette')
            ->set('description', 'Une description')
            ->set('instructions', '')
            ->call('store')
            ->assertHasErrors(['instructions']);
    }

    /**
     * ❌ ERREUR - Nom de recette trop long
     */
    public function test_user_cannot_create_recipe_with_very_long_name()
    {
        $longName = str_repeat('a', 256); // Plus de 255 caractères

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', $longName)
            ->set('description', 'Une description')
            ->set('instructions', 'Des instructions')
            ->call('store')
            ->assertHasErrors(['name']);
    }

    /**
     * ✅ CAS DE SUCCÈS - Upload d'image valide
     */
    public function test_user_can_upload_recipe_image()
    {
        Storage::fake('public');
        
        $image = UploadedFile::fake()->image('recipe.jpg', 800, 600);

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', 'Recette avec Image')
            ->set('description', 'Une description')
            ->set('instructions', 'Des instructions')
            ->set('imageFile', $image)
            ->call('store')
            ->assertHasNoErrors();

        $recipe = Recipe::where('name', 'Recette avec Image')->first();
        $this->assertNotNull($recipe->image);
        Storage::disk('public')->assertExists($recipe->image);
    }

    /**
     * ❌ ERREUR - Upload de fichier non-image
     */
    public function test_user_cannot_upload_non_image_file()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', 'Test Recipe')
            ->set('description', 'Une description')
            ->set('instructions', 'Des instructions')
            ->set('imageFile', $file)
            ->call('store')
            ->assertHasErrors(['imageFile']);
    }

    /**
     * ❌ ERREUR - Image trop volumineuse
     */
    public function test_user_cannot_upload_oversized_image()
    {
        Storage::fake('public');
        
        // Créer une image de plus de 2MB
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(3000);

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', 'Test Recipe')
            ->set('description', 'Une description')
            ->set('instructions', 'Des instructions')
            ->set('imageFile', $largeImage)
            ->call('store')
            ->assertHasErrors(['imageFile']);
    }

    /**
     * ✅ CAS DE SUCCÈS - Modification d'une recette existante
     */
    public function test_user_can_edit_own_recipe()
    {
        $recipe = Recipe::factory()->create([
            'name' => 'Recette Originale',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->call('edit', $recipe->id)
            ->set('name', 'Recette Modifiée')
            ->set('description', 'Description modifiée')
            ->set('instructions', 'Instructions modifiées')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'name' => 'Recette Modifiée',
        ]);
    }

    /**
     * ❌ ERREUR - Modification d'une recette d'un autre utilisateur
     */
    public function test_user_cannot_edit_other_user_recipe()
    {
        $otherUser = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'name' => 'Recette d\'autrui',
            'user_id' => $otherUser->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->call('edit', $recipe->id)
            ->assertSet('editingId', null); // Ne devrait pas pouvoir éditer
    }

    /**
     * ✅ CAS DE SUCCÈS - Suppression d'une recette propre
     */
    public function test_user_can_delete_own_recipe()
    {
        $recipe = Recipe::factory()->create([
            'name' => 'Recette à Supprimer',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->call('delete', $recipe->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('recipes', [
            'id' => $recipe->id,
        ]);
    }

    /**
     * ❌ ERREUR - Suppression d'une recette d'un autre utilisateur
     */
    public function test_user_cannot_delete_other_user_recipe()
    {
        $otherUser = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'name' => 'Recette d\'autrui',
            'user_id' => $otherUser->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->call('delete', $recipe->id);

        // La recette devrait toujours exister
        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
        ]);
    }

    /**
     * ✅ CAS DE SUCCÈS - Recherche de recettes
     */
    public function test_user_can_search_recipes()
    {
        Recipe::factory()->create([
            'name' => 'Pasta Bolognaise',
            'user_id' => $this->user->id,
        ]);
        
        Recipe::factory()->create([
            'name' => 'Salade César',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('search', 'Pasta')
            ->assertSee('Pasta Bolognaise')
            ->assertDontSee('Salade César');
    }

    /**
     * ❌ ERREUR - Quantité d'ingrédient négative
     */
    public function test_user_cannot_add_negative_ingredient_quantity()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', 'Test Recipe')
            ->set('description', 'Une description')
            ->set('instructions', 'Des instructions')
            ->set('selectedIngredients', [$this->ingredients[0]->id])
            ->set('ingredientQuantities', [
                $this->ingredients[0]->id => -5,
            ])
            ->call('store')
            ->assertHasErrors();
    }

    /**
     * ❌ ERREUR - Accès aux recettes sans authentification
     */
    public function test_guest_cannot_access_recipes_page()
    {
        $response = $this->get('/recipes');
        
        $response->assertRedirect('/login');
    }

    /**
     * ✅ CAS DE SUCCÈS - Affichage des détails d'une recette
     */
    public function test_user_can_view_recipe_details()
    {
        $recipe = Recipe::factory()->create([
            'name' => 'Recette Détaillée',
            'description' => 'Description détaillée',
            'user_id' => $this->user->id,
        ]);

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->call('showRecipeDetails', $recipe->id)
            ->assertSet('selectedRecipe.name', 'Recette Détaillée')
            ->assertSet('showDetailModal', true);
    }

    /**
     * ❌ ERREUR - Création de recette avec caractères malveillants
     */
    public function test_user_cannot_create_recipe_with_malicious_content()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', '<script>alert("hack")</script>')
            ->set('description', '<script>alert("hack")</script>')
            ->set('instructions', '<script>alert("hack")</script>')
            ->call('store');

        // Vérifier que le script n'est pas exécuté/stocké tel quel
        $recipe = Recipe::where('user_id', $this->user->id)->first();
        if ($recipe) {
            $this->assertStringNotContainsString('<script>', $recipe->name);
            $this->assertStringNotContainsString('<script>', $recipe->description);
            $this->assertStringNotContainsString('<script>', $recipe->instructions);
        }
    }

    /**
     * ✅ CAS DE SUCCÈS - Ajout d'ingrédients avec quantités personnalisées
     */
    public function test_user_can_add_ingredients_with_custom_quantities()
    {
        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', 'Recette avec Quantités')
            ->set('description', 'Test des quantités')
            ->set('instructions', 'Mélanger les ingrédients')
            ->set('selectedIngredients', [$this->ingredients[0]->id, $this->ingredients[1]->id])
            ->set('ingredientQuantities', [
                $this->ingredients[0]->id => 2.5,
                $this->ingredients[1]->id => 3,
            ])
            ->set('ingredientUnits', [
                $this->ingredients[0]->id => 'kg',
                $this->ingredients[1]->id => 'pièce',
            ])
            ->call('store')
            ->assertHasNoErrors();

        $recipe = Recipe::where('name', 'Recette avec Quantités')->first();
        $this->assertNotNull($recipe);
        $this->assertEquals(2, $recipe->ingredients->count());
    }

    /**
     * ❌ ERREUR - Tentative de création de recette sans ingrédients
     */
    public function test_user_can_create_recipe_without_ingredients()
    {
        // Note: Selon les règles métier, une recette peut ou non avoir d'ingrédients
        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->set('name', 'Recette Sans Ingrédients')
            ->set('description', 'Une recette simple')
            ->set('instructions', 'Juste des instructions')
            ->set('selectedIngredients', [])
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recipes', [
            'name' => 'Recette Sans Ingrédients',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * ✅ CAS DE SUCCÈS - Recherche d'ingrédients lors de la création de recette
     */
    public function test_user_can_search_ingredients_when_creating_recipe()
    {
        // Créer des ingrédients avec des noms différents
        Ingredient::factory()->create(['name' => 'Tomate Rouge', 'user_id' => $this->user->id]);
        Ingredient::factory()->create(['name' => 'Oignon Blanc', 'user_id' => $this->user->id]);
        Ingredient::factory()->create(['name' => 'Ail', 'user_id' => $this->user->id]);

        $component = Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->call('create'); // Ouvrir le modal de création

        // Tester la recherche d'ingrédients
        $component->set('ingredientSearch', 'Tomate')
                  ->assertSee('Tomate Rouge')
                  ->assertDontSee('Oignon Blanc')
                  ->assertDontSee('Ail');

        // Tester avec un autre terme
        $component->set('ingredientSearch', 'Oignon')
                  ->assertSee('Oignon Blanc')
                  ->assertDontSee('Tomate Rouge')
                  ->assertDontSee('Ail');

        // Tester recherche vide (tous les ingrédients)
        $component->set('ingredientSearch', '')
                  ->assertSee('Tomate Rouge')
                  ->assertSee('Oignon Blanc')
                  ->assertSee('Ail');
    }

    /**
     * ❌ ERREUR - Recherche d'ingrédient inexistant
     */
    public function test_ingredient_search_shows_no_results_message()
    {
        Ingredient::factory()->create(['name' => 'Tomate', 'user_id' => $this->user->id]);

        Livewire::actingAs($this->user)
            ->test('recipe-manager')
            ->call('create')
            ->set('ingredientSearch', 'IngrédientInexistant')
            ->assertSee('Aucun ingrédient trouvé')
            ->assertSee('Essayez un autre terme de recherche');
    }
}
