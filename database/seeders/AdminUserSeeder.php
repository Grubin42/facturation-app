<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Utilisateur administrateur créé avec succès.');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Mot de passe: admin123');
        $this->command->info('Pensez à changer ce mot de passe après la première connexion!');
    }
}
