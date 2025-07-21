<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOfMeasure extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'abbreviation',
        'conversion_factor',
    ];

    /**
     * Una unidad de medida pertenece a un negocio.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
