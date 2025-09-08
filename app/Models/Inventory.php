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
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
