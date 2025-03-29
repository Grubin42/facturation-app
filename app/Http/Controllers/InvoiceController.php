<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Afficher la liste des factures
     */
    public function index(Request $request)
    {
        // Mettre à jour le statut des factures en retard
        Invoice::where('user_id', Auth::id())
            ->whereIn('status', ['draft', 'sent'])
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => 'overdue']);

        $query = Invoice::with('client')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('invoice_date', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $invoices = $query->get();

        $clients = Client::where('user_id', Auth::id())->orderBy('name')->get();

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'clients' => $clients,
            'filters' => $request->only(['client_id', 'status', 'start_date', 'end_date']),
            'statuses' => [
                'draft' => 'Brouillon',
                'sent' => 'Envoyée',
                'paid' => 'Payée',
                'overdue' => 'En retard',
                'cancelled' => 'Annulée'
            ]
        ]);
    }

    /**
     * Afficher le formulaire de création d'une facture
     */
    public function create(Request $request)
    {
        $clients = Client::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        $selectedClient = null;
        if ($request->filled('client_id')) {
            $selectedClient = Client::where('user_id', Auth::id())
                ->where('id', $request->client_id)
                ->first();
        }

        // Obtenir les paramètres de l'utilisateur
        $settings = Setting::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'company_name' => 'Ma Société',
                'invoice_prefix' => 'INV',
                'invoice_next_number' => 1,
                'default_tax_rate' => 20,
                'payment_terms' => 30
            ]
        );

        $invoiceNumber = $settings->invoice_prefix . '-' . str_pad($settings->invoice_next_number, 5, '0', STR_PAD_LEFT);
        $today = Carbon::today()->format('Y-m-d');
        $dueDate = Carbon::today()->addDays($settings->payment_terms)->format('Y-m-d');

        return Inertia::render('Invoices/Create', [
            'clients' => $clients,
            'selectedClient' => $selectedClient,
            'invoiceNumber' => $invoiceNumber,
            'defaultTaxRate' => $settings->default_tax_rate,
            'today' => $today,
            'dueDate' => $dueDate
        ]);
    }

    /**
     * Enregistrer une nouvelle facture
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Créer la facture
        $invoice = new Invoice();
        $invoice->user_id = Auth::id();
        $invoice->client_id = $request->client_id;
        $invoice->invoice_number = $request->invoice_number;
        $invoice->invoice_date = $request->invoice_date;
        $invoice->due_date = $request->due_date;
        $invoice->subtotal = $request->subtotal;
        $invoice->tax_rate = $request->tax_rate ?? 0;
        $invoice->tax_amount = $request->tax_amount;
        $invoice->total = $request->total;
        $invoice->status = $request->status;
        $invoice->notes = $request->notes;

        if ($request->status === 'paid') {
            $invoice->paid_at = Carbon::now();
            $invoice->payment_method = $request->payment_method ?? null;
        }

        $invoice->save();

        // Ajouter les éléments de la facture
        foreach ($request->items as $index => $item) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->description = $item['description'];
            $invoiceItem->quantity = $item['quantity'];
            $invoiceItem->unit_price = $item['unit_price'];
            $invoiceItem->tax_rate = $item['tax_rate'];
            $invoiceItem->tax_amount = $item['tax_amount'];
            $invoiceItem->subtotal = $item['subtotal'];
            $invoiceItem->total = $item['total'];
            $invoiceItem->order = $index;
            $invoiceItem->save();
        }

        // Mettre à jour le numéro de facture dans les paramètres
        $setting = Setting::where('user_id', Auth::id())->first();
        if ($setting) {
            $setting->invoice_next_number += 1;
            $setting->save();
        }

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Facture créée avec succès.');
    }

    /**
     * Afficher une facture
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'items']);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice
        ]);
    }

    /**
     * Afficher le formulaire d'édition d'une facture
     */
    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->load(['client', 'items']);

        $clients = Client::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice,
            'clients' => $clients
        ]);
    }

    /**
     * Mettre à jour une facture
     */
    public function update(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number,' . $invoice->id,
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,paid,overdue,cancelled',
            'notes' => 'nullable|string',
        ]);

        // Mettre à jour la facture
        $invoice->client_id = $request->client_id;
        $invoice->invoice_number = $request->invoice_number;
        $invoice->invoice_date = $request->invoice_date;
        $invoice->due_date = $request->due_date;
        $invoice->subtotal = $request->subtotal;
        $invoice->tax_rate = $request->tax_rate ?? 0;
        $invoice->tax_amount = $request->tax_amount;
        $invoice->total = $request->total;
        $invoice->status = $request->status;
        $invoice->notes = $request->notes;

        if ($request->status === 'paid' && !$invoice->paid_at) {
            $invoice->paid_at = Carbon::now();
            $invoice->payment_method = $request->payment_method ?? null;
        } else if ($request->status !== 'paid') {
            $invoice->paid_at = null;
            $invoice->payment_method = null;
        }

        $invoice->save();

        // Supprimer les éléments existants
        $invoice->items()->delete();

        // Ajouter les nouveaux éléments
        foreach ($request->items as $index => $item) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->description = $item['description'];
            $invoiceItem->quantity = $item['quantity'];
            $invoiceItem->unit_price = $item['unit_price'];
            $invoiceItem->tax_rate = $item['tax_rate'];
            $invoiceItem->tax_amount = $item['tax_amount'];
            $invoiceItem->subtotal = $item['subtotal'];
            $invoiceItem->total = $item['total'];
            $invoiceItem->order = $index;
            $invoiceItem->save();
        }

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Facture mise à jour avec succès.');
    }

    /**
     * Supprimer une facture
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);

        // Supprimer les éléments associés
        $invoice->items()->delete();

        // Supprimer la facture
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Facture supprimée avec succès.');
    }

    /**
     * Marquer une facture comme payée
     */
    public function markAsPaid(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'payment_method' => 'nullable|string|max:255',
        ]);

        $invoice->status = 'paid';
        $invoice->paid_at = Carbon::now();
        $invoice->payment_method = $request->payment_method;
        $invoice->save();

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Facture marquée comme payée avec succès.');
    }

    /**
     * Générer un PDF de la facture
     */
    public function generatePdf(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'items']);

        // Récupérer les paramètres de l'entreprise
        $settings = Setting::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'company_name' => 'Ma Société',
                'invoice_prefix' => 'INV',
                'invoice_next_number' => 1,
                'default_tax_rate' => 20,
                'payment_terms' => 30,
                'currency' => 'CHF'
            ]
        );

        // On pourrait utiliser une bibliothèque comme Dompdf ici
        // Pour l'instant, nous allons juste renvoyer la vue pour afficher la facture
        return Inertia::render('Invoices/Pdf', [
            'invoice' => $invoice,
            'settings' => $settings
        ]);
    }

    /**
     * Marquer une facture comme annulée
     */
    public function markAsCancelled(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'cancellation_reason' => 'nullable|string|max:255',
        ]);

        $invoice->status = 'cancelled';
        $invoice->cancellation_reason = $request->cancellation_reason;
        $invoice->cancelled_at = Carbon::now();
        $invoice->save();

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Facture annulée avec succès.');
    }
}
