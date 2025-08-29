<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShoppingListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_access_shopping_list_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/shopping-list');

        $response->assertStatus(200);
        $response->assertSee('Liste des courses');
    }

    /** @test */
    public function user_can_select_recipe_for_shopping_list()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'name' => 'Pasta Bolognaise',
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $recipe->id)
            ->assertSet('selectedRecipeId', $recipe->id)
            ->assertSet('showIngredients', true);
    }

    /** @test */
    public function shopping_list_calculates_quantities_correctly()
    {
        $user = User::factory()->create();
        
        $ingredient1 = Ingredient::factory()->create(['name' => 'Pâtes', 'user_id' => $user->id]);
        $ingredient2 = Ingredient::factory()->create(['name' => 'Tomates', 'user_id' => $user->id]);
        
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        
        // Attacher les ingrédients à la recette
        $recipe->ingredients()->attach($ingredient1->id, ['quantity' => 400, 'unit' => 'g']);
        $recipe->ingredients()->attach($ingredient2->id, ['quantity' => 500, 'unit' => 'g']);

        $component = Livewire::actingAs($user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $recipe->id)
            ->set('servings', 6); // 6 personnes au lieu de 4

        $ingredientsList = $component->get('ingredientsList');
        
        // Vérifier que les quantités sont correctement calculées
        $this->assertEquals(2400, $ingredientsList[0]['adjusted_quantity']); // 400 * 6
        $this->assertEquals(3000, $ingredientsList[1]['adjusted_quantity']); // 500 * 6
    }

    /** @test */
    public function user_can_edit_ingredient_prices()
    {
        $user = User::factory()->create();
        
        $ingredient = Ingredient::factory()->create(['user_id' => $user->id]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $recipe->ingredients()->attach($ingredient->id, ['quantity' => 100, 'unit' => 'g']);

        Livewire::actingAs($user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $recipe->id)
            ->call('editPrice', 0)
            ->set('tempPrice', 2.50)
            ->call('savePrice', 0)
            ->assertSet('editingPrice', null);

        $component = Livewire::actingAs($user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $recipe->id);

        $ingredientsList = $component->get('ingredientsList');
        $this->assertEquals(2.50, $ingredientsList[0]['estimated_cost']);
    }

    /** @test */
    public function user_can_mark_ingredients_as_purchased()
    {
        $user = User::factory()->create();
        
        $ingredient = Ingredient::factory()->create(['user_id' => $user->id]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $recipe->ingredients()->attach($ingredient->id, ['quantity' => 100, 'unit' => 'g']);

        Livewire::actingAs($user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $recipe->id)
            ->call('togglePurchased', 0);

        $component = Livewire::actingAs($user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $recipe->id);

        $ingredientsList = $component->get('ingredientsList');
        $this->assertTrue($ingredientsList[0]['is_purchased']);
    }

    /** @test */
    public function user_can_export_shopping_list_to_pdf()
    {
        $user = User::factory()->create();
        
        $ingredient = Ingredient::factory()->create(['user_id' => $user->id]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $recipe->ingredients()->attach($ingredient->id, ['quantity' => 100, 'unit' => 'g']);

        $response = $this->actingAs($user)
            ->get('/recipe-shopping/export/pdf?' . http_build_query([
                'recipe_id' => $recipe->id,
                'servings' => 4,
                'ingredients' => [
                    $ingredient->id => [
                        'estimated_cost' => 2.50,
                        'is_purchased' => false,
                    ]
                ],
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function user_can_export_shopping_list_to_excel()
    {
        $user = User::factory()->create();
        
        $ingredient = Ingredient::factory()->create(['user_id' => $user->id]);
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $recipe->ingredients()->attach($ingredient->id, ['quantity' => 100, 'unit' => 'g']);

        $response = $this->actingAs($user)
            ->get('/recipe-shopping/export/excel?' . http_build_query([
                'recipe_id' => $recipe->id,
                'servings' => 4,
                'ingredients' => [
                    $ingredient->id => [
                        'estimated_cost' => 2.50,
                        'is_purchased' => false,
                    ]
                ],
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function export_requires_valid_recipe()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/recipe-shopping/export/pdf?recipe_id=999');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function user_can_reset_shopping_list_selection()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('recipe-shopping-generator')
            ->call('selectRecipe', $recipe->id)
            ->assertSet('showIngredients', true)
            ->call('resetSelection')
            ->assertSet('showIngredients', false)
            ->assertSet('selectedRecipeId', null);
    }

    /** @test */
    public function user_can_search_recipes_in_shopping_list()
    {
        $user = User::factory()->create();
        
        Recipe::factory()->create([
            'name' => 'Pasta Bolognaise',
            'user_id' => $user->id,
        ]);
        
        Recipe::factory()->create([
            'name' => 'Salade César',
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test('recipe-shopping-generator')
            ->set('searchTerm', 'Pasta')
            ->assertSee('Pasta Bolognaise')
            ->assertDontSee('Salade César');
    }
}
