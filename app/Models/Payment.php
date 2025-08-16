<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'business_id',
        'client_id',
        'sale_id',
        'amount',
        'payment_date',
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
}
