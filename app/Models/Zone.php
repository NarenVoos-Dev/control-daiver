<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
    ];

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }
    
    public function clients()
    {
        return $this->hasMany(\App\Models\Client::class);
    }
}
