<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'user_id',
        'origin_location_id',
        'destination_location_id',
        'date',
        'notes',
        'status',
    ];
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Un traslado tiene muchos productos (items).
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /**
     * El traslado pertenece a una bodega de origen.
     */
    public function originLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    /**
     * El traslado pertenece a una bodega de destino.
     */
    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    /**
     * El traslado fue creado por un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
