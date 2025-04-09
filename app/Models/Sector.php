<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Obtém os fornecedores deste setor.
     */
    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }
}
