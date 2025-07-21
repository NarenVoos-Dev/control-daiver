<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    protected $fillable = ['sale_id', 'product_id', 'quantity', 'price', 'tax_rate' , 'unit_of_measure_id'];

    /**
     * Un item de venta pertenece a una venta.
     */
    public function sale()
    {
        return $this->belongsTo(\App\Models\Sale::class);
    }

    /**
     * Un item de venta corresponde a un producto.
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function unitOfMeasure() 
    { 
        return $this->belongsTo(\App\Models\UnitOfMeasure::class);
    }

}
