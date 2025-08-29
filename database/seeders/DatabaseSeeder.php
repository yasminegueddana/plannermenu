<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Ordre important : d'abord les utilisateurs, puis les ingrédients, puis les recettes
        $this->call([
            AdminUserSeeder::class,
            UserSeeder::class,
            GlobalIngredientsSeeder::class,  // Ingrédients globaux d'abord
            IngredientSeeder::class,         // Puis ingrédients spécifiques
            RecipeSeeder::class,
            RecipeIngredientsSeeder::class,  // Associer ingrédients aux recettes
        ]);
    }
}
