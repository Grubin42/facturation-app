<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class QuoteController extends Controller
{
    /**
     * Affiche la liste des devis.
     */
    public function index()
    {
        $user = Auth::user();
        $quotes = Quote::where('user_id', $user->id)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();

        $currency = Setting::where('user_id', $user->id)->value('currency') ?? 'EUR';

        return Inertia::render('Quotes/Index', [
            'quotes' => $quotes,
            'currency' => $currency,
        ]);
    }

    /**
     * Affiche le formulaire de création de devis.
     */
    public function create()
    {
        $user = Auth::user();
        $clients = Client::where('user_id', $user->id)->get();
        $settings = Setting::where('user_id', $user->id)->first();
        $currency = $settings->currency ?? 'EUR';
        $default_tax_rate = $settings->default_tax_rate ?? 20;

        // Générer un numéro de devis par défaut en utilisant le préfixe configuré dans les paramètres
        $lastQuote = Quote::where('user_id', $user->id)->orderBy('id', 'desc')->first();

        // Utilisation du préfixe configuré ou 'DEVIS-' par défaut
        $prefix = $settings->quote_prefix ?? 'DEVIS-';
        $nextNumber = $settings->quote_next_number ?? 1;

        // Si un devis existe déjà, incrémenter le numéro
        if ($lastQuote) {
            // Extraire le numéro du dernier devis
            $prefixLength = strlen($prefix);
            if (strpos($lastQuote->quote_number, $prefix) === 0) {
                $lastNumber = intval(substr($lastQuote->quote_number, $prefixLength));
                $nextNumber = $lastNumber + 1;
            }
        }

        $quoteNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return Inertia::render('Quotes/Create', [
            'clients' => $clients,
            'defaultQuoteNumber' => $quoteNumber,
            'currency' => $currency,
            'client_id' => request('client_id'),
            'default_tax_rate' => $default_tax_rate,
        ]);
    }

    /**
     * Enregistre un nouveau devis.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'quote_number' => 'required|string|max:255',
            'quote_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:quote_date',
            'status' => 'required|in:draft,sent,accepted,rejected,expired,converted',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Calculs des totaux
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($request->items as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $itemTax = $itemSubtotal * ($item['tax_rate'] / 100);

            $subtotal += $itemSubtotal;
            $taxAmount += $itemTax;
        }

        $total = $subtotal + $taxAmount;

        // Création du devis
        $quote = Quote::create([
            'user_id' => $user->id,
            'client_id' => $request->client_id,
            'quote_number' => $request->quote_number,
            'quote_date' => $request->quote_date,
            'expiry_date' => $request->expiry_date,
            'status' => $request->status,
            'notes' => $request->notes,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);

        // Création des éléments du devis
        foreach ($request->items as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $itemTax = $itemSubtotal * ($item['tax_rate'] / 100);
            $itemTotal = $itemSubtotal + $itemTax;

            QuoteItem::create([
                'quote_id' => $quote->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'],
                'subtotal' => $itemSubtotal,
                'tax_amount' => $itemTax,
                'total' => $itemTotal,
            ]);
        }

        // Mettre à jour le compteur de devis dans les paramètres
        $settings = Setting::where('user_id', $user->id)->first();

        if ($settings) {
            $prefix = $settings->quote_prefix ?? 'DEVIS-';

            // Extraire le numéro du devis à partir du numéro de devis créé
            $prefixLength = strlen($prefix);
            if (strpos($quote->quote_number, $prefix) === 0) {
                $quoteNumber = intval(substr($quote->quote_number, $prefixLength));

                // Mettre à jour le prochain numéro de devis si le numéro actuel est plus grand
                if ($quoteNumber >= ($settings->quote_next_number ?? 1)) {
                    $settings->quote_next_number = $quoteNumber + 1;
                    $settings->save();
                }
            }
        }

        return redirect()->route('quotes.show', $quote->id)->with('success', 'Devis créé avec succès.');
    }

    /**
     * Affiche un devis spécifique.
     */
    public function show(Quote $quote)
    {
        // Vérifier que l'utilisateur est bien propriétaire du devis
        if ($quote->user_id !== Auth::id()) {
            abort(403);
        }

        $quote->load(['client', 'items']);
        $currency = Setting::where('user_id', Auth::id())->value('currency') ?? 'EUR';

        return Inertia::render('Quotes/Show', [
            'quote' => $quote,
            'currency' => $currency,
        ]);
    }

    /**
     * Affiche le formulaire de modification d'un devis.
     */
    public function edit(Quote $quote)
    {
        // Vérifier que l'utilisateur est bien propriétaire du devis
        if ($quote->user_id !== Auth::id()) {
            abort(403);
        }

        $quote->load(['client', 'items']);
        $clients = Client::where('user_id', Auth::id())->get();
        $settings = Setting::where('user_id', Auth::id())->first();
        $currency = $settings->currency ?? 'EUR';
        $default_tax_rate = $settings->default_tax_rate ?? 20;

        return Inertia::render('Quotes/Edit', [
            'quote' => $quote,
            'clients' => $clients,
            'currency' => $currency,
            'default_tax_rate' => $default_tax_rate,
        ]);
    }

    /**
     * Met à jour un devis existant.
     */
    public function update(Request $request, Quote $quote)
    {
        // Vérifier que l'utilisateur est bien propriétaire du devis
        if ($quote->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'quote_number' => 'required|string|max:255',
            'quote_date' => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:quote_date',
            'status' => 'required|in:draft,sent,accepted,rejected,expired,converted',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Calculs des totaux
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($request->items as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $itemTax = $itemSubtotal * ($item['tax_rate'] / 100);

            $subtotal += $itemSubtotal;
            $taxAmount += $itemTax;
        }

        $total = $subtotal + $taxAmount;

        // Mise à jour du devis
        $quote->update([
            'client_id' => $request->client_id,
            'quote_number' => $request->quote_number,
            'quote_date' => $request->quote_date,
            'expiry_date' => $request->expiry_date,
            'status' => $request->status,
            'notes' => $request->notes,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);

        // Supprimer les anciens éléments
        $quote->items()->delete();

        // Créer les nouveaux éléments
        foreach ($request->items as $item) {
            $itemSubtotal = $item['quantity'] * $item['unit_price'];
            $itemTax = $itemSubtotal * ($item['tax_rate'] / 100);
            $itemTotal = $itemSubtotal + $itemTax;

            QuoteItem::create([
                'quote_id' => $quote->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'],
                'subtotal' => $itemSubtotal,
                'tax_amount' => $itemTax,
                'total' => $itemTotal,
            ]);
        }

        return redirect()->route('quotes.show', $quote->id)->with('success', 'Devis mis à jour avec succès.');
    }

    /**
     * Supprime un devis.
     */
    public function destroy(Quote $quote)
    {
        // Vérifier que l'utilisateur est bien propriétaire du devis
        if ($quote->user_id !== Auth::id()) {
            abort(403);
        }

        // Supprimer les éléments du devis
        $quote->items()->delete();

        // Supprimer le devis
        $quote->delete();

        return redirect()->route('quotes.index')->with('success', 'Devis supprimé avec succès.');
    }

    /**
     * Marquer un devis comme accepté.
     */
    public function markAsAccepted(Quote $quote)
    {
        // Vérifier que l'utilisateur est bien propriétaire du devis
        if ($quote->user_id !== Auth::id()) {
            abort(403);
        }

        $quote->update(['status' => 'accepted']);

        return redirect()->back()->with('success', 'Devis marqué comme accepté.');
    }

    /**
     * Marquer un devis comme refusé.
     */
    public function markAsRejected(Quote $quote)
    {
        // Vérifier que l'utilisateur est bien propriétaire du devis
        if ($quote->user_id !== Auth::id()) {
            abort(403);
        }

        $quote->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Devis marqué comme refusé.');
    }

    /**
     * Générer une facture à partir d'un devis.
     */
    public function convertToInvoice(Quote $quote)
    {
        // Vérifier que l'utilisateur est bien propriétaire du devis
        if ($quote->user_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier que le devis n'est pas déjà converti
        if ($quote->status === 'converted') {
            return redirect()->back()->with('error', 'Ce devis a déjà été converti en facture.');
        }

        $user = Auth::user();

        // Récupérer les paramètres de l'utilisateur
        $settings = Setting::where('user_id', $user->id)->first();

        // Utiliser le préfixe configuré ou 'FACTURE-' par défaut si pas de paramètres
        $prefix = $settings->invoice_prefix ?? 'FACTURE-';
        $nextNumber = $settings->invoice_next_number ?? 1;

        // Générer un numéro de facture
        $invoiceNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Créer la facture
        $invoice = \App\Models\Invoice::create([
            'user_id' => $user->id,
            'client_id' => $quote->client_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => Carbon::today()->format('Y-m-d'),
            'due_date' => Carbon::today()->addDays(30)->format('Y-m-d'),
            'status' => 'draft',
            'notes' => $quote->notes,
            'subtotal' => $quote->subtotal,
            'tax_amount' => $quote->tax_amount,
            'total' => $quote->total,
        ]);

        // Créer les éléments de facture
        foreach ($quote->items as $item) {
            \App\Models\InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate,
                'subtotal' => $item->subtotal,
                'tax_amount' => $item->tax_amount,
                'total' => $item->total,
            ]);
        }

        // Mettre à jour le statut du devis
        $quote->update([
            'status' => 'converted',
            'invoice_id' => $invoice->id,
        ]);

        // Mettre à jour le compteur de numéro de facture
        if ($settings) {
            $settings->invoice_next_number = $nextNumber + 1;
            $settings->save();
        }

        return redirect()->route('invoices.show', $invoice->id)->with('success', 'Devis converti en facture avec succès.');
    }

    /**
     * Génère un PDF à partir d'un devis.
     */
    public function generatePdf(Quote $quote, Request $request)
    {
        // Vérifier que l'utilisateur est bien propriétaire du devis
        if ($quote->user_id !== Auth::id()) {
            abort(403);
        }

        $quote->load(['client', 'items']);
        $settings = Setting::where('user_id', Auth::id())->first();

        if (!$settings) {
            $settings = new Setting();
            $settings->currency = 'EUR';
        }

        // Si c'est juste pour l'aperçu, retourner la vue Inertia
        if (!$request->has('download') && !$request->has('print')) {
            return Inertia::render('Quotes/Pdf', [
                'quote' => $quote,
                'settings' => $settings,
            ]);
        }

        // Sinon générer le PDF pour téléchargement ou impression
        $pdf = PDF::loadView('pdf.quote', [
            'quote' => $quote,
            'settings' => $settings,
        ]);

        // Configuration du PDF
        $pdf->setPaper('a4');
        $pdf->setOption('margin-top', 15);
        $pdf->setOption('margin-right', 15);
        $pdf->setOption('margin-bottom', 15);
        $pdf->setOption('margin-left', 15);

        // Pour téléchargement direct
        if ($request->has('download')) {
            return $pdf->download("devis_{$quote->quote_number}.pdf");
        }

        // Pour impression ou visualisation dans le navigateur
        return $pdf->stream("devis_{$quote->quote_number}.pdf");
    }
}
