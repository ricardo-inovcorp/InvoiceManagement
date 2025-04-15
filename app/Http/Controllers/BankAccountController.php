<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $active = $request->input('active');
        
        $query = BankAccount::query();
        
        // Filtrar por termo de pesquisa
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%")
                  ->orWhere('iban', 'like', "%{$search}%");
            });
        }
        
        // Filtrar por status (ativo/inativo)
        if ($active !== null && $active !== '') {
            $query->where('active', $active);
        }
        
        $bankAccounts = $query->latest()->get();
        
        return Inertia::render('BankAccounts/Index', [
            'bankAccounts' => $bankAccounts,
            'filters' => [
                'search' => $search,
                'active' => $active
            ],
            'debug' => [
                'count' => $bankAccounts->count(),
                'route' => 'bank-accounts.index',
                'method' => 'index',
                'controller' => 'BankAccountController',
                'component' => 'BankAccounts/Index',
                'time' => now()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('BankAccounts/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'iban' => 'required|string|max:50|unique:bank_accounts',
            'initial_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Definir saldo atual igual ao saldo inicial
        $validated['current_balance'] = $validated['initial_balance'];
        
        DB::beginTransaction();
        try {
            BankAccount::create($validated);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Erro ao criar a conta bancária: ' . $e->getMessage()]);
        }

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BankAccount $bankAccount, Request $request): Response
    {
        // Definir número de registros por página
        $perPage = $request->input('per_page', 10);
        
        // Carregar as transações associadas à conta com paginação
        $transactions = $bankAccount->transactions()
            ->with('invoice')
            ->latest('transaction_date')
            ->paginate($perPage);
        
        // Carregar faturas pendentes para o formulário de nova transação
        $pendingInvoices = \App\Models\Invoice::where('status', 'pending')
            ->with('supplier')
            ->get(['id', 'invoice_number', 'supplier_id', 'total_amount', 'due_date']);
        
        return Inertia::render('BankAccounts/Show', [
            'bankAccount' => $bankAccount,
            'transactions' => $transactions,
            'pendingInvoices' => $pendingInvoices
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount): Response
    {
        return Inertia::render('BankAccounts/Edit', [
            'bankAccount' => $bankAccount
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        // Debug para verificar o valor no request
        logger()->info('Request completo:', $request->all());
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'iban' => 'required|string|max:50|unique:bank_accounts,iban,' . $bankAccount->id,
            'initial_balance' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Debug para verificar o valor de is_active
        logger()->info('Valor recebido para is_active: ' . var_export($validated['is_active'] ?? 'não definido', true));

        // Garantir que is_active tenha um valor padrão se não for enviado
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = false;
        }

        // Forçar o valor para boolean
        $validated['is_active'] = $validated['is_active'] === true || $validated['is_active'] === "true" || $validated['is_active'] === 1 || $validated['is_active'] === "1";
        
        // Mapear is_active para active, que é o nome do campo no banco de dados
        $validated['active'] = $validated['is_active'];
        unset($validated['is_active']);

        // Debug para verificar o valor de active após conversão
        logger()->info('Valor final para active: ' . var_export($validated['active'], true));

        DB::beginTransaction();
        try {
            // Se houver mudança no saldo inicial, recalcular o saldo atual
            if ($bankAccount->initial_balance != $validated['initial_balance']) {
                $bankAccount->initial_balance = $validated['initial_balance'];
                $bankAccount->updateBalance();
            }

            $bankAccount->update($validated);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Erro ao atualizar a conta bancária: ' . $e->getMessage()]);
        }

        return redirect()->route('bank-accounts.show', $bankAccount)
            ->with('success', 'Conta bancária atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        // Verificar se há transações associadas
        if ($bankAccount->transactions()->count() > 0) {
            return redirect()->back()->withErrors(['error' => 'Não é possível excluir uma conta bancária com transações associadas.']);
        }

        DB::beginTransaction();
        try {
            $bankAccount->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Erro ao excluir a conta bancária: ' . $e->getMessage()]);
        }

        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária excluída com sucesso.');
    }
}
