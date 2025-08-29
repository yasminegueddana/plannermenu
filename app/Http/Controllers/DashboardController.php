<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\User;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\DayMenu;
use App\Livewire\MenuPlanner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Données de base
            $totalIngredients = Ingredient::count();
            $totalUsers = User::count();
            $totalMenus = Menu::count();

            // Répartition des rôles
            $roleDistribution = User::select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->get();

            // Repas planifiés
            $mealsPerWeek = DayMenu::whereBetween('created_at', [
                now()->startOfWeek(), 
                now()->endOfWeek()
            ])->count();

            $mealsPerMonth = DayMenu::whereBetween('created_at', [
                now()->startOfMonth(), 
                now()->endOfMonth()
            ])->count();

             // Jours avec repas

            $coveredDays = 0;
            if (Auth::check()) {
                $menuPlanner = app(MenuPlanner::class);
                $menuPlanner->mount(); // charge les données
                $coveredDays = $menuPlanner->getCoveredDaysCount();
            }
             

            // Top recettes
            $topRecipes = DayMenu::select('recipe_id', DB::raw('count(*) as total'))
                ->whereNotNull('recipe_id')
                ->groupBy('recipe_id')
                ->orderByDesc('total')
                ->with('recipe')
                ->limit(5)
                ->get();

            // Recettes par utilisateur
            $recipesPerUser = User::withCount('recipes')->get();

            // Évolution mensuelle
            $recipesByMonth = Recipe::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

            return view('dashboard', compact(
                'totalIngredients',
                'totalUsers',
                'roleDistribution',
                'totalMenus',
                'mealsPerWeek',
                'mealsPerMonth',
                'coveredDays',
                'topRecipes',
                'recipesPerUser',
                'recipesByMonth'
            ));

        } catch (\Exception $e) {
            // Données par défaut en cas d'erreur
            return view('dashboard', [
                'totalIngredients' => 0,
                'totalUsers' => 0,
                'roleDistribution' => collect(),
                'totalMenus' => 0,
                'mealsPerWeek' => 0,
                'mealsPerMonth' => 0,
                'coveredDays' => 0,
                'topRecipes' => collect(),
                'recipesPerUser' => collect(),
                'recipesByMonth' => collect()
            ]);
        }
    }
}