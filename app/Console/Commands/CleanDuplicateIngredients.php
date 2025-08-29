<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicateIngredients extends Command
{
    protected $signature = 'ingredients:clean-duplicates';
    protected $description = 'Nettoie les ingrédients doublants en gardant le plus récent';

    public function handle()
    {
        $this->info('🧹 Nettoyage des ingrédients doublants...');

        // Trouver les doublons par nom (insensible à la casse)
        $duplicates = DB::select("
            SELECT LOWER(name) as lower_name, COUNT(*) as count 
            FROM ingredients 
            GROUP BY LOWER(name) 
            HAVING COUNT(*) > 1
        ");

        if (empty($duplicates)) {
            $this->info('✅ Aucun doublon trouvé !');
            return;
        }

        $this->info('📋 Doublons trouvés : ' . count($duplicates));

        $totalDeleted = 0;

        foreach ($duplicates as $duplicate) {
            $this->line("🔍 Traitement de : {$duplicate->lower_name} ({$duplicate->count} occurrences)");

            // Récupérer tous les ingrédients avec ce nom
            $ingredients = Ingredient::whereRaw('LOWER(name) = ?', [$duplicate->lower_name])
                                   ->orderBy('created_at', 'desc')
                                   ->get();

            // Garder le plus récent (premier dans la liste)
            $toKeep = $ingredients->first();
            $toDelete = $ingredients->skip(1);

            $this->line("  ✅ Garder : ID {$toKeep->id} - {$toKeep->name} (créé le {$toKeep->created_at})");

            foreach ($toDelete as $ingredient) {
                $this->line("  🗑️  Supprimer : ID {$ingredient->id} - {$ingredient->name} (créé le {$ingredient->created_at})");
                
                // Vérifier s'il est utilisé dans des recettes
                $usedInRecipes = $ingredient->recipes()->count();
                if ($usedInRecipes > 0) {
                    $this->warn("    ⚠️  Cet ingrédient est utilisé dans {$usedInRecipes} recette(s)");
                    $this->warn("    🔄 Migration des relations vers l'ingrédient conservé...");
                    
                    // Migrer les relations vers l'ingrédient conservé
                    DB::table('recipe_ingredient')
                        ->where('ingredient_id', $ingredient->id)
                        ->update(['ingredient_id' => $toKeep->id]);
                }
                
                $ingredient->delete();
                $totalDeleted++;
            }

            $this->line('');
        }

        $this->info("✅ Nettoyage terminé ! {$totalDeleted} doublons supprimés.");
        
        // Afficher un résumé
        $this->showSummary();
    }

    private function showSummary()
    {
        $this->line('');
        $this->info('📊 RÉSUMÉ DES INGRÉDIENTS :');
        
        $total = Ingredient::count();
        $withUser = Ingredient::whereNotNull('user_id')->count();
        $global = Ingredient::whereNull('user_id')->count();
        
        $this->table(
            ['Type', 'Nombre'],
            [
                ['Total', $total],
                ['Utilisateur', $withUser],
                ['Global', $global],
            ]
        );

        // Afficher les ingrédients les plus utilisés
        $this->line('');
        $this->info('🏆 TOP 10 DES INGRÉDIENTS LES PLUS UTILISÉS :');
        
        $topIngredients = DB::select("
            SELECT i.name, COUNT(ri.recipe_id) as usage_count
            FROM ingredients i
            LEFT JOIN recipe_ingredient ri ON i.id = ri.ingredient_id
            GROUP BY i.id, i.name
            ORDER BY usage_count DESC
            LIMIT 10
        ");

        $tableData = [];
        foreach ($topIngredients as $ingredient) {
            $tableData[] = [$ingredient->name, $ingredient->usage_count];
        }

        $this->table(['Ingrédient', 'Utilisé dans X recettes'], $tableData);
    }
}
