<?php

namespace Database\Seeders;

use App\Models\Admin\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'title' => 'Owner',        'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'title' => 'Player',       'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'title' => 'SystemWallet', 'created_at' => now(), 'updated_at' => now()],
        ];

        Role::insert($roles);
    }
}
