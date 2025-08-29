<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\User;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index()
    {
        $recipes = Recipe::with(['ingredients', 'user'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(9);
        
        $totalRecipes = Recipe::count();
        $totalUsers = User::count();
        $totalIngredients = Ingredient::count();
        
        return view('welcome', compact('recipes', 'totalRecipes', 'totalUsers', 'totalIngredients'));
    }

    public function show($id)
    {
        $recipe = Recipe::with(['ingredients', 'user'])->findOrFail($id);
        return view('recipes.show', compact('recipe'));
    }
}