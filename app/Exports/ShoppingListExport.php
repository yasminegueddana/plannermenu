<?php

namespace App\Exports;

class ShoppingListExport
{
    protected $ingredientsList;
    protected $menuName;
    protected $date;

    public function __construct($ingredientsList, $menuName, $date)
    {
        $this->ingredientsList = $ingredientsList;
        $this->menuName = $menuName;
        $this->date = $date;
    }

    public function generateCsv()
    {
        $output = fopen('php://temp', 'w');

        fputcsv($output, ['Liste de Courses - ' . $this->menuName]);
        fputcsv($output, ['Générée le ' . $this->date->format('d/m/Y à H:i')]);
        fputcsv($output, []); // Ligne vide

        
        fputcsv($output, [
            'Ingrédient',
            'Quantité',
            'Unité',
            'Utilisé dans',
            'Acheté',
            'Prix estimé'
        ]);

        // Données
        foreach ($this->ingredientsList as $ingredient) {
            fputcsv($output, [
                $ingredient['name'],
                $ingredient['quantity'],
                $ingredient['unit'],
                implode(', ', $ingredient['recipes']),
                $ingredient['is_purchased'] ? 'Oui' : 'Non',
                $ingredient['estimated_cost'] > 0 ? number_format($ingredient['estimated_cost'], 2) . '€' : '-'
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
