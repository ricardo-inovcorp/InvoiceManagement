<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcategory extends Model
{
    use HasFactory;
    
    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = ['category_id', 'name', 'description'];
    
    /**
     * Obtém a categoria a que esta subcategoria pertence.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Obtém os artigos pertencentes a esta subcategoria.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
