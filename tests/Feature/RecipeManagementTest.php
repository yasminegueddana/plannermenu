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

class RecipeManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_recipe_with_ingredients()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        
        $ingredient1 = Ingredient::factory()->create(['name' => 'Tomate', 'user_id' => $user->id]);
        $ingredient2 = Ingredient::factory()->create(['name' => 'Oignon', 'user_id' => $user->id]);

        $image = UploadedFile::fake()->image('recipe.jpg');

        Livewire::actingAs($user)
            ->test('recipe-manager')
            ->set('name', 'Salade de Tomates')
            ->set('description', 'Une délicieuse salade')
            ->set('instructions', 'Couper les tomates...')
            ->set('prep_time', 15)
            ->set('cook_time', 0)
            ->set('servings', 4)
            ->set('image', $image)
            ->set('selectedIngredients', [
                $ingredient1->id => ['quantity' => 2, 'unit' => 'pièces'],
                $ingredient2->id => ['quantity' => 1, 'unit' => 'pièce'],
            ])
            ->call('createRecipe')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recipes', [
            'name' => 'Salade de Tomates',
            'user_id' => $user->id,
        ]);

        $recipe = Recipe::where('name', 'Salade de Tomates')->first();
        $this->assertCount(2, $recipe->ingredients);
    }

    /** @test */
    public function user_cannot_create_recipe_without_required_fields()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('recipe-manager')
            ->set('name', '')
            ->set('description', '')
            ->set('instructions', '')
            ->call('createRecipe')
            ->assertHasErrors(['name', 'description', 'instructions']);
    }

    /** @test */
    public function user_cannot_create_recipe_with_invalid_prep_time()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('recipe-manager')
            ->set('name', 'Test Recipe')
            ->set('description', 'Test')
            ->set('instructions', 'Test instructions')
            ->set('prep_time', -5)
            ->call('createRecipe')
            ->assertHasErrors(['prep_time']);
    }

    /** @test */
    public function user_can_upload_recipe_image()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        
        $image = UploadedFile::fake()->image('recipe.jpg', 800, 600);

        Livewire::actingAs($user)
            ->test('recipe-manager')
            ->set('name', 'Test Recipe')
            ->set('description', 'Test description')
            ->set('instructions', 'Test instructions')
            ->set('image', $image)
            ->call('createRecipe')
            ->assertHasNoErrors();

        $recipe = Recipe::where('name', 'Test Recipe')->first();
        $this->assertNotNull($recipe->image);
        Storage::disk('public')->assertExists($recipe->image);
    }

    /** @test */
    public function user_cannot_upload_invalid_image_format()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        Livewire::actingAs($user)
            ->test('recipe-manager')
            ->set('name', 'Test Recipe')
            ->set('description', 'Test description')
            ->set('instructions', 'Test instructions')
            ->set('image', $file)
            ->call('createRecipe')
            ->assertHasErrors(['image']);
    }

    /** @test */
    public function user_can_edit_their_recipe()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'name' => 'Original Recipe',
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test('recipe-manager')
            ->call('editRecipe', $recipe->id)
            ->set('name', 'Updated Recipe')
            ->set('description', 'Updated description')
            ->call('updateRecipe')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'name' => 'Updated Recipe',
            'description' => 'Updated description',
        ]);
    }

    /** @test */
    public function user_cannot_edit_other_users_recipe()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $recipe = Recipe::factory()->create([
            'name' => 'User2 Recipe',
            'user_id' => $user2->id,
        ]);

        Livewire::actingAs($user1)
            ->test('recipe-manager')
            ->call('editRecipe', $recipe->id)
            ->assertForbidden();
    }

    /** @test */
    public function user_can_delete_their_recipe()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'name' => 'Recipe to Delete',
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test('recipe-manager')
            ->call('deleteRecipe', $recipe->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('recipes', [
            'id' => $recipe->id,
        ]);
    }

    /** @test */
    public function user_can_search_recipes()
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
            ->test('recipe-manager')
            ->set('search', 'Pasta')
            ->assertSee('Pasta Bolognaise')
            ->assertDontSee('Salade César');
    }
}
