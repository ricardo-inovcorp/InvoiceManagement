<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Armazena uma nova categoria.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $category = Category::create($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Categoria criada com sucesso.',
                'category' => $category
            ]);
        }
        
        return Inertia::render('Articles/Create', [
            'category' => $category,
            'success' => 'Categoria criada com sucesso.',
            'categories' => Category::all()
        ]);
    }
} 