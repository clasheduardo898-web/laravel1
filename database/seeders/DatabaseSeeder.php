<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = \App\Models\User::create([
            'name' => 'Admin', 'email' => 'admin@cortes.com', 'password' => bcrypt('admin1234'),
        ]);
        $admin->assignRole('admin');

        $operario = \App\Models\User::create([
            'name' => 'Operarios', 'email' => 'operarios@cortes.com', 'password' => bcrypt('operarios1234'),
        ]);
        $operario->assignRole('operario');

        $bodega = \App\Models\User::create([
            'name' => 'Bodega', 'email' => 'bodega@cortes.com', 'password' => bcrypt('bodega1234'),
        ]);
        $bodega->assignRole('bodega');
    }
}
