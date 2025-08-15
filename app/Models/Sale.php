<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    
    protected $fillable = ['business_id', 'client_id', 'date', 'subtotal', 'tax', 'total','is_cash', 'status', 'pending_amount'];

    protected $casts = [         
        'is_cash' => 'boolean',
        'date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];
    /**
     * Una venta pertenece a un negocio.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Una venta pertenece a un cliente.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Una venta tiene muchos items (productos).
     */
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Una venta tiene un documento electrónico asociado.
     */
    public function electronicDocument()
    {
        return $this->hasOne(ElectronicDocument::class);
    }

}
