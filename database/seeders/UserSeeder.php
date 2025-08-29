<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'admin seulement s'il n'existe pas déjà
        if (!User::where('email', 'admin@menuplanner.com')->exists()) {
            User::create([
                'name' => 'Administrateur',
                'email' => 'admin@menuplanner.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]);
        }

        // Créer votre utilisateur personnel
        if (!User::where('email', 'yg@gmail.com')->exists()) {
            User::create([
                'name' => 'Yasmine',
                'email' => 'yg@gmail.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_USER,
                'is_active' => true,
            ]);
        }
    }
}
