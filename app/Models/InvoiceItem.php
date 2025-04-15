<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'article_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'tax_rate',
        'tax_amount',
        'notes',
        'is_valid'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'is_valid' => 'boolean',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
    
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
    
    /**
     * Validar o item da fatura associando-o a um artigo.
     */
    public function validate(Article $article): bool
    {
        $this->article_id = $article->id;
        $this->is_valid = true;
        return $this->save();
    }
    
    /**
     * Verificar se o item está validado (associado a um artigo).
     */
    public function isValidated(): bool
    {
        return $this->is_valid && $this->article_id !== null;
    }
}
