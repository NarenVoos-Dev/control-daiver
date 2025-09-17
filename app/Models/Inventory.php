<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $table = 'inventory';
    public $incrementing = true;
    protected $fillable = [
        'product_id',
        'location_id',
        'stock',
        'stock_minimo',
    ];
/*
    protected $casts = [
        'stock' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }*/

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
