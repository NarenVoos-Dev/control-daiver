<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashSession extends Model {
    use HasFactory;
    protected $fillable = [ 
        'business_id', 
        'location_id',
        'user_id_opened', 
        'user_id_closed', 
        'opening_balance', 
        'closing_balance', 
        'calculated_balance', 
        'difference', 
        'status', 
        'notes', 
        'opened_at', 
        'closed_at' ];
    protected $casts = [ 'opened_at' => 'datetime', 'closed_at' => 'datetime' ];
    public function userOpened() { 
        return $this->belongsTo(User::class, 'user_id_opened'); 
    }
    public function userClosed() {
        return $this->belongsTo(User::class, 'user_id_closed'); 
    }
    public function transactions() {
        return $this->hasMany(CashSessionTransaction::class); 
    }
    //Realcion con sucursal
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }
}
