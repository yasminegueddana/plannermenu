<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IngredientManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_access_ingredients_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ingredients');

        $response->assertStatus(200);
        $response->assertSee('Gestion des Ingrédients');
    }

    /** @test */
    public function guest_cannot_access_ingredients_page()
    {
        $response = $this->get('/ingredients');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function user_can_create_ingredient()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('ingredient-manager')
            ->set('name', 'Tomate')
            ->set('category', 'Légume')
            ->call('createIngredient')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('ingredients', [
            'name' => 'Tomate',
            'category' => 'Légume',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function user_cannot_create_ingredient_with_empty_name()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('ingredient-manager')
            ->set('name', '')
            ->set('category', 'Légume')
            ->call('createIngredient')
            ->assertHasErrors(['name']);

        $this->assertDatabaseMissing('ingredients', [
            'category' => 'Légume',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function user_cannot_create_duplicate_ingredient()
    {
        $user = User::factory()->create();
        
        Ingredient::factory()->create([
            'name' => 'Tomate',
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test('ingredient-manager')
            ->set('name', 'Tomate')
            ->set('category', 'Légume')
            ->call('createIngredient')
            ->assertHasErrors(['name']);
    }

    /** @test */
    public function user_can_edit_their_ingredient()
    {
        $user = User::factory()->create();
        $ingredient = Ingredient::factory()->create([
            'name' => 'Tomate',
            'category' => 'Légume',
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test('ingredient-manager')
            ->call('editIngredient', $ingredient->id)
            ->set('name', 'Tomate Rouge')
            ->set('category', 'Légume Bio')
            ->call('updateIngredient')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'name' => 'Tomate Rouge',
            'category' => 'Légume Bio',
        ]);
    }

    /** @test */
    public function user_cannot_edit_other_users_ingredient()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $ingredient = Ingredient::factory()->create([
            'name' => 'Tomate',
            'user_id' => $user2->id,
        ]);

        Livewire::actingAs($user1)
            ->test('ingredient-manager')
            ->call('editIngredient', $ingredient->id)
            ->assertForbidden();
    }

    /** @test */
    public function user_can_delete_their_ingredient()
    {
        $user = User::factory()->create();
        $ingredient = Ingredient::factory()->create([
            'name' => 'Tomate',
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test('ingredient-manager')
            ->call('deleteIngredient', $ingredient->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('ingredients', [
            'id' => $ingredient->id,
        ]);
    }

    /** @test */
    public function user_can_search_ingredients()
    {
        $user = User::factory()->create();
        
        Ingredient::factory()->create([
            'name' => 'Tomate',
            'user_id' => $user->id,
        ]);
        
        Ingredient::factory()->create([
            'name' => 'Carotte',
            'user_id' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test('ingredient-manager')
            ->set('search', 'Tom')
            ->assertSee('Tomate')
            ->assertDontSee('Carotte');
    }
}
