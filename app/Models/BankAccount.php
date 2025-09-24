<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'account_number',
        'details',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

     /**
     * Una cuenta bancaria pertenece a un negocio.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
