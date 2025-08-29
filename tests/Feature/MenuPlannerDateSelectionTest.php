<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\DayMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests pour la sélection de dates et génération d'ingrédients spécifiques
 */
class MenuPlannerDateSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $menu;
    protected $recipes;
    protected $ingredients;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->menu = Menu::factory()->create(['user_id' => $this->user->id]);
        
        // Créer des ingrédients
        $this->ingredients = [
            Ingredient::factory()->create(['name' => 'Tomate', 'unite' => 'g']),
            Ingredient::factory()->create(['name' => 'Oignon', 'unite' => 'g']),
            Ingredient::factory()->create(['name' => 'Ail', 'unite' => 'g']),
        ];
        
        // Créer des recettes avec ingrédients
        $this->recipes = [
            Recipe::factory()->create(['name' => 'Salade', 'user_id' => $this->user->id]),
            Recipe::factory()->create(['name' => 'Soupe', 'user_id' => $this->user->id]),
        ];
        
        // Attacher les ingrédients aux recettes
        $this->recipes[0]->ingredients()->attach([
            $this->ingredients[0]->id => ['quantity' => 200, 'unit' => 'g'],
            $this->ingredients[1]->id => ['quantity' => 100, 'unit' => 'g'],
        ]);
        
        $this->recipes[1]->ingredients()->attach([
            $this->ingredients[1]->id => ['quantity' => 150, 'unit' => 'g'],
            $this->ingredients[2]->id => ['quantity' => 50, 'unit' => 'g'],
        ]);
    }

    /**
     * ✅ CAS DE SUCCÈS - Basculer en mode sélection de dates
     */
    public function test_user_can_toggle_date_selection_mode()
    {
        Livewire::actingAs($this->user)
            ->test('menu-planner')
            ->assertSet('showDateSelection', false)
            ->call('toggleDateSelectionMode')
            ->assertSet('showDateSelection', true)
            ->assertSee('Sélectionnez les jours')
            ->call('toggleDateSelectionMode')
            ->assertSet('showDateSelection', false);
    }

    /**
     * ✅ CAS DE SUCCÈS - Sélectionner des dates avec menus planifiés
     */
    public function test_user_can_select_dates_with_planned_meals()
    {
        // Créer des menus planifiés pour des dates spécifiques
        DayMenu::factory()->create([
            'menu_id' => $this->menu->id,
            'user_id' => $this->user->id,
            'day' => '2024-01-15',
            'meal' => 'Déjeuner',
            'recipe_id' => $this->recipes[0]->id,
        ]);
        
        DayMenu::factory()->create([
            'menu_id' => $this->menu->id,
            'user_id' => $this->user->id,
            'day' => '2024-01-16',
            'meal' => 'Dîner',
            'recipe_id' => $this->recipes[1]->id,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test('menu-planner')
            ->call('toggleDateSelectionMode')
            ->assertSet('showDateSelection', true);

        // Sélectionner une date
        $component->call('toggleDateSelection', '2024-01-15')
                  ->assertSet('selectedDates', ['2024-01-15']);

        // Sélectionner une autre date
        $component->call('toggleDateSelection', '2024-01-16')
                  ->assertSet('selectedDates', ['2024-01-15', '2024-01-16']);

        // Désélectionner une date
        $component->call('toggleDateSelection', '2024-01-15')
                  ->assertSet('selectedDates', ['2024-01-16']);
    }

    /**
     * ✅ CAS DE SUCCÈS - Sélectionner toute une semaine
     */
    public function test_user_can_select_entire_week()
    {
        // Créer des menus pour plusieurs jours d'une semaine
        $dates = ['2024-01-15', '2024-01-16', '2024-01-17'];
        
        foreach ($dates as $date) {
            DayMenu::factory()->create([
                'menu_id' => $this->menu->id,
                'user_id' => $this->user->id,
                'day' => $date,
                'meal' => 'Déjeuner',
                'recipe_id' => $this->recipes[0]->id,
            ]);
        }

        Livewire::actingAs($this->user)
            ->test('menu-planner')
            ->call('toggleDateSelectionMode')
            ->call('selectWeek', '2024-01-15')
            ->assertSet('selectedDates', $dates);
    }

    /**
     * ✅ CAS DE SUCCÈS - Générer ingrédients pour dates sélectionnées
     */
    public function test_user_can_generate_ingredients_for_selected_dates()
    {
        // Créer un menu planifié
        DayMenu::factory()->create([
            'menu_id' => $this->menu->id,
            'user_id' => $this->user->id,
            'day' => '2024-01-15',
            'meal' => 'Déjeuner',
            'recipe_id' => $this->recipes[0]->id,
            'servings' => 2,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test('menu-planner')
            ->call('toggleDateSelectionMode')
            ->call('toggleDateSelection', '2024-01-15')
            ->call('generateIngredientsForSelectedDates');

        // Vérifier la redirection vers la liste de courses
        $component->assertRedirect(route('shopping-list.index'));
    }

    /**
     * ❌ ERREUR - Générer ingrédients sans sélectionner de dates
     */
    public function test_cannot_generate_ingredients_without_selected_dates()
    {
        Livewire::actingAs($this->user)
            ->test('menu-planner')
            ->call('toggleDateSelectionMode')
            ->call('generateIngredientsForSelectedDates')
            ->assertHasErrors()
            ->assertSee('Veuillez sélectionner au moins une date');
    }

    /**
     * ✅ CAS DE SUCCÈS - Vérifier si une date a des menus planifiés
     */
    public function test_can_check_if_date_has_planned_meals()
    {
        DayMenu::factory()->create([
            'menu_id' => $this->menu->id,
            'user_id' => $this->user->id,
            'day' => '2024-01-15',
            'meal' => 'Déjeuner',
            'recipe_id' => $this->recipes[0]->id,
        ]);

        $component = Livewire::actingAs($this->user)->test('menu-planner');
        
        // Cette date a des menus
        $this->assertTrue($component->instance()->hasPlannedMeals('2024-01-15'));
        
        // Cette date n'a pas de menus
        $this->assertFalse($component->instance()->hasPlannedMeals('2024-01-20'));
    }

    /**
     * ✅ CAS DE SUCCÈS - Interface affiche les checkboxes en mode sélection
     */
    public function test_interface_shows_checkboxes_in_selection_mode()
    {
        // Créer un menu planifié
        DayMenu::factory()->create([
            'menu_id' => $this->menu->id,
            'user_id' => $this->user->id,
            'day' => '2024-01-15',
            'meal' => 'Déjeuner',
            'recipe_id' => $this->recipes[0]->id,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test('menu-planner');

        // En mode normal, pas de checkboxes
        $component->assertDontSee('type="checkbox"');

        // En mode sélection, checkboxes visibles
        $component->call('toggleDateSelectionMode')
                  ->assertSee('Sélectionnez les jours')
                  ->assertSee('Sélection rapide par semaine');
    }

    /**
     * ✅ CAS DE SUCCÈS - Boutons changent selon le mode
     */
    public function test_buttons_change_based_on_mode()
    {
        Livewire::actingAs($this->user)
            ->test('menu-planner')
            // Mode normal
            ->assertSee('Sélectionner des dates pour générer les ingrédients')
            ->assertDontSee('Générer les ingrédients')
            ->assertDontSee('Annuler la sélection')
            // Mode sélection
            ->call('toggleDateSelectionMode')
            ->assertDontSee('Sélectionner des dates pour générer les ingrédients')
            ->assertSee('Générer les ingrédients')
            ->assertSee('Annuler la sélection');
    }

    /**
     * ✅ CAS DE SUCCÈS - Compteur de dates sélectionnées
     */
    public function test_selected_dates_counter_updates()
    {
        // Créer des menus planifiés
        DayMenu::factory()->create([
            'menu_id' => $this->menu->id,
            'user_id' => $this->user->id,
            'day' => '2024-01-15',
            'meal' => 'Déjeuner',
            'recipe_id' => $this->recipes[0]->id,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test('menu-planner')
            ->call('toggleDateSelectionMode');

        // Aucune date sélectionnée
        $component->assertSee('Générer les ingrédients (0 jour(s))');

        // Une date sélectionnée
        $component->call('toggleDateSelection', '2024-01-15')
                  ->assertSee('Générer les ingrédients (1 jour(s))')
                  ->assertSee('(1 jour(s) sélectionné(s))');
    }
}
