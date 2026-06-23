<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Usuario::create([
            'nome' => 'Igor Varela',
            'username' => 'mr_varela',
            'email' => 'varela@admin.com',
            'password' => bcrypt('admin123'),
            'tipo' => 'admin'
        ]);

        Usuario::create([
            'nome' => 'Wanga Varela',
            'username' => 'ms_varela',
            'email' => 'wanga@varela.com',
            'password' => bcrypt('wanga123'),
            'tipo' => 'user'
        ]);
    }
}
