<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code', 
        'name', 
        'description', 
        'price',
        'category_id', 
        'subcategory_id', 
        'active'
    ];
    
    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
    ];
    
    /**
     * Obtém a categoria a que este artigo pertence.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Obtém a subcategoria a que este artigo pertence.
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }
}
