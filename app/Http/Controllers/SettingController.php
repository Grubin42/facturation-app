<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * Afficher le formulaire des paramètres
     */
    public function edit()
    {
        $settings = Setting::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'company_name' => 'Ma Société',
                'invoice_prefix' => 'INV',
                'invoice_next_number' => 1,
                'quote_prefix' => 'QUO',
                'quote_next_number' => 1,
                'payment_terms' => 30,
                'default_tax_rate' => 20,
                'currency' => 'EUR',
            ]
        );

        return Inertia::render('settings/Edit', [
            'settings' => $settings
        ]);
    }

    /**
     * Mettre à jour les paramètres
     */
    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:255',
            'company_city' => 'nullable|string|max:255',
            'company_postal_code' => 'nullable|string|max:20',
            'company_country' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'company_siret' => 'nullable|string|max:30',
            'company_vat' => 'nullable|string|max:30',
            'invoice_prefix' => 'required|string|max:10',
            'invoice_next_number' => 'required|integer|min:1',
            'quote_prefix' => 'required|string|max:10',
            'quote_next_number' => 'required|integer|min:1',
            'payment_terms' => 'required|integer|min:0',
            'default_tax_rate' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'invoice_footer' => 'nullable|string',
            'quote_footer' => 'nullable|string',
        ]);

        $settings = Setting::updateOrCreate(
            ['user_id' => Auth::id()],
            $request->all()
        );

        return redirect()->route('settings.edit')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }
}
