<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'business_id',
        'client_id',
        'sale_id',
        'cash_session_id',
        'amount',
        'payment_date',
        'payment_method_id', 
        'bank_account_id',  
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }

    public function sale()
    {
        return $this->belongsTo(\App\Models\Sale::class);
    }
     /**
     * Un pago se hizo con un método de pago.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Un pago puede estar asociado a una cuenta bancaria.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
