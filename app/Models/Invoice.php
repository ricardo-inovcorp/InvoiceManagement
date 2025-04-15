<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'supplier_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'base_amount',
        'total_amount',
        'tax_amount',
        'status',
        'validation_status',
        'payment_method',
        'payment_date',
        'file_path',
        'notes'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'base_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];
    
    /**
     * Estados de validação da fatura
     */
    const VALIDATION_PENDING = 'pending';
    const VALIDATION_VALIDATED = 'validated';
    const VALIDATION_VERIFIED = 'verified';

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Obtém os logs associados a esta fatura.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(InvoiceLog::class)->latest();
    }
    
    /**
     * Obtém as transações bancárias associadas a esta fatura.
     */
    public function bankTransactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }
    
    /**
     * Verifica se todos os itens da fatura estão validados.
     */
    public function allItemsValidated(): bool
    {
        // Se não houver itens, retorna falso
        if ($this->items->isEmpty()) {
            return false;
        }
        
        // Verifica se todos os itens estão validados
        foreach ($this->items as $item) {
            if (!$item->isValidated()) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Marca a fatura como validada.
     */
    public function markAsValidated(): bool
    {
        if ($this->validation_status === self::VALIDATION_PENDING && $this->allItemsValidated()) {
            $this->validation_status = self::VALIDATION_VALIDATED;
            return $this->save();
        }
        
        return false;
    }
    
    /**
     * Marca a fatura como verificada.
     */
    public function markAsVerified(): bool
    {
        if ($this->validation_status === self::VALIDATION_VALIDATED) {
            $this->validation_status = self::VALIDATION_VERIFIED;
            return $this->save();
        }
        
        return false;
    }
    
    /**
     * Retorna os itens não validados da fatura.
     */
    public function getInvalidItems()
    {
        return $this->items()->where('is_valid', false)->orWhereNull('article_id')->get();
    }
}
