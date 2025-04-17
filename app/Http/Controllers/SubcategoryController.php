<?php

namespace App\Http\Controllers;

use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubcategoryController extends Controller
{
    /**
     * Armazena uma nova subcategoria.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);
        
        $subcategory = Subcategory::create($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subcategoria criada com sucesso.',
                'subcategory' => $subcategory
            ]);
        }
        
        return Inertia::render('Articles/Create', [
            'subcategory' => $subcategory,
            'success' => 'Subcategoria criada com sucesso.',
            'categories' => Category::all()
        ]);
    }
} 