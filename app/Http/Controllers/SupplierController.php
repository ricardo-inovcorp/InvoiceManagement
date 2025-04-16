<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\District;
use App\Models\County;
use App\Models\Sector;
use App\Models\OrganizationType;
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
    public function index(Request $request)
    {
        $search = $request->input('search');
        $active = $request->input('active');
        
        $query = Supplier::query();
        
        // Filtrar por termo de pesquisa se fornecido
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }
        
        // Filtrar por estado (ativo/inativo)
        if ($active !== null) {
            $query->where('active', $active);
        }
        
        $suppliers = $query->latest()->paginate(10)
                            ->withQueryString(); // Mantém os parâmetros de query na paginação
        
        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => [
                'search' => $search,
                'active' => $active !== null ? (int)$active : null
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $districts = District::orderBy('name')->get();
        $counties = County::orderBy('name')->get();
        $sectors = Sector::orderBy('name')->get();
        $organizationTypes = OrganizationType::orderBy('name')->get();
        
        return Inertia::render('Suppliers/Create', [
            'districts' => $districts,
            'counties' => $counties,
            'sectors' => $sectors,
            'organizationTypes' => $organizationTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Log::info('Supplier store method called', $request->all());
        
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'document' => ['required', 'string', 'max:9', 'min:9', 'unique:suppliers', 'regex:/^[0-9]{9}$/'],
            'email' => 'required|email|unique:suppliers',
            'phone' => ['required', 'string', 'max:20', 'regex:/^(\+351)?[\s]?9[1236][0-9]{7}$/'],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'county_id' => 'required|exists:counties,id',
            'district_id' => 'required|exists:districts,id',
            'zip_code' => ['required', 'string', 'max:8', 'regex:/^[0-9]{4}-[0-9]{3}$/'],
            'sector_id' => 'nullable|exists:sectors,id',
            'organization_type_id' => 'nullable|exists:organization_types,id',
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
        // Carregar relacionamentos
        $supplier->load(['invoices', 'district', 'county', 'sector', 'organizationType']);
        
        return Inertia::render('Suppliers/Show', [
            'supplier' => $supplier
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        // Carregar relacionamentos
        $supplier->load(['district', 'county', 'sector', 'organizationType']);
        
        // Carregar dados para os selectboxes
        $districts = District::orderBy('name')->get();
        $counties = County::orderBy('name')->get();
        $sectors = Sector::orderBy('name')->get();
        $organizationTypes = OrganizationType::orderBy('name')->get();
        
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
            'districts' => $districts,
            'counties' => $counties,
            'sectors' => $sectors,
            'organizationTypes' => $organizationTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'document' => ['required', 'string', 'max:9', 'min:9', 'regex:/^[0-9]{9}$/', 'unique:suppliers,document,' . $supplier->id],
            'email' => 'required|email|unique:suppliers,email,' . $supplier->id,
            'phone' => ['required', 'string', 'max:20', 'regex:/^(\+351)?[\s]?9[1236][0-9]{7}$/'],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'county_id' => 'required|exists:counties,id',
            'district_id' => 'required|exists:districts,id',
            'zip_code' => ['required', 'string', 'max:8', 'regex:/^[0-9]{4}-[0-9]{3}$/'],
            'sector_id' => 'nullable|exists:sectors,id',
            'organization_type_id' => 'nullable|exists:organization_types,id',
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
