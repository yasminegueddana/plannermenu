<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GlobalIngredientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supprimer d'abord tous les ingrédients globaux existants pour éviter les doublons
        Ingredient::whereNull('user_id')->delete();

        // Ingrédients globaux de base (créés par le système, user_id = null)
        $globalIngredients = [
            // Épices et assaisonnements
            ['name' => 'Sel', 'unite' => 'g'],
            ['name' => 'Poivre noir', 'unite' => 'g'],
            ['name' => 'Ail', 'unite' => 'g'],
            ['name' => 'Persil', 'unite' => 'g'],
            ['name' => 'Thym', 'unite' => 'g'],

            // Huiles et matières grasses
            ['name' => 'Huile d\'olive', 'unite' => 'ml'],
            ['name' => 'Beurre', 'unite' => 'g'],

            // Produits de base
            ['name' => 'Farine', 'unite' => 'g'],
            ['name' => 'Sucre', 'unite' => 'g'],
            ['name' => 'Lait', 'unite' => 'ml'],
            ['name' => 'Œufs', 'unite' => 'pièce'],

            // Légumes de base
            ['name' => 'Oignons', 'unite' => 'kg'],
            ['name' => 'Tomates', 'unite' => 'kg'],
            ['name' => 'Pommes de terre', 'unite' => 'kg'],
            ['name' => 'Carottes', 'unite' => 'kg'],

            // Féculents
            ['name' => 'Riz', 'unite' => 'g'],
            ['name' => 'Pâtes', 'unite' => 'g'],
            ['name' => 'Pain', 'unite' => 'pièce'],
        ];

        foreach ($globalIngredients as $ingredient) {
            Ingredient::create([
                'name' => $ingredient['name'],
                'unite' => $ingredient['unite'],
                'user_id' => null, // Ingrédient global
            ]);
        }

        $this->command->info(count($globalIngredients) . ' ingrédients globaux créés avec succès !');
    }
}
