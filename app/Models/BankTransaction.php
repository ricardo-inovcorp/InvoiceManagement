<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bank_account_id',
        'invoice_id',
        'transaction_date',
        'description',
        'amount',
        'type',
        'notes'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Obter a conta bancária associada a esta transação.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Obter a fatura associada a esta transação (se houver).
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Evento de modelo ao criar
     */
    protected static function booted()
    {
        // Atualizar o saldo da conta após uma transação ser criada
        static::created(function ($transaction) {
            $transaction->bankAccount->updateBalance();
        });

        // Atualizar o saldo da conta após uma transação ser atualizada
        static::updated(function ($transaction) {
            $transaction->bankAccount->updateBalance();
        });

        // Atualizar o saldo da conta após uma transação ser excluída
        static::deleted(function ($transaction) {
            $transaction->bankAccount->updateBalance();
        });
    }
}
