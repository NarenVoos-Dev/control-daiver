<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Limpiar caché de roles y permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- CREAR PERMISOS ---
        // Usaremos un array para definirlos de forma más limpia.
        $permissions = [
            // Permisos de Productos
            'product.view',
            'product.create',
            'product.edit',
            'product.delete',

            // Permisos de Ventas
            'sale.view',
            'sale.create',
            'sale.edit',
            'sale.delete',

            // Permisos de Compras
            'purchase.view',
            'purchase.create',
            'purchase.edit',
            'purchase.delete',

            // Permisos de Clientes 
            'client.view',
            'client.create',
            'client.edit',
            'client.delete',
            // Permisos de Proveedores
            'supplier.view',
            'supplier.create',
            'supplier.edit',
            'supplier.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // --- CREAR ROLES ---
        $adminRole = Role::findOrCreate('admin', 'web');
        $vendedorRole = Role::findOrCreate('vendedor', 'web');
        $bodegueroRole = Role::findOrCreate('bodeguero', 'web');
        Role::findOrCreate('super-admin', 'web');

        // --- ASIGNAR PERMISOS A ROLES ---

        // El Admin tiene todos los permisos
        $adminRole->givePermissionTo(Permission::all());

        // El Vendedor puede ver productos, gestionar clientes y crear ventas
        $vendedorRole->givePermissionTo([
            'product.view',
            'client.view',
            'client.create',
            'client.edit',
            'sale.view',
            'sale.create',
        ]);
        
        // El Bodeguero puede ver y gestionar productos, y gestionar compras
        $bodegueroRole->givePermissionTo([
            'product.view',
            'product.create',
            'product.edit',
            'purchase.view',
            'purchase.create',
            'supplier.view',
            'supplier.create',
        ]);
    }
}