<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'product_id',
        'location_id',
        'type',
        'quantity',
        'reason',
    ];
    
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Un ajuste pertenece a un producto.
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    /**
     * Un ajuste pertenece a un negocio.
     */
    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }
}