<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Business extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'name', 
        'nit',
        'address',
        'phone',
        'is_active',
        'has_pos_access',
        'license_expires_at'

    ];

    /**
     * Un negocio puede tener muchos usuarios.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Un negocio tiene muchos productos.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Un negocio tiene muchos clientes.
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Un negocio tiene muchos proveedores.
     */
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    /**
     * Un negocio tiene muchas compras.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Un negocio tiene muchas ventas.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
