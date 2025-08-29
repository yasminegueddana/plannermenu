<?php

namespace App\Livewire;

use App\Models\Menu;
use App\Models\Recipe;
use App\Models\DayMenu;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MenuPlanner extends Component
{
    public $currentMenu;
    public $selectedMonth;
    public $selectedYear;
    public $recipes;
    public $mealTypes = ['Petit-déjeuner', 'Déjeuner', 'Dîner'];
    public $menuName = '';
    public $showCreateMenu = false;
    public $selectedDate = null;
    public $selectedMealType = null;
    public $showMealModal = false;
    public $searchRecipes = '';
    public $selectedRecipeIds = [];
    public $servings = 1;

    // Nouvelles propriétés pour les créneaux horaires
    public $showMealTypeModal = false;
    public $currentMealMenus = [];
    public $newMenuName = '';
    public $startTime = '';
    public $endTime = '';
    public $menuDescription = '';

    // Créneaux horaires par défaut
    public $defaultTimeSlots = [
        'Petit-déjeuner' => ['08:00', '12:00'],
        'Déjeuner' => ['12:00', '18:00'],
        'Dîner' => ['19:00', '22:00']
    ];

    // Structure pour stocker les recettes planifiées par date
    public $plannedMeals = [];

    // Sélection de dates pour génération d'ingrédients
    public $selectedDates = [];
    public $selectedWeek = null;
    public $showDateSelection = false;

    public $calendarDays = [];
    public $monthName = '';
    public $weekDays = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

    public function mount()
    {
        $this->selectedMonth = Carbon::now()->month;
        $this->selectedYear = Carbon::now()->year;
        $this->loadCurrentMenu();
        $this->recipes = Recipe::with('ingredients')->get();
        $this->generateCalendar();
        $this->loadPlannedMeals();
    }

    public function loadCurrentMenu()
    {
        $this->currentMenu = Menu::where('user_id', Auth::id())
                                ->with(['dayMenus', 'recipes'])
                                ->latest()
                                ->first();
    }

    public function generateCalendar()
    {
        $date = Carbon::create($this->selectedYear, $this->selectedMonth, 1);
        $this->monthName = $date->locale('fr')->monthName . ' ' . $this->selectedYear;

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);

        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $this->calendarDays = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $this->calendarDays[] = [
                'date' => $currentDate->copy(),
                'isCurrentMonth' => $currentDate->month === $this->selectedMonth,
                'isToday' => $currentDate->isToday(),
                'dayNumber' => $currentDate->day,
                'fullDate' => $currentDate->format('Y-m-d'),
            ];
            $currentDate->addDay();
        }
    }

    public function loadPlannedMeals()
    {
        $this->plannedMeals = [];

        if ($this->currentMenu) {
            // Charger les repas planifiés depuis la bd avec les recettes
            $dayMenus = DayMenu::with('recipe')
                              ->where('menu_id', $this->currentMenu->id)
                              ->where('user_id', Auth::id())
                              ->get();

            // Grouper les menus par date, type de repas et nom de menu
            $groupedMenus = [];
            foreach ($dayMenus as $dayMenu) {
                $key = $dayMenu->day . '|' . $dayMenu->meal . '|' . $dayMenu->menu_name;

                if (!isset($groupedMenus[$key])) {
                    $groupedMenus[$key] = [
                        'day' => $dayMenu->day,
                        'meal' => $dayMenu->meal,
                        'menu_name' => $dayMenu->menu_name,
                        'servings' => $dayMenu->servings,
                        'start_time' => $dayMenu->start_time,
                        'end_time' => $dayMenu->end_time,
                        'recipes' => [],
                        'day_menu_ids' => []
                    ];
                }

                // Ajouter la recette au groupe
                if ($dayMenu->recipe) {
                    $groupedMenus[$key]['recipes'][] = $dayMenu->recipe->name;
                }
                $groupedMenus[$key]['day_menu_ids'][] = $dayMenu->id;
            }

            // Convertir en format attendu par la vue
            foreach ($groupedMenus as $group) {
                $this->plannedMeals[$group['day']][$group['meal']][] = [
                    'day_menu_ids' => $group['day_menu_ids'],
                    'menu_name' => $group['menu_name'],
                    'recipes' => $group['recipes'],
                    'servings' => $group['servings'],
                    'start_time' => $group['start_time'],
                    'end_time' => $group['end_time'],
                ];
            }
        }
    }

    public function createNewMenu()
    {
        $this->validate(['menuName' => 'required|string|max:255']);

        $this->currentMenu = Menu::create([
            'name' => $this->menuName,
            'user_id' => Auth::id(),
        ]);

        $this->showCreateMenu = false;
        $this->menuName = '';
        $this->initializePlannedMeals();

        session()->flash('message', 'Menu créé avec succès !');
    }

    public function initializePlannedMeals()
    {
        // Initialiser la structure des repas planifiés
        foreach ($this->weekDays as $day) {
            foreach ($this->mealTypes as $mealType) {
                $this->plannedMeals[$day][$mealType] = [];
            }
        }

        // Charger les repas existants si un menu est sélectionné
        if ($this->currentMenu) {
            foreach ($this->currentMenu->dayMenus as $dayMenu) {
                $date = $dayMenu->day;
                $mealType = $dayMenu->meal;

                // Initialiser le tableau si nécessaire
                if (!isset($this->plannedMeals[$date])) {
                    $this->plannedMeals[$date] = [];
                }
                if (!isset($this->plannedMeals[$date][$mealType])) {
                    $this->plannedMeals[$date][$mealType] = [];
                }


            }
        }
    }

    public function addRecipeToMeal($recipeId, $day, $mealType, $servings = 1)
    {
        $recipe = Recipe::find($recipeId);
        if ($recipe) {
            // Sauvegarder en base si un menu existe
            $dayMenu = null;
            if ($this->currentMenu) {
                $dayMenu = $this->savePlannedMeal($recipeId, $day, $mealType, $servings);
            }

            // Ajouter la recette au planning
            $this->plannedMeals[$day][$mealType][] = [
                'day_menu_id' => $dayMenu ? $dayMenu->id : null,
                'recipe_id' => $recipe->id,
                'recipe_name' => $recipe->name,
                'image' => $recipe->image,
                'servings' => $servings,
            ];
        }
    }

    public function selectRecipeForMeal($recipeId)
    {
        $this->toggleRecipeSelection($recipeId);
        $this->servings = 1; // Reset à 1 personne par défaut
    }

    public function addSelectedRecipeToMeal()
    {
        if (count($this->selectedRecipeIds) > 0 && $this->selectedDate && $this->selectedMealType) {
            foreach ($this->selectedRecipeIds as $recipeId) {
                $this->addRecipeToMeal($recipeId, $this->selectedDate, $this->selectedMealType, $this->servings);
            }
            $this->closeMealModal();
        }
    }

    public function closeMealModal()
    {
        $this->showMealModal = false;
        $this->selectedRecipeIds = [];
        $this->servings = 1;
        $this->searchRecipes = '';
    }

    /**
     * Ouvrir la modal pour un type de repas spécifique
     */
    public function openMealTypeModal($date, $mealType)
    {
        $this->selectedDate = $date;
        $this->selectedMealType = $mealType;
        $this->showMealTypeModal = true;

        // Charger les menus existants pour ce type de repas et cette date
        $this->loadMealMenus($date, $mealType);

        // Définir les horaires par défaut
        if (isset($this->defaultTimeSlots[$mealType])) {
            $this->startTime = $this->defaultTimeSlots[$mealType][0];
            $this->endTime = $this->defaultTimeSlots[$mealType][1];
        }
    }

    /**
     * Charger les menus existants pour un type de repas
     */
    public function loadMealMenus($date, $mealType)
    {
        if (!$this->currentMenu) {
            $this->currentMealMenus = [];
            return;
        }

        $dayMenus = DayMenu::where('menu_id', $this->currentMenu->id)
            ->where('day', $date)
            ->where('meal', $mealType)
            ->with('recipe')
            ->orderBy('start_time')
            ->get();

        // Grouper les menus par nom de menu
        $groupedMenus = [];
        foreach ($dayMenus as $dayMenu) {
            $key = $dayMenu->menu_name . '|' . $dayMenu->start_time . '|' . $dayMenu->end_time . '|' . $dayMenu->servings;

            if (!isset($groupedMenus[$key])) {
                $groupedMenus[$key] = [
                    'ids' => [],
                    'menu_name' => $dayMenu->menu_name,
                    'description' => $dayMenu->description,
                    'start_time' => $dayMenu->start_time ? $dayMenu->start_time->format('H:i') : null,
                    'end_time' => $dayMenu->end_time ? $dayMenu->end_time->format('H:i') : null,
                    'servings' => $dayMenu->servings,
                    'recipes' => [],
                    'is_active' => $dayMenu->isCurrentlyActive(),
                ];
            }

            // Ajouter l'ID et la recette au groupe
            $groupedMenus[$key]['ids'][] = $dayMenu->id;
            if ($dayMenu->recipe) {
                $groupedMenus[$key]['recipes'][] = [
                    'id' => $dayMenu->recipe->id,
                    'name' => $dayMenu->recipe->name,
                    'image' => $dayMenu->recipe->image,
                    'ingredients' => $dayMenu->getAdjustedIngredients()->toArray(),
                ];
            }
        }

        // Convertir en format attendu par la vue
        $this->currentMealMenus = array_values(array_map(function ($group) {
            return [
                'id' => implode(',', $group['ids']), // IDs séparés par des virgules pour la suppression
                'menu_name' => $group['menu_name'],
                'description' => $group['description'],
                'start_time' => $group['start_time'],
                'end_time' => $group['end_time'],
                'servings' => $group['servings'],
                'recipes' => $group['recipes'], // Tableau de recettes avec leurs ingrédients
                'is_active' => $group['is_active'],
            ];
        }, $groupedMenus));
    }

    /**
     * Sélectionner/désélectionner une recette pour le menu
     */
    public function toggleRecipeSelection($recipeId)
    {
        if (in_array($recipeId, $this->selectedRecipeIds)) {
            // Désélectionner la recette
            $this->selectedRecipeIds = array_values(array_filter($this->selectedRecipeIds, function($id) use ($recipeId) {
                return $id != $recipeId;
            }));
        } else {
            // Sélectionner la recette
            $this->selectedRecipeIds[] = $recipeId;
        }
    }

    /**
     * Créer un nouveau menu avec recettes et créneau horaire
     */
    public function createMealMenuWithRecipe()
    {
        $this->validate([
            'newMenuName' => 'required|string|max:255',
            'startTime' => 'required',
            'endTime' => 'required|after:startTime',
            'servings' => 'required|integer|min:1|max:50',
            'selectedRecipeIds' => 'required|array|min:1',
            'selectedRecipeIds.*' => 'exists:recipes,id',
        ]);

        if (!$this->currentMenu) {
            session()->flash('error', 'Aucun menu principal sélectionné.');
            return;
        }

        // Créer un menu pour chaque recette sélectionnée
        foreach ($this->selectedRecipeIds as $recipeId) {
            DayMenu::create([
                'menu_id' => $this->currentMenu->id,
                'day' => $this->selectedDate,
                'meal' => $this->selectedMealType,
                'user_id' => Auth::id(),
                'recipe_id' => $recipeId,
                'menu_name' => $this->newMenuName,
                'start_time' => $this->startTime,
                'end_time' => $this->endTime,
                'servings' => $this->servings,
            ]);
        }

        // Ajouter toutes les recettes au menu principal
        $this->currentMenu->recipes()->syncWithoutDetaching($this->selectedRecipeIds);

        // Recharger les menus
        $this->loadMealMenus($this->selectedDate, $this->selectedMealType);

        // Recharger les repas planifiés pour mettre à jour le calendrier
        $this->loadPlannedMeals();

        // Reset du formulaire
        $this->resetMenuForm();

        session()->flash('message', 'Menu créé avec ' . count($this->selectedRecipeIds) . ' recette(s) !');
    }

    /**
     * Ajouter une recette à un menu existant
     */
    public function addRecipeToMenu($menuId, $recipeId)
    {
        $dayMenu = DayMenu::find($menuId);
        if ($dayMenu && !$dayMenu->recipe_id) {
            $dayMenu->update(['recipe_id' => $recipeId]);

            // Ajouter la recette au menu principal
            $this->currentMenu->recipes()->syncWithoutDetaching([$recipeId]);

            // Recharger les menus
            $this->loadMealMenus($this->selectedDate, $this->selectedMealType);

            session()->flash('message', 'Recette ajoutée au menu !');
        }
    }

    /**
     * Supprimer un menu
     */
    public function deleteMealMenu($menuIds)
    {
        // Gérer plusieurs IDs séparés par des virgules
        $ids = is_string($menuIds) ? explode(',', $menuIds) : [$menuIds];
        $ids = array_filter($ids); // Supprimer les valeurs vides

        if (!empty($ids)) {
            DayMenu::whereIn('id', $ids)
                   ->where('menu_id', $this->currentMenu->id)
                   ->delete();

            // Recharger les données de la modal
            $this->loadMealMenus($this->selectedDate, $this->selectedMealType);

            // Recharger les données du calendrier
            $this->loadPlannedMeals();

            session()->flash('message', 'Menu supprimé avec succès !');
        }
    }

    /**
     * Fermer la modal des types de repas
     */
    public function closeMealTypeModal()
    {
        $this->showMealTypeModal = false;
        $this->resetMenuForm();
    }

    /**
     * Reset du formulaire de menu
     */
    private function resetMenuForm()
    {
        $this->newMenuName = '';
        $this->servings = 1;
        $this->selectedRecipeIds = [];
    }

    public function removeRecipeFromMeal($recipeIndex, $day, $mealType)
    {
        unset($this->plannedMeals[$day][$mealType][$recipeIndex]);
        $this->plannedMeals[$day][$mealType] = array_values($this->plannedMeals[$day][$mealType]);
    }

    private function savePlannedMeal($recipeId, $day, $mealType, $servings = 1)
    {

        $dayMenu = DayMenu::firstOrCreate([
            'menu_id' => $this->currentMenu->id,
            'day' => $day,
            'meal' => $mealType,
            'user_id' => Auth::id(),
            'recipe_id' => $recipeId,
            'servings' => $servings,
        ]);

        // Ajouter la recette au menu via la table pivot
        $this->currentMenu->recipes()->syncWithoutDetaching([$recipeId]);

        return $dayMenu;
    }

    public function changeMonth($direction)
    {
        if ($direction === 'next') {
            if ($this->selectedMonth === 12) {
                $this->selectedMonth = 1;
                $this->selectedYear++;
            } else {
                $this->selectedMonth++;
            }
        } else {
            if ($this->selectedMonth === 1) {
                $this->selectedMonth = 12;
                $this->selectedYear--;
            } else {
                $this->selectedMonth--;
            }
        }

        $this->generateCalendar();
        $this->loadPlannedMeals();
    }

    public function selectDateAndMeal($date, $mealType)
    {
        $this->openMealTypeModal($date, $mealType);
    }

    public function addRecipeToDate($recipeId)
    {
        if (!$this->selectedDate || !$this->selectedMealType || !$this->currentMenu) {
            return;
        }

        $recipe = Recipe::find($recipeId);
        if ($recipe) {
            // Ajouter à la structure locale
            $this->plannedMeals[$this->selectedDate][$this->selectedMealType][] = [
                'recipe_id' => $recipe->id,
                'recipe_name' => $recipe->name,
            ];

            // Sauvegarder en base
            DayMenu::create([
                'menu_id' => $this->currentMenu->id,
                'day' => Carbon::parse($this->selectedDate)->format('l'), // Nom du jour
                'meal' => $this->selectedMealType,
                'user_id' => 1,
            ]);

            // Ajouter la recette au menu
            $this->currentMenu->recipes()->syncWithoutDetaching([$recipeId]);

            $this->showMealModal = false;
            session()->flash('message', 'Recette ajoutée au menu !');
        }
    }

    /**
     * Basculer la sélection d'une date pour la génération d'ingrédients
     */
    public function toggleDateSelection($date)
    {
        if (in_array($date, $this->selectedDates)) {
            $this->selectedDates = array_diff($this->selectedDates, [$date]);
        } else {
            $this->selectedDates[] = $date;
        }
    }

    /**
 * Générer la liste d'ingrédients pour les dates sélectionnées
 */
