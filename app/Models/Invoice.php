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
}
