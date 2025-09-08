<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    use HasFactory;
    
    // Desactivamos los timestamps (created_at, updated_at) para esta tabla pivote si no los necesitas
    public $timestamps = false;

    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'quantity',
    ];

    /**
     * El item pertenece a un producto.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * El item pertenece a un traslado.
     */
    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }
}