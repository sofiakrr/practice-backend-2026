<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Администратор',
            'email'    => 'admin@cafe.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Иван Иванов',
            'email'    => 'user@cafe.com',
            'password' => Hash::make('password'),
            'role'     => 'user',
        ]);
    }
}
