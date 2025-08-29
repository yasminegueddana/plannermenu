<?php

namespace App\Exports;

class RecipeShoppingExport
{
    protected $recipe;
    protected $servings;
    protected $ingredients;

    public function __construct($recipe, $servings, $ingredients)
    {
        $this->recipe = $recipe;
        $this->servings = $servings;
        $this->ingredients = $ingredients;
    }

    public function generateCsv()
    {
        $output = fopen('php://temp', 'w');

        // En-tête avec informations de la recette
        fputcsv($output, ['Recette:', $this->recipe->name]);
        fputcsv($output, ['Nombre de personnes:', $this->servings]);
        fputcsv($output, ['Généré le:', now()->format('d/m/Y à H:i')]);
        fputcsv($output, []); // Ligne vide

        // En-têtes des colonnes
        fputcsv($output, ['Ingrédient', 'Quantité', 'Unité', 'Prix Estimé', 'Acheté', 'Calcul']);

        // Données des ingrédients
        foreach ($this->ingredients as $ingredient) {
            fputcsv($output, [
                $ingredient['name'],
                $ingredient['adjusted_quantity'],
                $ingredient['unit'],
                number_format($ingredient['estimated_cost'], 2) . '€',
                $ingredient['is_purchased'] ? 'Oui' : 'Non',
                $ingredient['original_quantity'] . ' × ' . $this->servings
            ]);
        }

        // Ligne vide et total
        fputcsv($output, []);
        $totalCost = collect($this->ingredients)->sum('estimated_cost');
        fputcsv($output, ['TOTAL', '', '', number_format($totalCost, 2) . '€', '', '']);

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}