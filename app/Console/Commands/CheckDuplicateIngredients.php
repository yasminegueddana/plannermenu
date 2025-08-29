<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDuplicateIngredients extends Command
{
    protected $signature = 'ingredients:check-duplicates';
    protected $description = 'Vérifie s\'il y a des ingrédients doublants sans les supprimer';

    public function handle()
    {
        $this->info('🔍 Vérification des doublons d\'ingrédients...');

        // Trouver les doublons par nom (insensible à la casse)
        $duplicates = DB::select("
            SELECT LOWER(name) as lower_name, COUNT(*) as count,
                   GROUP_CONCAT(CONCAT(id, ':', name, ' (', created_at, ')') SEPARATOR ' | ') as details
            FROM ingredients 
            GROUP BY LOWER(name) 
            HAVING COUNT(*) > 1
            ORDER BY count DESC
        ");

        if (empty($duplicates)) {
            $this->info('✅ Aucun doublon trouvé ! Base de données propre.');
            return;
        }

        $this->warn('⚠️  ' . count($duplicates) . ' groupe(s) de doublons trouvé(s) :');
        $this->line('');

        foreach ($duplicates as $duplicate) {
            $this->line("🔸 <fg=yellow>{$duplicate->lower_name}</> ({$duplicate->count} occurrences)");
            $this->line("   {$duplicate->details}");
            $this->line('');
        }

        $this->line('💡 Pour nettoyer automatiquement, exécutez :');
        $this->line('   <fg=green>php artisan ingredients:clean-duplicates</>');
    }
}
