<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\BankAccount;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BankTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $type = $request->input('type');
        $bankAccountId = $request->input('bank_account_id');
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $query = BankTransaction::with(['bankAccount', 'invoice']);
        
        // Filtrar por termo de pesquisa
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function($query) use ($search) {
                      $query->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filtrar por tipo (débito/crédito)
        if ($type) {
            $query->where('type', $type);
        }
        
        // Filtrar por conta bancária
        if ($bankAccountId) {
            $query->where('bank_account_id', $bankAccountId);
        }
        
        // Filtrar por data - início
        if ($dateStart) {
            $query->whereDate('transaction_date', '>=', $dateStart);
        }
        
        // Filtrar por data - fim
        if ($dateEnd) {
            $query->whereDate('transaction_date', '<=', $dateEnd);
        }
        
        $transactions = $query->latest('transaction_date')->paginate(15)->withQueryString();
        $bankAccounts = BankAccount::where('active', true)->get(['id', 'name', 'bank_name']);

        return Inertia::render('BankTransactions/Index', [
            'transactions' => $transactions,
            'bankAccounts' => $bankAccounts,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'bank_account_id' => $bankAccountId,
                'date_start' => $dateStart,
                'date_end' => $dateEnd
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $bankAccounts = BankAccount::where('active', true)->get(['id', 'name', 'bank_name', 'current_balance']);
        $invoiceId = $request->input('invoice_id');
        $invoice = null;
        
        if ($invoiceId) {
            $invoice = Invoice::with('supplier')->find($invoiceId);
        }
        
        $pendingInvoices = Invoice::where('status', 'pending')
            ->with('supplier')
            ->get(['id', 'invoice_number', 'supplier_id', 'total_amount', 'due_date']);
        
        return Inertia::render('BankTransactions/Create', [
            'bankAccounts' => $bankAccounts,
            'selectedInvoice' => $invoice,
            'pendingInvoices' => $pendingInvoices
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:credit,debit',
            'notes' => 'nullable|string',
        ]);
        
        // Se for um débito com fatura, verificar se já existe transação para essa fatura
        if ($validated['type'] === 'debit' && $validated['invoice_id']) {
            $existingTransaction = BankTransaction::where('invoice_id', $validated['invoice_id'])
                ->where('type', 'debit')
                ->first();
                
            if ($existingTransaction) {
                return redirect()->back()->withErrors([
                    'invoice_id' => 'Esta fatura já possui uma transação de pagamento associada.'
                ]);
            }
            
            // Verificar se o valor da transação é igual ao da fatura
            $invoice = Invoice::find($validated['invoice_id']);
            if ($invoice && $invoice->total_amount != $validated['amount']) {
                return redirect()->back()->withErrors([
                    'amount' => 'O valor da transação deve ser igual ao valor total da fatura (' . 
                                number_format($invoice->total_amount, 2, ',', '.') . ').'
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $transaction = BankTransaction::create($validated);

            // Atualizar status da fatura se for um débito com fatura
            if ($validated['type'] === 'debit' && $validated['invoice_id']) {
                $invoice = Invoice::find($validated['invoice_id']);
                $invoice->status = 'paid';
                $invoice->payment_date = $validated['transaction_date'];
                $invoice->save();
            }
            
            // Forçar atualização do saldo da conta bancária
            $bankAccount = BankAccount::find($validated['bank_account_id']);
            $bankAccount->updateBalance();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Erro ao criar a transação: ' . $e->getMessage()]);
        }

        // Redirecionar para a página da conta bancária em vez da lista de transações
        return redirect()->route('bank-accounts.show', $validated['bank_account_id'])
            ->with('success', 'Transação bancária registrada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BankTransaction $bankTransaction): Response
    {
        $bankTransaction->load(['bankAccount', 'invoice.supplier']);
        
        return Inertia::render('BankTransactions/Show', [
            'transaction' => $bankTransaction
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankTransaction $bankTransaction): Response
    {
        $bankTransaction->load(['bankAccount', 'invoice.supplier']);
        $bankAccounts = BankAccount::where('active', true)->get(['id', 'name', 'bank_name', 'current_balance']);
        
        $pendingInvoices = Invoice::where('status', 'pending')
            ->with('supplier')
            ->get(['id', 'invoice_number', 'supplier_id', 'total_amount', 'due_date']);
            
        // Adicionar a fatura atual à lista, caso seja uma fatura paga
        if ($bankTransaction->invoice && $bankTransaction->invoice->status === 'paid') {
            $pendingInvoices->push($bankTransaction->invoice);
        }
        
        return Inertia::render('BankTransactions/Edit', [
            'transaction' => $bankTransaction,
            'bankAccounts' => $bankAccounts,
            'pendingInvoices' => $pendingInvoices
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankTransaction $bankTransaction): RedirectResponse
    {
        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:credit,debit',
            'notes' => 'nullable|string',
        ]);

        // Se mudar a fatura associada ou desassociar uma fatura, atualizar o status da fatura anterior
        $oldInvoiceId = $bankTransaction->invoice_id;
        $newInvoiceId = $validated['invoice_id'];
        
        DB::beginTransaction();
        try {
            // Atualizar a transação
            $bankTransaction->update($validated);
            
            // Se havia uma fatura associada antes e agora mudou
            if ($oldInvoiceId && $oldInvoiceId !== $newInvoiceId) {
                $oldInvoice = Invoice::find($oldInvoiceId);
                if ($oldInvoice) {
                    $oldInvoice->status = 'pending';
                    $oldInvoice->payment_date = null;
                    $oldInvoice->save();
                }
            }
            
            // Se há uma nova fatura associada
            if ($newInvoiceId && $bankTransaction->type === 'debit') {
                $newInvoice = Invoice::find($newInvoiceId);
                if ($newInvoice) {
                    $newInvoice->status = 'paid';
                    $newInvoice->payment_date = $validated['transaction_date'];
                    $newInvoice->save();
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Erro ao atualizar a transação: ' . $e->getMessage()]);
        }

        return redirect()->route('bank-transactions.show', $bankTransaction)
            ->with('success', 'Transação bancária atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankTransaction $bankTransaction): RedirectResponse
    {
        DB::beginTransaction();
        try {
            // Se a transação estava associada a uma fatura, atualizar o status da fatura
            if ($bankTransaction->invoice_id && $bankTransaction->type === 'debit') {
                $invoice = Invoice::find($bankTransaction->invoice_id);
                if ($invoice) {
                    $invoice->status = 'pending';
                    $invoice->payment_date = null;
                    $invoice->save();
                }
            }
            
            $bankTransaction->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Erro ao excluir a transação: ' . $e->getMessage()]);
        }

        return redirect()->route('bank-transactions.index')
            ->with('success', 'Transação bancária excluída com sucesso.');
    }
}
