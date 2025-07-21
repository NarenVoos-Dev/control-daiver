<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'stock'
    ];

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