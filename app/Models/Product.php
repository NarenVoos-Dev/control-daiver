<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'category_id',
        'name',
        'sku',
        'unit_of_measure_id',
        'price',
        'cost',
    ];

    /**
     * Un producto puede estar en muchas bodegas a través de la tabla de inventario.
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'inventory')
            ->withPivot('stock')
            ->withTimestamps();
    }

    /**
     * Relación directa con la tabla de inventario para cálculos.
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * ACCESOR: Calcula el stock total sumando el stock de todas las bodegas.
     */
    public function getTotalStockAttribute(): float
    {
        return $this->inventory->sum('stock');
    }

    /**
     * Un producto pertenece a un negocio.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Un producto pertenece a una categoría.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Un producto tiene una unidad de medida base.
     */
    public function unitOfMeasure() 
    { 
        return $this->belongsTo(UnitOfMeasure::class);
    }

    /**
     * Un producto tiene muchos movimientos de stock.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}