<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    
    protected $fillable = 
    [
        'business_id', 
        'name', 
        'document', 
        'phone',
        'address',
        'email'
    ];

    /**
     * Un proveedor pertenece a un negocio.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
    
    /**
     * Un proveedor puede tener muchas compras asociadas.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}