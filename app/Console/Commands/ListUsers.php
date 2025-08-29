<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:list 
                            {--inactive : Afficher seulement les utilisateurs inactifs}
                            {--active : Afficher seulement les utilisateurs actifs}
                            {--admins : Afficher seulement les administrateurs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lister tous les utilisateurs avec leur statut';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = User::query();

        // Filtres
        if ($this->option('inactive')) {
            $query->where('is_active', false);
        } elseif ($this->option('active')) {
            $query->where('is_active', true);
        }

        if ($this->option('admins')) {
            $query->where('role', 'admin');
        }

        $users = $query->orderBy('created_at', 'desc')->get();

        if ($users->isEmpty()) {
            $this->warn('Aucun utilisateur trouvé avec les critères spécifiés.');
            return 0;
        }

        // Préparer les données pour le tableau
        $tableData = [];
        foreach ($users as $user) {
            $tableData[] = [
                'ID' => $user->id,
                'Nom' => $user->name,
                'Email' => $user->email,
                'Rôle' => ucfirst($user->role),
                'Statut' => $user->is_active ? '✅ Actif' : '❌ Inactif',
                'Inscription' => $user->created_at->format('d/m/Y H:i'),
            ];
        }

        // Afficher le tableau
        $this->table([
            'ID', 'Nom', 'Email', 'Rôle', 'Statut', 'Inscription'
        ], $tableData);

        // Statistiques
        $total = $users->count();
        $active = $users->where('is_active', true)->count();
        $inactive = $users->where('is_active', false)->count();
        $admins = $users->where('role', 'admin')->count();

        $this->info("\n📊 Statistiques :");
        $this->line("  Total: {$total} utilisateurs");
        $this->line("  Actifs: {$active}");
        $this->line("  Inactifs: {$inactive}");
        $this->line("  Administrateurs: {$admins}");

        return 0;
    }
}
