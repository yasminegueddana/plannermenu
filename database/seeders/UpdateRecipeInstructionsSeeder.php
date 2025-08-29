<?php

namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;

class UpdateRecipeInstructionsSeeder extends Seeder
{
    public function run(): void
    {
        $recipeUpdates = [
            'Spaghetti Bolognaise' => [
                'instructions' => "1. Faire chauffer l'huile d'olive dans une grande poêle à feu moyen.\n\n2. Ajouter l'oignon haché et faire revenir 3-4 minutes jusqu'à ce qu'il soit translucide.\n\n3. Ajouter l'ail émincé et cuire 1 minute supplémentaire.\n\n4. Ajouter la viande hachée et cuire en remuant jusqu'à ce qu'elle soit bien dorée (environ 8-10 minutes).\n\n5. Incorporer les tomates concassées, saler et poivrer. Laisser mijoter 20-25 minutes à feu doux.\n\n6. Pendant ce temps, cuire les spaghettis selon les instructions du paquet dans une grande casserole d'eau salée bouillante.\n\n7. Égoutter les pâtes et les mélanger avec la sauce.\n\n8. Servir immédiatement avec du parmesan râpé si désiré.",
                'prep_time' => 15,
                'cook_time' => 35,
                'servings' => 4,
            ],
            'Salade César' => [
                'instructions' => "1. Préchauffer le four à 180°C.\n\n2. Couper le pain en cubes et les disposer sur une plaque de cuisson.\n\n3. Arroser les croûtons d'huile d'olive et enfourner 10-12 minutes jusqu'à ce qu'ils soient dorés.\n\n4. Préparer la vinaigrette : mélanger l'huile d'olive, l'ail écrasé, le sel et le poivre.\n\n5. Laver et essorer la salade, la couper en morceaux.\n\n6. Dans un grand saladier, mélanger la salade avec la vinaigrette.\n\n7. Ajouter les croûtons et le parmesan râpé.\n\n8. Mélanger délicatement et servir immédiatement.",
                'prep_time' => 20,
                'cook_time' => 12,
                'servings' => 4,
            ],
            'Poulet rôti aux légumes' => [
                'instructions' => "1. Préchauffer le four à 200°C.\n\n2. Laver et éplucher les pommes de terre et les carottes. Les couper en morceaux moyens.\n\n3. Dans un plat allant au four, disposer les légumes et les arroser d'huile d'olive.\n\n4. Saler, poivrer et mélanger les légumes.\n\n5. Préparer le poulet : le badigeonner de beurre fondu, saler et poivrer généreusement.\n\n6. Placer le poulet au centre du plat, sur les légumes.\n\n7. Enfourner pour 1h15-1h30, en arrosant régulièrement avec le jus de cuisson.\n\n8. Vérifier la cuisson avec un thermomètre (74°C à cœur) ou en piquant la cuisse.\n\n9. Laisser reposer 10 minutes avant de découper et servir.",
                'prep_time' => 20,
                'cook_time' => 90,
                'servings' => 6,
            ],
            'Omelette aux champignons' => [
                'instructions' => "1. Nettoyer et émincer les champignons.\n\n2. Faire chauffer une noisette de beurre dans une poêle antiadhésive.\n\n3. Faire revenir les champignons 5-6 minutes jusqu'à ce qu'ils soient dorés. Saler, poivrer et réserver.\n\n4. Casser les œufs dans un bol, saler, poivrer et battre énergiquement.\n\n5. Faire chauffer le reste du beurre dans la poêle à feu moyen.\n\n6. Verser les œufs battus et laisser prendre 1-2 minutes.\n\n7. À l'aide d'une spatule, ramener les bords cuits vers le centre en inclinant la poêle.\n\n8. Quand l'omelette est encore légèrement baveuse, ajouter les champignons sur une moitié.\n\n9. Plier l'omelette en deux et faire glisser dans l'assiette.\n\n10. Servir immédiatement.",
                'prep_time' => 10,
                'cook_time' => 15,
                'servings' => 2,
            ],
        ];

        foreach ($recipeUpdates as $recipeName => $updates) {
            $recipe = Recipe::where('name', $recipeName)->first();
            if ($recipe) {
                $recipe->update($updates);
                echo "✅ Recette mise à jour : {$recipeName}\n";
            } else {
                echo "❌ Recette non trouvée : {$recipeName}\n";
            }
        }

        echo "\n🎉 Mise à jour des instructions terminée !\n";
    }
}
