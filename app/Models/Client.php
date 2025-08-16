<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'business_id',
        'zone_id',
        'name', 
        'document', 
        'phone', 
        'address',
        'email',
        'credit_limit'
    ];
     protected $casts = [
        'credit_limit' => 'decimal:2'
    ];

    /**
     * Un cliente pertenece a un negocio.
     */
    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }
    
    /**
     * Un cliente puede tener muchas ventas.
     */
    public function sales()
    {
        return $this->hasMany(\App\Models\Sale::class);
    }

    public function zone()
    {
        return $this->belongsTo(\App\Models\Zone::class);
    }
    /**
     * Calcula la deuda actual del cliente sumando el total de sus ventas
     * que no están marcadas como 'Pagada'.
     */
    public function getCurrentDebt(): float
    {
        return $this->sales()
        ->where('status', 'Pendiente')
        ->sum('pending_amount');
    }

     // NUEVO MÉTODO: Verificar si puede hacer una compra a crédito
    public function canPurchaseOnCredit($amount)
    {
        if ($this->credit_limit <= 0) {
            return [
                'can_purchase' => false,
                'reason' => 'Cliente sin límite de crédito asignado'
            ];
        }
        
        $currentDebt = $this->getCurrentDebt();
        $newDebt = $currentDebt + $amount;
        
        if ($newDebt <= $this->credit_limit) {
            return [
                'can_purchase' => true,
                'current_debt' => $currentDebt,
                'available_credit' => $this->credit_limit - $currentDebt
            ];
        } else {
            return [
                'can_purchase' => false,
                'reason' => 'Excede el límite de crédito',
                'current_debt' => $currentDebt,
                'credit_limit' => $this->credit_limit,
                'excess_amount' => $newDebt - $this->credit_limit
            ];
        }
    }

    // NUEVO MÉTODO: Obtener estadísticas de crédito
    public function getCreditStats()
    {
        $currentDebt = $this->getCurrentDebt();
        $availableCredit = max(0, $this->credit_limit - $currentDebt);
        $creditUtilization = $this->credit_limit > 0 ? ($currentDebt / $this->credit_limit) * 100 : 0;

        return [
            'credit_limit' => $this->credit_limit,
            'current_debt' => $currentDebt,
            'available_credit' => $availableCredit,
            'credit_utilization_percentage' => round($creditUtilization, 2),
            'is_over_limit' => $currentDebt > $this->credit_limit
        ];
    }

    // Scope para clientes con límite de crédito
    public function scopeWithCreditLimit($query)
    {
        return $query->where('credit_limit', '>', 0);
    }

    // Scope para clientes que han excedido su límite
    public function scopeOverCreditLimit($query)
    {
        return $query->whereHas('sales', function($subQuery) {
            $subQuery->where('is_cash', false)
                     ->where(function($q) {
                         $q->where('paid', false)->orWhereNull('paid');
                     });
        })
        ->whereRaw('(SELECT SUM(total) FROM sales WHERE client_id = clients.id AND is_cash = false AND (paid = false OR paid IS NULL)) > credit_limit');
    }

}