public function generateIngredientsForSelectedDates()
{
    if (empty($this->selectedDates)) {
        session()->flash('error', 'Veuillez sélectionner au moins une date.');
        return;
    }

    $ingredients = [];
    $dayMenuIds = [];

    // Collecter tous les IDs de DayMenu en une seule passe
    foreach ($this->selectedDates as $date) {
        if (!isset($this->plannedMeals[$date])) {
            continue;
        }

        foreach ($this->plannedMeals[$date] as $meals) {
            foreach ($meals as $meal) {
                if (isset($meal['day_menu_ids']) && !empty($meal['day_menu_ids'])) {
                    $dayMenuIds = array_merge($dayMenuIds, $meal['day_menu_ids']);
                }
            }
        }
    }

    // Éviter les requêtes inutiles si aucun ID trouvé
    if (empty($dayMenuIds)) {
        return redirect()->route('shopping-list.index')->with('selectedIngredients', []);
    }

    // requete optimisée
    $dayMenus = DayMenu::with([
        'recipe:id,name',
        'recipe.ingredients' // Charger les ingr avec la table pivot
    ])->whereIn('id', array_unique($dayMenuIds))
      ->get(['id', 'recipe_id', 'servings']);
        //dd($dayMenus);

    foreach ($dayMenus as $dayMenu) {
        if (!$dayMenu->recipe || !$dayMenu->recipe->ingredients->count()) {
            continue;
        }

        $servings = $dayMenu->servings ?? 1;
        
        foreach ($dayMenu->recipe->ingredients as $ingredient) {
            // Accéder aux données
            $pivot = $ingredient->pivot;
            $key = $ingredient->name . '_' . $pivot->unit;

            if (!isset($ingredients[$key])) {
                $ingredients[$key] = [
                    'name' => $ingredient->name,
                    'unit' => $pivot->unit,
                    'quantity' => 0,
                    'recipes' => []
                ];
            }

            $ingredients[$key]['quantity'] += $pivot->quantity * $servings;

            $recipeName = $dayMenu->recipe->name;
            if (!in_array($recipeName, $ingredients[$key]['recipes'])) {
                $ingredients[$key]['recipes'][] = $recipeName;
            }
        }
    }

    // Rediriger vers la liste de courses avec les ingrédients
    return redirect()->route('shopping-list.index')->with('selectedIngredients', $ingredients);
   }


  

    /**
     * Vérifier si une date a des menus planifiés
     */
    public function hasPlannedMeals($date)
    {
        return isset($this->plannedMeals[$date]) && !empty($this->plannedMeals[$date]);
    }

    /**
     * Basculer le mode de sélection de dates
     */
    public function toggleDateSelectionMode()
    {
        $this->showDateSelection = !$this->showDateSelection;
        $this->selectedDates = [];
    }




    
    // Méthodes pour les statistiques
    public function getTotalPlannedMeals()
    {
        $total = 0;
        foreach ($this->plannedMeals as $dayMeals) {
            foreach ($dayMeals as $mealType => $meals) {
                $total += count($meals);
            }
        }
        return $total;
    }

    public function getUniqueRecipesCount()
    {
        $recipeIds = [];
        foreach ($this->plannedMeals as $dayMeals) {
            foreach ($dayMeals as $mealType => $meals) {
                foreach ($meals as $meal) {
                    // Gérer les deux formats possibles : 'id' et 'recipe_id'
                    $recipeId = $meal['recipe_id'] ?? $meal['id'] ?? null;
                    if ($recipeId) {
                        $recipeIds[] = $recipeId;
                    }
                }
            }
        }
        return count(array_unique($recipeIds));
    }

    public function getCoveredDaysCount()
    {
        $coveredDays = 0;
        foreach ($this->plannedMeals as $dayMeals) {
            $hasAnyMeal = false;
            foreach ($dayMeals as $mealType => $meals) {
                if (count($meals) > 0) {
                    $hasAnyMeal = true;
                    break;
                }
            }
            if ($hasAnyMeal) {
                $coveredDays++;
            }
        }
        return $coveredDays;
    }

    public function getTotalIngredientsCount()
    {
        $ingredientIds = [];
        foreach ($this->plannedMeals as $dayMeals) {
            foreach ($dayMeals as $mealType => $meals) {
                foreach ($meals as $meal) {
                    $recipeId = $meal['recipe_id'] ?? $meal['id'] ?? null;
                    $recipe = Recipe::find($recipeId);
                    if ($recipe) {
                        foreach ($recipe->ingredients as $ingredient) {
                            $ingredientIds[] = $ingredient->id;
                        }
                    }
                }
            }
        }
        return count(array_unique($ingredientIds));
    }

    public function removeMeal($date, $mealType, $dayMenuIds)
    {
        // Supprimer de la bd - gérer plusieurs IDs séparés par des virgules
        if ($dayMenuIds) {
            $ids = is_string($dayMenuIds) ? explode(',', $dayMenuIds) : [$dayMenuIds];
            $ids = array_filter($ids); // Supprimer les valeurs vides

            if (!empty($ids)) {
                DayMenu::whereIn('id', $ids)
                       ->where('menu_id', $this->currentMenu->id)
                       ->delete();
            }
        }

        // Recharger les repas planifiés
        $this->loadPlannedMeals();

        // Si la modal est ouverte pour cette date/repas, recharger ses données
        if ($this->showMealTypeModal && $this->selectedDate === $date && $this->selectedMealType === $mealType) {
            $this->loadMealMenus($date, $mealType);
        }

        session()->flash('message', 'Menu supprimé avec succès !');
    }



    public function clearAllMeals()
    {
        if ($this->currentMenu) {
            DayMenu::where('menu_id', $this->currentMenu->id)->delete();
            $this->loadPlannedMeals();
            session()->flash('message', 'Tous les repas ont été supprimés du menu.');
        }
    }

    public function render()
    {
        // Filtrer les recettes selon la recherche
        $recipesQuery = Recipe::with(['ingredients', 'user']);

        if (!empty($this->searchRecipes)) {
            $recipesQuery->where('name', 'like', '%' . $this->searchRecipes . '%')
                        ->orWhere('description', 'like', '%' . $this->searchRecipes . '%');
        }

        $this->recipes = $recipesQuery->get();

        return view('livewire.menu-planner');
    }
}
