<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'business_id',
        'zone_id',
        'name', 
        'document', 
        'phone', 
        'address',
        'email',
        'credit_limit'
    ];

    /**
     * Un cliente pertenece a un negocio.
     */
    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }
    
    /**
     * Un cliente puede tener muchas ventas.
     */
    public function sales()
    {
        return $this->hasMany(\App\Models\Sale::class);
    }

    public function zone()
    {
        return $this->belongsTo(\App\Models\Zone::class);
    }
    /**
     * Calcula la deuda actual del cliente sumando el total de sus ventas
     * que no están marcadas como 'Pagada'.
     */
    public function getCurrentDebt(): float
    {
        return $this->sales()
        ->where('status', '!=', 'Pagada')
        ->sum('total');
    }
}