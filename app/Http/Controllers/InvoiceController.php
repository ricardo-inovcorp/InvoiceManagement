<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $invoices = Invoice::with('supplier')
            ->latest()
            ->paginate(10);
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $suppliers = Supplier::where('active', true)->get();
        return view('invoices.create', compact('suppliers'));
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
            'notes' => 'nullable|string'
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('invoices');
            $validated['file_path'] = $path;
        }

        $invoice = Invoice::create($validated);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Fatura criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['supplier', 'items']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice): View
    {
        $suppliers = Supplier::where('active', true)->get();
        return view('invoices.edit', compact('invoice', 'suppliers'));
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
            'notes' => 'nullable|string'
        ]);

        if ($request->hasFile('file')) {
            if ($invoice->file_path) {
                Storage::delete($invoice->file_path);
            }
            $path = $request->file('file')->store('invoices');
            $validated['file_path'] = $path;
        }

        $invoice->update($validated);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Fatura atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->file_path) {
            Storage::delete($invoice->file_path);
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Fatura removida com sucesso.');
    }
}
