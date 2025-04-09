<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class County extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'district_id'
    ];

    /**
     * Obtém o distrito a que este concelho pertence.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Obtém os fornecedores deste concelho.
     */
    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }
}
