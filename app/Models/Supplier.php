<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'document',
        'email',
        'phone',
        'address',
        'city',
        'zip_code',
        'notes',
        'active',
        'sector_id',
        'organization_type_id',
        'district_id',
        'county_id'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Obtém as faturas deste fornecedor.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
    
    /**
     * Obtém o setor deste fornecedor.
     */
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }
    
    /**
     * Obtém o tipo de organização deste fornecedor.
     */
    public function organizationType(): BelongsTo
    {
        return $this->belongsTo(OrganizationType::class);
    }
    
    /**
     * Obtém o distrito deste fornecedor.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
    
    /**
     * Obtém o concelho deste fornecedor.
     */
    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }
    
    /**
     * Retorna o nome completo da empresa, incluindo o tipo de organização.
     */
    public function getFullNameAttribute(): string
    {
        if ($this->organizationType) {
            return "{$this->company_name} {$this->organizationType->abbreviation}";
        }
        
        return $this->company_name;
    }
}
