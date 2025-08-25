<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashSessionTransaction extends Model {
    use HasFactory;
    protected $fillable = [ 'cash_session_id', 'type', 'amount', 'description', 'source_type', 'source_id' ];
    public function cashSession() { return $this->belongsTo(CashSession::class); }
    public function source() { return $this->morphTo(); }
}
