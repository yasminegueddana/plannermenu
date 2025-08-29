<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\User;
use App\Models\Ingredient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Utiliser l'utilisateur admin pour créer les recettes d'exemple
        $user = User::where('role', User::ROLE_ADMIN)->first();

        // Si pas d'admin trouvé, utiliser le premier utilisateur
        if (!$user) {
            $user = User::first();
        }

        // Si toujours pas d'utilisateur, en créer un
        if (!$user) {
            $user = User::create([
                'name' => 'Admin Seeder',
                'email' => 'admin.seeder@menuplanner.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]);
        }

        // Créer quelques recettes d'exemple avec instructions détaillées
        $recipes = [
            [
                'name' => 'Spaghetti Bolognaise',
                'description' => 'Délicieuses pâtes à la sauce bolognaise maison',
                'instructions' => "1. Faire chauffer l'huile d'olive dans une grande poêle à feu moyen.\n\n2. Ajouter l'oignon haché et faire revenir 3-4 minutes jusqu'à ce qu'il soit translucide.\n\n3. Ajouter l'ail émincé et cuire 1 minute supplémentaire.\n\n4. Ajouter la viande hachée et cuire en remuant jusqu'à ce qu'elle soit bien dorée (environ 8-10 minutes).\n\n5. Incorporer les tomates concassées, saler et poivrer. Laisser mijoter 20-25 minutes à feu doux.\n\n6. Pendant ce temps, cuire les spaghettis selon les instructions du paquet dans une grande casserole d'eau salée bouillante.\n\n7. Égoutter les pâtes et les mélanger avec la sauce.\n\n8. Servir immédiatement avec du parmesan râpé si désiré.",
                'prep_time' => 15,
                'cook_time' => 35,
                'servings' => 4,
                'user_id' => $user->id,
            ],
            [
                'name' => 'Salade César',
                'description' => 'Salade fraîche avec croûtons et parmesan',
                'instructions' => "1. Préchauffer le four à 180°C.\n\n2. Couper le pain en cubes et les disposer sur une plaque de cuisson.\n\n3. Arroser les croûtons d'huile d'olive et enfourner 10-12 minutes jusqu'à ce qu'ils soient dorés.\n\n4. Préparer la vinaigrette : mélanger l'huile d'olive, l'ail écrasé, le sel et le poivre.\n\n5. Laver et essorer la salade, la couper en morceaux.\n\n6. Dans un grand saladier, mélanger la salade avec la vinaigrette.\n\n7. Ajouter les croûtons et le parmesan râpé.\n\n8. Mélanger délicatement et servir immédiatement.",
                'prep_time' => 20,
                'cook_time' => 12,
                'servings' => 4,
                'user_id' => $user->id,
            ],
            [
                'name' => 'Poulet rôti aux légumes',
                'description' => 'Poulet rôti accompagné de légumes de saison',
                'instructions' => "1. Préchauffer le four à 200°C.\n\n2. Laver et éplucher les pommes de terre et les carottes. Les couper en morceaux moyens.\n\n3. Dans un plat allant au four, disposer les légumes et les arroser d'huile d'olive.\n\n4. Saler, poivrer et mélanger les légumes.\n\n5. Préparer le poulet : le badigeonner de beurre fondu, saler et poivrer généreusement.\n\n6. Placer le poulet au centre du plat, sur les légumes.\n\n7. Enfourner pour 1h15-1h30, en arrosant régulièrement avec le jus de cuisson.\n\n8. Vérifier la cuisson avec un thermomètre (74°C à cœur) ou en piquant la cuisse.\n\n9. Laisser reposer 10 minutes avant de découper et servir.",
                'prep_time' => 20,
                'cook_time' => 90,
                'servings' => 6,
                'user_id' => $user->id,
            ],
            [
                'name' => 'Omelette aux champignons',
                'description' => 'Omelette moelleuse aux champignons frais',
                'instructions' => "1. Nettoyer et émincer les champignons.\n\n2. Faire chauffer une noisette de beurre dans une poêle antiadhésive.\n\n3. Faire revenir les champignons 5-6 minutes jusqu'à ce qu'ils soient dorés. Saler, poivrer et réserver.\n\n4. Casser les œufs dans un bol, saler, poivrer et battre énergiquement.\n\n5. Faire chauffer le reste du beurre dans la poêle à feu moyen.\n\n6. Verser les œufs battus et laisser prendre 1-2 minutes.\n\n7. À l'aide d'une spatule, ramener les bords cuits vers le centre en inclinant la poêle.\n\n8. Quand l'omelette est encore légèrement baveuse, ajouter les champignons sur une moitié.\n\n9. Plier l'omelette en deux et faire glisser dans l'assiette.\n\n10. Servir immédiatement.",
                'prep_time' => 10,
                'cook_time' => 15,
                'servings' => 2,
                'user_id' => $user->id,
            ],
        ];

        foreach ($recipes as $recipeData) {
            $recipe = Recipe::create($recipeData);

            // Ajouter quelques ingrédients à chaque recette
            $this->addIngredientsToRecipe($recipe);
        }
    }

    private function addIngredientsToRecipe(Recipe $recipe): void
    {
        $ingredients = Ingredient::all();

        switch ($recipe->name) {
            case 'Spaghetti Bolognaise':
                $recipe->ingredients()->attach([
                    $ingredients->where('name', 'Pâtes')->first()->id => ['quantity' => 400, 'unit' => 'g'],
                    $ingredients->where('name', 'Tomate')->first()->id => ['quantity' => 0.5, 'unit' => 'kg'],
                    $ingredients->where('name', 'Oignon')->first()->id => ['quantity' => 0.2, 'unit' => 'kg'],
                    $ingredients->where('name', 'Ail')->first()->id => ['quantity' => 10, 'unit' => 'g'],
                    $ingredients->where('name', 'Bœuf')->first()->id => ['quantity' => 0.5, 'unit' => 'kg'],
                ]);
                break;

            case 'Salade César':
                $recipe->ingredients()->attach([
                    $ingredients->where('name', 'Fromage')->first()->id => ['quantity' => 100, 'unit' => 'g'],
                    $ingredients->where('name', 'Pain')->first()->id => ['quantity' => 2, 'unit' => 'pièce'],
                    $ingredients->where('name', 'Huile d\'olive')->first()->id => ['quantity' => 50, 'unit' => 'ml'],
                ]);
                break;

            case 'Poulet rôti aux légumes':
                $recipe->ingredients()->attach([
                    $ingredients->where('name', 'Poulet')->first()->id => ['quantity' => 1.5, 'unit' => 'kg'],
                    $ingredients->where('name', 'Carotte')->first()->id => ['quantity' => 0.5, 'unit' => 'kg'],
                    $ingredients->where('name', 'Pomme de terre')->first()->id => ['quantity' => 1, 'unit' => 'kg'],
                ]);
                break;

            case 'Omelette aux champignons':
                $recipe->ingredients()->attach([
                    $ingredients->where('name', 'Œufs')->first()->id => ['quantity' => 6, 'unit' => 'pièce'],
                    $ingredients->where('name', 'Beurre')->first()->id => ['quantity' => 30, 'unit' => 'g'],
                ]);
                break;
        }
    }
}
