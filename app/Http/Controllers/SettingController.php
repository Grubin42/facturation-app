<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        // Ajouter l'URL du logo s'il existe
        if ($settings->logo_path) {
            $settings->logo_url = Storage::url($settings->logo_path);
        }

        return Inertia::render('settings/Edit', [
            'settings' => $settings
        ]);
    }

    /**
     * Mettre à jour les paramètres
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
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
            'company_iban' => 'nullable|string|max:50',
            'invoice_prefix' => 'required|string|max:10',
            'invoice_next_number' => 'required|integer|min:1',
            'quote_prefix' => 'required|string|max:10',
            'quote_next_number' => 'required|integer|min:1',
            'payment_terms' => 'required|integer|min:0',
            'default_tax_rate' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'invoice_footer' => 'nullable|string',
            'quote_footer' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Récupérer les paramètres existants
        $settings = Setting::where('user_id', Auth::id())->first();

        if (!$settings) {
            $settings = new Setting();
            $settings->user_id = Auth::id();
        }

        // Gérer l'upload du logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo s'il existe
            if ($settings->logo_path) {
                Storage::delete($settings->logo_path);
            }

            // Stocker le nouveau logo
            $path = $request->file('logo')->store('logos', 'public');
            $settings->logo_path = $path;
        }

        // Mettre à jour les autres champs
        foreach ($validated as $key => $value) {
            if ($key !== 'logo') {
                $settings->{$key} = $value;
            }
        }

        $settings->save();

        return redirect()->route('settings.edit')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }
}
