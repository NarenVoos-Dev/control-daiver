<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Crear un negocio de prueba
            $business = Business::firstOrCreate(
                ['nit' => '900123456-7'],
                ['name' => 'La Avenida']
            );

            // 2. Crear un usuario administrador para ese negocio
            $adminUser = User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin General',
                    'password' => Hash::make('password'), // ¡Cambiar en producción!
                    'business_id' => $business->id,
                ]
            );

            $superAdmin = User::firstOrCreate(
                ['email' => 'super@example.com'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('superpassword'),
                    'business_id' => null, // Importante que sea null
                ]
            );
            $superAdmin->assignRole('super-admin');
            // Nota: Un Super Admin no necesita pertenecer a un 'business'. Puedes hacer el campo 'business_id' nullable.
                        


            // 3. Asignarle el rol de 'admin'
            // Esto asume que el RolesAndPermissionsSeeder ya se ejecutó
            $adminUser->assignRole('admin');
        });
    }
}