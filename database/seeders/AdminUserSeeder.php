<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nos aseguramos de que exista el rol admin
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web']
        );

        // Creamos el usuario si no existe
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@hospital.uncu.edu.ar'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('12345678'), 
                'dni' => '99999999',
                'telefono' => '999999999',
            ]
        );

        // Le asignamos el rol
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
        }
    }
}
