<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    protected $fillable = [
        'sale_id', 
        'product_id', 
        'quantity', 
        'price', 
        'tax_rate' , 
        'unit_of_measure_id'  
    ];

    
    /**
     * Un item de venta pertenece a un Producto.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Un item de venta pertenece a una Venta.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Un item de venta tiene una unidad de medida.
     */
    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

}
