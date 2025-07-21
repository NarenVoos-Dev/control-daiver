<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;
    
    protected $fillable = ['product_id', 'type', 'quantity', 'source_type', 'source_id'];

    /**
     * Un movimiento de stock pertenece a un producto.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación polimórfica para el origen del movimiento (puede ser una Venta, Compra, etc.).
     */
    public function source()
    {
        return $this->morphTo();
    }
}