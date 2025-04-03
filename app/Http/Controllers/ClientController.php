<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClientController extends Controller
{
    use AuthorizesRequests;

    /**
     * Afficher la liste des clients
     */
    public function index()
    {
        $clients = Client::where('user_id', Auth::id())->orderBy('name')->get();

        return Inertia::render('Clients/Index', [
            'clients' => $clients
        ]);
    }

    /**
     * Afficher le formulaire de création d'un client
     */
    public function create()
    {
        return Inertia::render('Clients/Create');
    }

    /**
     * Enregistrer un nouveau client
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'siret' => 'nullable|string|max:30',
            'notes' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $client = Client::create($data);

        return redirect()->route('clients.index')
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Afficher un client
     */
    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load(['invoices', 'quotes']);

        $settings = Setting::where('user_id', Auth::id())->first();
        $currency = $settings ? $settings->currency : 'EUR';

        return Inertia::render('Clients/Show', [
            'client' => $client,
            'currency' => $currency
        ]);
    }

    /**
     * Afficher le formulaire d'édition d'un client
     */
    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        return Inertia::render('Clients/Edit', [
            'client' => $client
        ]);
    }

    /**
     * Mettre à jour un client
     */
    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'siret' => 'nullable|string|max:30',
            'notes' => 'nullable|string',
        ]);

        $client->update($request->all());

        return redirect()->route('clients.index')
            ->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Supprimer un client
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client supprimé avec succès.');
    }
}
