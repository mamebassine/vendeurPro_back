<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'Niang',
            'prenom' => 'Bassine',
            'email' => 'bassinen13@gmail.com',
            'password' => Hash::make('nessiba96'), // Toujours hasher le mot de passe !
            'phone' => '770000000',
            'address' => 'Dakar',
            'role' => 'admin',
            'code_parrainage' => null, // pas de code

        ]);
    }
}
