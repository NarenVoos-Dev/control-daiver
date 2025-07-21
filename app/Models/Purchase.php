<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'supplier_id', 'date', 'total'];

    /**
     * Una compra pertenece a un negocio.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Una compra es hecha a un proveedor.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Una compra tiene muchos items (productos).
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
