<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            //RolesAndPermissionsSeeder::class,
            //UserSeeder::class, 
            //UnitOfMeasureSeeder::class, // <-- AÑADE ESTA LÍNEA
              // Luego crea el usuario y le asigna el rol

            // Aquí puedes añadir otros seeders en el futuro
            //PRUEBAS
             ProductAndPurchaseSeeder::class,
        ]);
    }
}
