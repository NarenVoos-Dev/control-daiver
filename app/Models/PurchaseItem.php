<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;
    
    public $timestamps = false; // Los items no necesitan timestamps propios
    protected $fillable = 
    [
        'purchase_id', 
        'product_id', 
        'quantity', 
        'price',
        'unit_of_measure_id'
    ];

    /**
     * Un item de compra pertenece a una compra.
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Un item de compra corresponde a un producto.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    //Relacion con unidad de medidias
     public function unitOfMeasure()
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }
}
