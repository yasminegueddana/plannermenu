<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class RecipeIngredientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les recettes et ingrédients
        $spaghettiRecipe = Recipe::where('name', 'Spaghetti Bolognaise')->first();
        $saladeRecipe = Recipe::where('name', 'Salade César')->first();
        $pouletRecipe = Recipe::where('name', 'Poulet rôti aux légumes')->first();

        // Récupérer les ingrédients globaux
        $pates = Ingredient::where('name', 'Pâtes')->first();
        $tomates = Ingredient::where('name', 'Tomates')->first();
        $oignons = Ingredient::where('name', 'Oignons')->first();
        $ail = Ingredient::where('name', 'Ail')->first();
        $huileOlive = Ingredient::where('name', 'Huile d\'olive')->first();
        $sel = Ingredient::where('name', 'Sel')->first();
        $poivre = Ingredient::where('name', 'Poivre noir')->first();
        $beurre = Ingredient::where('name', 'Beurre')->first();
        $pommesTerre = Ingredient::where('name', 'Pommes de terre')->first();
        $carottes = Ingredient::where('name', 'Carottes')->first();

        // Ajouter les ingrédients aux recettes
        if ($spaghettiRecipe && $pates && $tomates && $oignons && $ail && $huileOlive) {
            $spaghettiRecipe->ingredients()->sync([
                $pates->id => ['quantity' => 400, 'unit' => 'g'],
                $tomates->id => ['quantity' => 500, 'unit' => 'g'],
                $oignons->id => ['quantity' => 1, 'unit' => 'pièce'],
                $ail->id => ['quantity' => 3, 'unit' => 'g'],
                $huileOlive->id => ['quantity' => 30, 'unit' => 'ml'],
                $sel->id => ['quantity' => 5, 'unit' => 'g'],
                $poivre->id => ['quantity' => 2, 'unit' => 'g'],
            ]);
        }

        if ($saladeRecipe && $huileOlive && $ail && $sel && $poivre) {
            $saladeRecipe->ingredients()->sync([
                $huileOlive->id => ['quantity' => 50, 'unit' => 'ml'],
                $ail->id => ['quantity' => 2, 'unit' => 'g'],
                $sel->id => ['quantity' => 3, 'unit' => 'g'],
                $poivre->id => ['quantity' => 1, 'unit' => 'g'],
            ]);
        }

        if ($pouletRecipe && $pommesTerre && $carottes && $huileOlive && $sel && $poivre) {
            $pouletRecipe->ingredients()->sync([
                $pommesTerre->id => ['quantity' => 800, 'unit' => 'g'],
                $carottes->id => ['quantity' => 300, 'unit' => 'g'],
                $huileOlive->id => ['quantity' => 40, 'unit' => 'ml'],
                $sel->id => ['quantity' => 8, 'unit' => 'g'],
                $poivre->id => ['quantity' => 3, 'unit' => 'g'],
                $beurre->id => ['quantity' => 50, 'unit' => 'g'],
            ]);
        }

        $this->command->info('Ingrédients ajoutés aux recettes avec succès !');
    }
}
