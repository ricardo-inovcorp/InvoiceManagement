<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceLog;
use App\Models\Supplier;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $dueDateStart = $request->input('due_date_start');
        $dueDateEnd = $request->input('due_date_end');
        
        $query = Invoice::with('supplier');
        
        // Filtrar por termo de pesquisa se fornecido
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($query) use ($search) {
                      $query->where('company_name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filtrar por status
        if ($status) {
            $query->where('status', $status);
        }
        
        // Filtrar por data de vencimento - início
        if ($dueDateStart) {
            $query->whereDate('due_date', '>=', $dueDateStart);
        }
        
        // Filtrar por data de vencimento - fim
        if ($dueDateEnd) {
            $query->whereDate('due_date', '<=', $dueDateEnd);
        }
        
        $invoices = $query->latest()->paginate(10)
                        ->withQueryString(); // Mantém os parâmetros de query na paginação
        
        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'due_date_start' => $dueDateStart,
                'due_date_end' => $dueDateEnd
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $suppliers = Supplier::where('active', true)->get();
        $selectedSupplierId = $request->input('supplier_id');
        $selectedSupplier = null;
        
        if ($selectedSupplierId) {
            $selectedSupplier = Supplier::find($selectedSupplierId);
        }
        
        return Inertia::render('Invoices/Create', [
            'suppliers' => $suppliers,
            'selectedSupplier' => $selectedSupplier
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:50|unique:invoices',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'total_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'payment_method' => 'nullable|string|max:50',
            'payment_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf|max:10240',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'items.*.tax_amount' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0'
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('invoices');
            $validated['file_path'] = $path;
        }

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        DB::beginTransaction();
        try {
            $invoice = Invoice::create($validated);

            foreach ($items as $itemData) {
                $invoice->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $itemData['tax_rate'],
                    'tax_amount' => $itemData['tax_amount'],
                    'total_price' => $itemData['total']
                ]);
            }

            // Registrar log de criação
            $this->logActivity($invoice, 'created', 'Fatura criada', null, $invoice->toArray());

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Erro ao criar a fatura: ' . $e->getMessage()]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Fatura criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): Response
    {
        $invoice->load(['supplier', 'items', 'logs.user']);
        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice): Response
    {
        $suppliers = Supplier::where('active', true)->get();
        $invoice->load('items');
        
        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice,
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'required|string|max:50|unique:invoices,invoice_number,' . $invoice->id,
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'total_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'payment_method' => 'nullable|string|max:50',
            'payment_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf|max:10240',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|exists:invoice_items,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'items.*.tax_amount' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0'
        ]);

        // Armazenar valores antigos para o log
        $oldValues = $invoice->toArray();
        
        if ($request->hasFile('file')) {
            if ($invoice->file_path) {
                Storage::delete($invoice->file_path);
            }
            $path = $request->file('file')->store('invoices');
            $validated['file_path'] = $path;
        }

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        DB::beginTransaction();
        try {
            $invoice->update($validated);

            $sentItemIds = collect($items)->pluck('id')->filter()->toArray();
            
            $invoice->items()->whereNotIn('id', $sentItemIds)->delete();
            
            foreach ($items as $itemData) {
                if (isset($itemData['id'])) {
                    $item = InvoiceItem::find($itemData['id']);
                    if ($item) {
                        $item->update([
                            'description' => $itemData['description'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'tax_rate' => $itemData['tax_rate'],
                            'tax_amount' => $itemData['tax_amount'],
                            'total_price' => $itemData['total']
                        ]);
                    }
                } else {
                    $invoice->items()->create([
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'tax_rate' => $itemData['tax_rate'],
                        'tax_amount' => $itemData['tax_amount'],
                        'total_price' => $itemData['total']
                    ]);
                }
            }

            // Registrar log de atualização
            $this->logActivity($invoice, 'updated', 'Fatura editada', $oldValues, $invoice->fresh()->toArray());

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Erro ao atualizar a fatura: ' . $e->getMessage()]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Fatura editada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $oldValues = $invoice->toArray();
        
        DB::beginTransaction();
        try {
            if ($invoice->file_path) {
                Storage::delete($invoice->file_path);
            }

            $invoice->items()->delete();
            
            // Registrar log de exclusão
            $this->logActivity($invoice, 'deleted', 'Fatura removida', $oldValues, null);
            
            $invoice->delete();
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Erro ao remover a fatura: ' . $e->getMessage()]);
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Fatura removida com sucesso.');
    }
    
    /**
     * Registra um log de atividade para a fatura.
     *
     * @param Invoice $invoice A fatura
     * @param string $action A ação realizada (created, updated, deleted)
     * @param string $description Descrição da ação
     * @param array|null $oldValues Valores antigos
     * @param array|null $newValues Novos valores
     * @return InvoiceLog
     */
    private function logActivity(Invoice $invoice, string $action, string $description, ?array $oldValues, ?array $newValues): InvoiceLog
    {
        return $invoice->logs()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
