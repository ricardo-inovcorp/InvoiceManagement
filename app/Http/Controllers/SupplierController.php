<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(10);
        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Suppliers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info('Supplier store method called', $request->all());
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => ['required', 'string', 'max:9', 'min:9', 'unique:suppliers', 'regex:/^[0-9]{9}$/'],
            'email' => 'required|email|unique:suppliers',
            'phone' => ['required', 'string', 'max:20', 'regex:/^(\+351)?[\s]?9[1236][0-9]{7}$/'],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'county' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => ['required', 'string', 'max:8', 'regex:/^[0-9]{4}-[0-9]{3}$/'],
            'notes' => 'nullable|string',
            'active' => 'boolean'
        ], [
            'document.regex' => 'O NIF deve conter 9 dígitos.',
            'document.min' => 'O NIF deve conter 9 dígitos.',
            'document.max' => 'O NIF deve conter 9 dígitos.',
            'phone.regex' => 'O número de telefone deve seguir o formato português (ex: +351 912345678 ou 912345678).',
            'zip_code.regex' => 'O código postal deve estar no formato 0000-000.'
        ]);

        $supplier = Supplier::create($validated);
        Log::info('Supplier created', ['id' => $supplier->id]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Fornecedor criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return Inertia::render('Suppliers/Show', [
            'supplier' => $supplier
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => ['required', 'string', 'max:9', 'min:9', 'regex:/^[0-9]{9}$/', 'unique:suppliers,document,' . $supplier->id],
            'email' => 'required|email|unique:suppliers,email,' . $supplier->id,
            'phone' => ['required', 'string', 'max:20', 'regex:/^(\+351)?[\s]?9[1236][0-9]{7}$/'],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'county' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => ['required', 'string', 'max:8', 'regex:/^[0-9]{4}-[0-9]{3}$/'],
            'notes' => 'nullable|string',
            'active' => 'boolean'
        ], [
            'document.regex' => 'O NIF deve conter 9 dígitos.',
            'document.min' => 'O NIF deve conter 9 dígitos.',
            'document.max' => 'O NIF deve conter 9 dígitos.',
            'phone.regex' => 'O número de telefone deve seguir o formato português (ex: +351 912345678 ou 912345678).',
            'zip_code.regex' => 'O código postal deve estar no formato 0000-000.'
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Fornecedor atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Fornecedor removido com sucesso.');
    }
}
