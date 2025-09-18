<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'supplier_id',
        'purchase_id',
        'egress_id',
        'amount',
        'payment_date',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    /**
     * Un pago pertenece a un proveedor.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Un pago puede estar asociado a una compra.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Cada pago a proveedor es un egreso.
     */
    public function egress(): BelongsTo
    {
        return $this->belongsTo(Egress::class);
    }
}