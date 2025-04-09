<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InvoiceItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Invoice $invoice): View
    {
        $items = $invoice->items()->paginate(10);
        return view('invoice-items.index', compact('invoice', 'items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Invoice $invoice): View
    {
        return view('invoice-items.create', compact('invoice'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        $validated['total_price'] = $validated['quantity'] * $validated['unit_price'];
        $validated['tax_amount'] = $validated['total_price'] * ($validated['tax_rate'] / 100);

        $invoice->items()->create($validated);

        // Atualizar o total da fatura
        $invoice->update([
            'total_amount' => $invoice->items()->sum('total_price'),
            'tax_amount' => $invoice->items()->sum('tax_amount')
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Item adicionado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice, InvoiceItem $item): View
    {
        return view('invoice-items.show', compact('invoice', 'item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice, InvoiceItem $item): View
    {
        return view('invoice-items.edit', compact('invoice', 'item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice, InvoiceItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        $validated['total_price'] = $validated['quantity'] * $validated['unit_price'];
        $validated['tax_amount'] = $validated['total_price'] * ($validated['tax_rate'] / 100);

        $item->update($validated);

        // Atualizar o total da fatura
        $invoice->update([
            'total_amount' => $invoice->items()->sum('total_price'),
            'tax_amount' => $invoice->items()->sum('tax_amount')
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Item atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice, InvoiceItem $item): RedirectResponse
    {
        $item->delete();

        // Atualizar o total da fatura
        $invoice->update([
            'total_amount' => $invoice->items()->sum('total_price'),
            'tax_amount' => $invoice->items()->sum('tax_amount')
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Item removido com sucesso.');
    }
}
