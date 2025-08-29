<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ingrédients spécifiques (non doublants avec les globaux)
        $ingredients = [
            // Viandes et protéines
            ['name' => 'Poulet', 'unite' => 'kg'],
            ['name' => 'Bœuf', 'unite' => 'kg'],
            ['name' => 'Porc', 'unite' => 'kg'],
            ['name' => 'Saumon', 'unite' => 'kg'],
            ['name' => 'Crevettes', 'unite' => 'kg'],

            // Légumes spécifiques
            ['name' => 'Courgette', 'unite' => 'kg'],
            ['name' => 'Brocoli', 'unite' => 'kg'],
            ['name' => 'Épinards', 'unite' => 'kg'],
            ['name' => 'Champignons', 'unite' => 'kg'],
            ['name' => 'Poivrons', 'unite' => 'kg'],

            // Produits laitiers
            ['name' => 'Fromage râpé', 'unite' => 'g'],
            ['name' => 'Crème fraîche', 'unite' => 'ml'],
            ['name' => 'Yaourt', 'unite' => 'pièce'],

            // Fruits
            ['name' => 'Citron', 'unite' => 'pièce'],
            ['name' => 'Pommes', 'unite' => 'kg'],
            ['name' => 'Bananes', 'unite' => 'kg'],

            // Épices et herbes spécifiques
            ['name' => 'Basilic', 'unite' => 'g'],
            ['name' => 'Origan', 'unite' => 'g'],
            ['name' => 'Paprika', 'unite' => 'g'],
            ['name' => 'Cumin', 'unite' => 'g'],
        ];

        foreach ($ingredients as $ingredientData) {
            // Vérifier si l'ingrédient existe déjà (insensible à la casse)
            $exists = Ingredient::whereRaw('LOWER(name) = ?', [strtolower($ingredientData['name'])])->exists();

            if (!$exists) {
                Ingredient::create($ingredientData);
                echo "✅ Ingrédient créé : {$ingredientData['name']}\n";
            } else {
                echo "ℹ️ Ingrédient existe déjà : {$ingredientData['name']}\n";
            }
        }
    }
}
