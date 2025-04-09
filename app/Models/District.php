<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code'
    ];

    /**
     * Obtém os concelhos deste distrito.
     */
    public function counties(): HasMany
    {
        return $this->hasMany(County::class);
    }

    /**
     * Obtém os fornecedores deste distrito.
     */
    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }
}
