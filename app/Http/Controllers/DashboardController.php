<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics.
     */
    public function index(): Response
    {
        // Obter dados para os KPIs
        $pendingInvoices = Invoice::where('status', 'pending')->count();
        $pendingTotal = Invoice::where('status', 'pending')->sum('total_amount');
        
        $overdueInvoices = Invoice::where('status', 'overdue')->count();
        $overdueTotal = Invoice::where('status', 'overdue')->sum('total_amount');
        
        // Próximos vencimentos (7 dias)
        $nextWeekInvoices = Invoice::where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->count();
        $nextWeekTotal = Invoice::where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->sum('total_amount');
        
        // Total de faturas pagas
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $paidTotal = Invoice::where('status', 'paid')->sum('total_amount');
        
        // Dados para gráfico de status
        $invoicesByStatus = [
            ['status' => 'Pago', 'count' => Invoice::where('status', 'paid')->count()],
            ['status' => 'Pendente', 'count' => $pendingInvoices],
            ['status' => 'Atrasado', 'count' => $overdueInvoices],
            ['status' => 'Cancelado', 'count' => Invoice::where('status', 'cancelled')->count()],
        ];
        
        // Dados para mapa de calor
        $dueCalendar = [];
        $startDate = now()->startOfMonth();
        $endDate = now()->addMonths(1)->endOfMonth();
        
        $dueDates = Invoice::where('status', 'pending')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->selectRaw('DATE(due_date) as date, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('date')
            ->get()
            ->keyBy('date');
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $invoicesForDate = $dueDates->get($dateString);
            
            $dueCalendar[] = [
                'date' => $dateString,
                'count' => $invoicesForDate ? $invoicesForDate->count : 0,
                'total' => $invoicesForDate ? $invoicesForDate->total : 0,
            ];
        }
        
        // Top fornecedores
        $topSuppliers = Supplier::withSum('invoices as total_amount', 'total_amount')
            ->withCount('invoices')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get()
            ->map(function ($supplier) {
                return [
                    'name' => $supplier->company_name,
                    'count' => $supplier->invoices_count,
                    'total' => $supplier->total_amount,
                ];
            });
        
        // Dados para gráfico de área (últimos 6 meses)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyData[] = [
                'month' => $month->format('M'),
                'paid' => Invoice::where('status', 'paid')
                    ->whereYear('payment_date', $month->year)
                    ->whereMonth('payment_date', $month->month)
                    ->sum('total_amount'),
                'pending' => Invoice::where('status', 'pending')
                    ->whereYear('due_date', $month->year)
                    ->whereMonth('due_date', $month->month)
                    ->sum('total_amount'),
                'overdue' => Invoice::where('status', 'overdue')
                    ->whereYear('due_date', $month->year)
                    ->whereMonth('due_date', $month->month)
                    ->sum('total_amount'),
            ];
        }
        
        // Alertas para hoje
        $alertsToday = Invoice::where('status', 'pending')
            ->whereDate('due_date', today())
            ->with('supplier')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'supplier' => $invoice->supplier->company_name,
                    'total' => $invoice->total_amount,
                ];
            });
        
        return Inertia::render('Dashboard', [
            'stats' => [
                'pending' => [
                    'count' => $pendingInvoices,
                    'total' => $pendingTotal
                ],
                'overdue' => [
                    'count' => $overdueInvoices,
                    'total' => $overdueTotal
                ],
                'nextWeek' => [
                    'count' => $nextWeekInvoices,
                    'total' => $nextWeekTotal
                ],
                'paid' => [
                    'count' => $paidInvoices,
                    'total' => $paidTotal
                ],
            ],
            'invoicesByStatus' => $invoicesByStatus,
            'dueCalendar' => $dueCalendar,
            'topSuppliers' => $topSuppliers,
            'monthlyData' => $monthlyData,
            'alertsToday' => $alertsToday,
        ]);
    }
} 