<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeactivateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:deactivate 
                            {email : Email de l\'utilisateur à désactiver}
                            {--activate : Activer au lieu de désactiver}
                            {--force : Forcer l\'action sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Désactiver ou activer un utilisateur par son email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $activate = $this->option('activate');
        $force = $this->option('force');

        // Trouver l'utilisateur
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("Aucun utilisateur trouvé avec l'email : {$email}");
            return 1;
        }

        // Vérifier l'état actuel
        $currentStatus = $user->is_active ? 'actif' : 'inactif';
        $newStatus = $activate ? 'actif' : 'inactif';
        $action = $activate ? 'activer' : 'désactiver';

        $this->info("Utilisateur trouvé :");
        $this->line("  Nom: {$user->name}");
        $this->line("  Email: {$user->email}");
        $this->line("  Rôle: {$user->role}");
        $this->line("  Statut actuel: {$currentStatus}");

        // Vérifier si l'action est nécessaire
        if (($activate && $user->is_active) || (!$activate && !$user->is_active)) {
            $this->warn("L'utilisateur est déjà {$newStatus}. Aucune action nécessaire.");
            return 0;
        }

        // Demander confirmation si pas forcé
        if (!$force) {
            if (!$this->confirm("Voulez-vous vraiment {$action} cet utilisateur ?")) {
                $this->info('Action annulée.');
                return 0;
            }
        }

        // Effectuer l'action
        $user->update(['is_active' => $activate]);

        $this->info("✅ Utilisateur {$action} avec succès !");
        $this->line("  Nouveau statut: {$newStatus}");

        return 0;
    }
}
