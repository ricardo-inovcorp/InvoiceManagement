<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'bank_name',
        'iban',
        'initial_balance',
        'current_balance',
        'active',
        'notes'
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'active' => 'boolean',
    ];
    
    protected $appends = [
        'is_active'
    ];

    /**
     * Obter as transações associadas a esta conta.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    /**
     * Obtém o valor de is_active a partir do campo active
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->active ?? false;
    }

    /**
     * Define o valor de active a partir do campo is_active
     */
    public function setIsActiveAttribute($value): void
    {
        $this->attributes['active'] = $value;
    }

    /**
     * Calcular o saldo atual com base no saldo inicial e nas transações
     */
    public function calculateBalance(): float
    {
        $balance = $this->initial_balance;
        
        $credits = $this->transactions()
            ->where('type', 'credit')
            ->sum('amount');
            
        $debits = $this->transactions()
            ->where('type', 'debit')
            ->sum('amount');
            
        $balance = $balance + $credits - $debits;
        
        return (float) $balance;
    }

    /**
     * Atualizar o saldo atual
     */
    public function updateBalance(): void
    {
        $this->current_balance = $this->calculateBalance();
        $this->save();
    }
}
