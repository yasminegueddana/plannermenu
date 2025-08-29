<?php

namespace App\Livewire;

use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RecipeShoppingGenerator extends Component
{
    public $selectedRecipeId = null;
    public $selectedRecipe = null;
    public $servings = 4;
    public $ingredientsList = [];
    public $searchTerm = '';
    public $recipes = [];
    public $showIngredients = false;
    public $editingPrice = null;
    public $tempPrice = 0;
    public $fromMenuPlanner = false; // Indique si les ingrédients viennent du planificateur

    public function mount()
    {
        $this->loadRecipes();

        // Vérifier s'il y a des ingrédients sélectionnés depuis le planificateur de menus
        if (session()->has('selectedIngredients')) {
            $selectedIngredients = session('selectedIngredients');
            $this->loadIngredientsFromSession($selectedIngredients);
            session()->forget('selectedIngredients'); // Nettoyer la session
        }
    }

    public function loadRecipes()
    {
        // Afficher toutes les recettes publiées (admin + utilisateurs)
        $query = Recipe::with(['ingredients', 'user']);

        if ($this->searchTerm) {
            $query->where('name', 'like', '%' . $this->searchTerm . '%');
        }

        $this->recipes = $query->get();
    }

    public function updatedSearchTerm()
    {
        $this->loadRecipes();
    }

    public function selectRecipe($recipeId)
    {
        $this->selectedRecipeId = $recipeId;
        $this->selectedRecipe = Recipe::with('ingredients')->find($recipeId);
        $this->generateIngredientsList();
        $this->showIngredients = true;
        $this->fromMenuPlanner = false; // S'assurer que c'est une recette spécifique
    }

    public function updatedServings()
    {
        // Ne recalculer que si on a une recette spécifique (pas depuis le planificateur)
        if ($this->selectedRecipe && !$this->fromMenuPlanner) {
            $this->generateIngredientsList();
        }
    }

    public function generateIngredientsList()
    {
        if (!$this->selectedRecipe) {
            return;
        }

        $this->ingredientsList = [];

        foreach ($this->selectedRecipe->ingredients as $ingredient) {
            $originalQuantity = $ingredient->pivot->quantity;
            $adjustedQuantity = $originalQuantity * $this->servings;

            $this->ingredientsList[] = [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'original_quantity' => $originalQuantity,
                'adjusted_quantity' => $adjustedQuantity,
                'unit' => $ingredient->pivot->unit,
                'is_purchased' => false,
            ];
        }
    }

    /**
     * Charger les ingrédients depuis la session (venant du planificateur de menus)
     */
    public function loadIngredientsFromSession($selectedIngredients)
    {
        $this->ingredientsList = [];
        $this->showIngredients = true;
        $this->fromMenuPlanner = true; // Marquer comme venant du planificateur
        $this->servings = 1; // Réinitialiser à 1 car les quantités sont déjà calculées

        foreach ($selectedIngredients as $ingredient) {
            $this->ingredientsList[] = [
                'id' => null, // Pas d'ID spécifique car c'est un agrégat
                'name' => $ingredient['name'],
                'original_quantity' => $ingredient['quantity'],
                'adjusted_quantity' => $ingredient['quantity'],
                'unit' => $ingredient['unit'],
                'is_purchased' => false,
                'recipes' => $ingredient['recipes'] ?? [], // Recettes qui utilisent cet ingrédient
            ];
        }

        // Afficher un message informatif
        session()->flash('info', 'Liste d\'ingrédients générée à partir des dates sélectionnées dans le planificateur de menus.');
    }



    public function togglePurchased($index)
    {
        $this->ingredientsList[$index]['is_purchased'] = !$this->ingredientsList[$index]['is_purchased'];
    }

    public function getStatistics()
    {
        if (empty($this->ingredientsList)) {
            return [
                'total_items' => 0,
                'purchased_items' => 0,
                'remaining_items' => 0,
                'total_cost' => 0,
                'purchased_cost' => 0,
                'remaining_cost' => 0,
                'completion_percentage' => 0,
            ];
        }

        $totalItems = count($this->ingredientsList);
        $purchasedItems = collect($this->ingredientsList)->where('is_purchased', true)->count();
        $totalCost = collect($this->ingredientsList)->sum('estimated_cost');
        $purchasedCost = collect($this->ingredientsList)->where('is_purchased', true)->sum('estimated_cost');
        $remainingCost = $totalCost - $purchasedCost;

        return [
            'total_items' => $totalItems,
            'purchased_items' => $purchasedItems,
            'remaining_items' => $totalItems - $purchasedItems,
            'total_cost' => $totalCost,
            'purchased_cost' => $purchasedCost,
            'remaining_cost' => $remainingCost,
            'completion_percentage' => $totalItems > 0 ? round(($purchasedItems / $totalItems) * 100, 1) : 0,
        ];
    }

    public function resetSelection()
    {
        $this->selectedRecipeId = null;
        $this->selectedRecipe = null;
        $this->ingredientsList = [];
        $this->showIngredients = false;
        $this->servings = 4;
        $this->fromMenuPlanner = false;
    }

    public function exportToPdf()
    {
        if (empty($this->ingredientsList)) {
            session()->flash('error', 'Aucun ingrédient à exporter.');
            return;
        }

        if ($this->fromMenuPlanner) {
            // Export depuis le planificateur
            return $this->exportMenuPlannerToPdf();
        } else {
            // Export depuis une recette spécifique
            if (!$this->selectedRecipe) {
                session()->flash('error', 'Aucune recette sélectionnée.');
                return;
            }

            // Préparer les données des ingrédients pour l'export
            $ingredientsData = [];
            foreach ($this->ingredientsList as $ingredient) {
                $ingredientsData[$ingredient['id']] = [
                    'estimated_cost' => $ingredient['estimated_cost'],
                    'is_purchased' => $ingredient['is_purchased'],
                ];
            }

            // Construire l'URL avec les paramètres
            $url = route('recipe-shopping.export.pdf') . '?' . http_build_query([
                'recipe_id' => $this->selectedRecipeId,
                'servings' => $this->servings,
                'ingredients' => $ingredientsData,
            ]);

            return $this->redirect($url);
        }
    }

    public function exportToExcel()
    {
        if (empty($this->ingredientsList)) {
            session()->flash('error', 'Aucun ingrédient à exporter.');
            return;
        }

        if ($this->fromMenuPlanner) {
            // Export depuis le planificateur
            return $this->exportMenuPlannerToExcel();
        } else {
            // Export depuis une recette spécifique
            if (!$this->selectedRecipe) {
                session()->flash('error', 'Aucune recette sélectionnée.');
                return;
            }

            // Préparer les données des ingrédients pour l'export
            $ingredientsData = [];
            foreach ($this->ingredientsList as $ingredient) {
                $ingredientsData[$ingredient['id']] = [
                    'is_purchased' => $ingredient['is_purchased'],
                ];
            }

            // Construire l'URL avec les paramètres
            $url = route('recipe-shopping.export.excel') . '?' . http_build_query([
                'recipe_id' => $this->selectedRecipeId,
                'servings' => $this->servings,
                'ingredients' => $ingredientsData,
            ]);

            return $this->redirect($url);
        }
    }

    private function exportMenuPlannerToPdf()
    {
        // Préparer les données pour l'export PDF du planificateur
        $ingredientsData = [];
        foreach ($this->ingredientsList as $ingredient) {
            $ingredientsData[] = [
                'name' => $ingredient['name'],
                'quantity' => $ingredient['adjusted_quantity'],
                'unit' => $ingredient['unit'],
                'is_purchased' => $ingredient['is_purchased'],
                'recipes' => $ingredient['recipes'] ?? [],
            ];
        }

        // Stocker les données en session temporairement
        session()->put('menu_planner_export_data', [
            'ingredients' => $ingredientsData,
            'type' => 'pdf'
        ]);

        // Rediriger vers la route d'export du planificateur
        return $this->redirect(route('shopping-list.export.pdf'));
    }

    private function exportMenuPlannerToExcel()
    {
        // Préparer les données pour l'export Excel du planificateur
        $ingredientsData = [];
        foreach ($this->ingredientsList as $ingredient) {
            $ingredientsData[] = [
                'name' => $ingredient['name'],
                'quantity' => $ingredient['adjusted_quantity'],
                'unit' => $ingredient['unit'],
                'is_purchased' => $ingredient['is_purchased'],
                'recipes' => $ingredient['recipes'] ?? [],
            ];
        }

        // Stocker les données en session temporairement
        session()->put('menu_planner_export_data', [
            'ingredients' => $ingredientsData,
            'type' => 'excel'
        ]);

        // Rediriger vers la route d'export du planificateur
        return $this->redirect(route('shopping-list.export.excel'));
    }

    public function render()
    {
        return view('livewire.recipe-shopping-generator');
    }
}