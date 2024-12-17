<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criação de um usuário padrão
        User::create([
            'name' => 'Revenda Mais',
            'email' => 'revenda_mais@test.com',
            'password' => bcrypt('revenda_mais_password'),
        ]);
    }
}
