<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RecipeShoppingExport;

class RecipeShoppingController extends Controller
{
    public function exportPdf(Request $request)
    {
        $recipeId = $request->get('recipe_id');
        $servings = $request->get('servings', 4);
        $ingredientsList = $request->get('ingredients', []);

        if (!$recipeId) {
            return redirect()->back()->with('error', 'Aucune recette sélectionnée.');
        }

        $recipe = Recipe::with('ingredients')->find($recipeId);

        if (!$recipe) {
            return redirect()->back()->with('error', 'Recette introuvable.');
        }

        // Générer la liste des ingrédients avec quantités ajustées
        $adjustedIngredients = [];
        foreach ($recipe->ingredients as $ingredient) {
            $originalQuantity = $ingredient->pivot->quantity;
            $adjustedQuantity = $originalQuantity * $servings;
            
            $adjustedIngredients[] = [
                'name' => $ingredient->name,
                'original_quantity' => $originalQuantity,
                'adjusted_quantity' => $adjustedQuantity,
                'unit' => $ingredient->pivot->unit,
                'is_purchased' => $ingredientsList[$ingredient->id]['is_purchased'] ?? false,
            ];
        }


        $pdf = Pdf::loadView('exports.recipe-shopping-pdf', [
            'recipe' => $recipe,
            'servings' => $servings,
            'ingredients' => $adjustedIngredients,
            'generatedAt' => now()->format('d/m/Y à H:i'),
        ]);

        return $pdf->download('liste-courses-' . Str::slug($recipe->name) . '-' . $servings . 'p.pdf');
    }

public function exportExcel(Request $request)
{
    $recipeId = $request->get('recipe_id');
    $servings = $request->get('servings', 4);
    $ingredientsList = $request->get('ingredients', []);

    if (!$recipeId) {
        return redirect()->back()->with('error', 'Aucune recette sélectionnée.');
    }

    $recipe = Recipe::with('ingredients')->find($recipeId);

    if (!$recipe) {
        return redirect()->back()->with('error', 'Recette introuvable.');
    }

    // Générer la liste des ingrédients avec quantités ajustées
    $adjustedIngredients = [];
    foreach ($recipe->ingredients as $ingredient) {
        $originalQuantity = $ingredient->pivot->quantity;
        $adjustedQuantity = $originalQuantity * $servings;
        
        $adjustedIngredients[] = [
            'name' => $ingredient->name,
            'original_quantity' => $originalQuantity,
            'adjusted_quantity' => $adjustedQuantity,
            'unit' => $ingredient->pivot->unit,
            'is_purchased' => $ingredientsList[$ingredient->id]['is_purchased'] ?? false,
        ];
    }

    // Utilisez la version CSV au lieu de Excel::download
    $export = new RecipeShoppingExport($recipe, $servings, $adjustedIngredients);
    $csv = $export->generateCsv();

    $filename = 'liste-courses-' . Str::slug($recipe->name) . '-' . $servings . 'p.csv';

    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
}
}
