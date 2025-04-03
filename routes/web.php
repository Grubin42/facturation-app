<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\AdminMiddleware;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    $user = Auth::user();

    // Mettre à jour automatiquement le statut des factures en retard
    Invoice::where('user_id', $user->id)
        ->whereIn('status', ['draft', 'sent'])
        ->where('due_date', '<', Carbon::today())
        ->update(['status' => 'overdue']);

    // Récupérer les statistiques
    $totalInvoices = Invoice::where('user_id', $user->id)->count();
    $paidInvoices = Invoice::where('user_id', $user->id)->where('status', 'paid')->count();
    $overdueInvoices = Invoice::where('user_id', $user->id)
        ->where(function($query) {
            $query->where('status', 'overdue')
                ->orWhere(function($q) {
                    $q->whereIn('status', ['draft', 'sent'])
                        ->where('due_date', '<', Carbon::today());
                });
        })
        ->count();
    $totalRevenue = Invoice::where('user_id', $user->id)->where('status', 'paid')->sum('total');

    // Récupérer la devise de l'utilisateur
    $currency = \App\Models\Setting::where('user_id', $user->id)->value('currency') ?? 'EUR';

    // Récupérer les factures récentes
    $recentInvoices = Invoice::where('user_id', $user->id)
        ->with('client')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    // Récupérer les factures en retard
    $overdueInvoices = Invoice::where('user_id', $user->id)
        ->where('status', 'overdue')
        ->with('client')
        ->orderBy('due_date', 'asc')
        ->get()
        ->map(function ($invoice) {
            // Calculer le nombre de jours de retard
            $dueDate = Carbon::parse($invoice->due_date);
            $invoice->days_overdue = $dueDate->diffInDays(Carbon::today());
            return $invoice;
        });

    // Pour déboguer - Vérifier la requête SQL
    \Illuminate\Support\Facades\Log::info('Factures en retard requête SQL: ' . Invoice::where('user_id', $user->id)
        ->with('client')
        ->where(function($query) {
            $query->where('status', 'overdue')
                ->orWhere(function($q) {
                    $q->whereIn('status', ['draft', 'sent'])
                        ->where('due_date', '<', Carbon::today());
                });
        })
        ->toSql());

    // Pour déboguer - Vérifier les résultats
    \Illuminate\Support\Facades\Log::info('Nombre de factures en retard récupérées: ' . $overdueInvoices->count());
    \Illuminate\Support\Facades\Log::info('Contenu des factures en retard: ' . json_encode($overdueInvoices));

    return Inertia::render('Dashboard', [
        'stats' => [
            'total_invoices' => $totalInvoices,
            'paid_invoices' => $paidInvoices,
            'overdue_invoices' => $overdueInvoices->count(),
            'total_revenue' => $totalRevenue,
        ],
        'recentInvoices' => $recentInvoices,
        'overdueInvoices' => $overdueInvoices,
        'currency' => $currency,
    ]);
})->middleware(['auth', 'verified', EnsureUserIsActive::class])->name('dashboard');

// Routes protégées par l'authentification et vérification de l'activation
Route::middleware(['auth', 'verified', EnsureUserIsActive::class])->group(function () {
    // Gestion des clients
    Route::resource('clients', ClientController::class);

    // Gestion des factures
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-as-paid');
    Route::post('invoices/{invoice}/mark-as-cancelled', [InvoiceController::class, 'markAsCancelled'])->name('invoices.mark-as-cancelled');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');
});

// Routes d'administration
Route::prefix('admin')->name('admin.')->middleware(['auth', EnsureUserIsActive::class, AdminMiddleware::class])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [\App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users');
    Route::patch('/users/{user}/toggle-status', [\App\Http\Controllers\Admin\AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::patch('/users/{user}/change-role', [\App\Http\Controllers\Admin\AdminController::class, 'changeUserRole'])->name('users.change-role');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
