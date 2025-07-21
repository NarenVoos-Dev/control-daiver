<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectronicDocument extends Model
{
    use HasFactory;
    
    protected $fillable = ['sale_id', 'status', 'cufe', 'response_payload'];

    protected $casts = [
        'response_payload' => 'array', // Convertir el JSON a array automáticamente
    ];

    /**
     * Un documento electrónico pertenece a una venta.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}