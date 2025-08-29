<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer l'utilisateur admin s'il n'existe pas
        $adminUser = User::where('email', 'admin@menuplanner.com')->first();

        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Administrateur',
                'email' => 'admin@menuplanner.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            echo " Utilisateur admin créé : admin@menuplanner.com / password\n";
        } else {
            echo " Utilisateur admin existe déjà\n";
        }

        // Créer un utilisateur de test
        $testUser = User::where('email', 'test@menuplanner.com')->first();

        if (!$testUser) {
            $testUser = User::create([
                'name' => 'Utilisateur Test',
                'email' => 'test@menuplanner.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            echo "Utilisateur test créé : test@menuplanner.com / password\n";
        } else {
            echo "Utilisateur test existe déjà\n";
        }
    }
}
