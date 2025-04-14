<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    
    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'description'];
    
    /**
     * Obtém as subcategorias relacionadas a esta categoria.
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }
    
    /**
     * Obtém os artigos pertencentes diretamente a esta categoria.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
