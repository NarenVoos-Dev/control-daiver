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
        'name', 
        'document', 
        'phone', 
        'address',
        'email'
    ];

    /**
     * Un cliente pertenece a un negocio.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
    
    /**
     * Un cliente puede tener muchas ventas.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}