<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Recipe;
use App\Models\Menu;
use App\Models\DayMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Carbon\Carbon;

class MenuPlanningTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_menu()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('menu-planner')
            ->set('newMenuName', 'Menu de la semaine')
            ->set('startDate', '2024-01-01')
            ->set('endDate', '2024-01-07')
            ->call('createMenu')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('menus', [
            'name' => 'Menu de la semaine',
            'user_id' => $user->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-07',
        ]);
    }

    /** @test */
    public function user_cannot_create_menu_with_invalid_dates()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('menu-planner')
            ->set('newMenuName', 'Menu Test')
            ->set('startDate', '2024-01-07')
            ->set('endDate', '2024-01-01') // Date de fin avant date de début
            ->call('createMenu')
            ->assertHasErrors(['endDate']);
    }

    /** @test */
    public function user_can_add_recipe_to_menu()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $menu = Menu::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('menu-planner')
            ->set('currentMenuId', $menu->id)
            ->set('selectedDate', '2024-01-01')
            ->set('selectedMealType', 'déjeuner')
            ->set('selectedRecipeId', $recipe->id)
            ->set('servings', 4)
            ->call('addRecipeToMenu')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('day_menus', [
            'menu_id' => $menu->id,
            'recipe_id' => $recipe->id,
            'date' => '2024-01-01',
            'meal_type' => 'déjeuner',
            'servings' => 4,
        ]);
    }

    /** @test */
    public function user_cannot_add_recipe_without_selecting_date()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $menu = Menu::factory()->create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test('menu-planner')
            ->set('currentMenuId', $menu->id)
            ->set('selectedDate', '')
            ->set('selectedMealType', 'déjeuner')
            ->set('selectedRecipeId', $recipe->id)
            ->call('addRecipeToMenu')
            ->assertHasErrors(['selectedDate']);
    }

    /** @test */
    public function user_can_remove_recipe_from_menu()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $menu = Menu::factory()->create(['user_id' => $user->id]);
        
        $dayMenu = DayMenu::create([
            'menu_id' => $menu->id,
            'recipe_id' => $recipe->id,
            'date' => '2024-01-01',
            'meal_type' => 'déjeuner',
            'servings' => 4,
        ]);

        Livewire::actingAs($user)
            ->test('menu-planner')
            ->call('removeRecipeFromMenu', $dayMenu->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('day_menus', [
            'id' => $dayMenu->id,
        ]);
    }

    /** @test */
    public function user_cannot_access_other_users_menu()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $menu = Menu::factory()->create(['user_id' => $user2->id]);

        Livewire::actingAs($user1)
            ->test('menu-planner')
            ->call('selectMenu', $menu->id)
            ->assertForbidden();
    }

    /** @test */
    public function user_can_duplicate_menu()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $menu = Menu::factory()->create([
            'name' => 'Menu Original',
            'user_id' => $user->id,
        ]);
        
        DayMenu::create([
            'menu_id' => $menu->id,
            'recipe_id' => $recipe->id,
            'date' => '2024-01-01',
            'meal_type' => 'déjeuner',
            'servings' => 4,
        ]);

        Livewire::actingAs($user)
            ->test('menu-planner')
            ->call('duplicateMenu', $menu->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('menus', [
            'name' => 'Menu Original (Copie)',
            'user_id' => $user->id,
        ]);

        $duplicatedMenu = Menu::where('name', 'Menu Original (Copie)')->first();
        $this->assertCount(1, $duplicatedMenu->dayMenus);
    }

    /** @test */
    public function user_can_view_menu_statistics()
    {
        $user = User::factory()->create();
        $recipe1 = Recipe::factory()->create(['user_id' => $user->id]);
        $recipe2 = Recipe::factory()->create(['user_id' => $user->id]);
        $menu = Menu::factory()->create(['user_id' => $user->id]);
        
        DayMenu::create([
            'menu_id' => $menu->id,
            'recipe_id' => $recipe1->id,
            'date' => '2024-01-01',
            'meal_type' => 'déjeuner',
            'servings' => 4,
        ]);
        
        DayMenu::create([
            'menu_id' => $menu->id,
            'recipe_id' => $recipe2->id,
            'date' => '2024-01-01',
            'meal_type' => 'dîner',
            'servings' => 2,
        ]);

        $component = Livewire::actingAs($user)
            ->test('menu-planner')
            ->set('currentMenuId', $menu->id)
            ->call('selectMenu', $menu->id);

        // Vérifier que les statistiques sont calculées correctement
        $this->assertEquals(2, $component->get('getTotalPlannedMeals'));
        $this->assertEquals(2, $component->get('getUniqueRecipesCount'));
    }
}
