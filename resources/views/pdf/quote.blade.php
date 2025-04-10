<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis {{ $quote->quote_number }}</title>
    <style>
        @page {
            margin: 15mm 15mm 10mm 15mm;
            size: A4;
        }
        @page {
            @top-right {
                content: "DEVIS {{ $quote->quote_number }}";
                font-size: 10pt;
            }
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12pt;
            line-height: 1.3;
            color: #333;
        }
        .container {
            max-width: 100%;
            padding: 20px;
            padding-bottom: 60px;
        }

        /* Structure de tableau pour l'en-tête */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .header-table td {
            vertical-align: top;
            padding: 5px;
            border: none;
        }

        .company-cell {
            width: auto;
            text-align: left;
        }

        .client-cell {
            text-align: right;
            padding-right: 0;
        }

        .logo {
            max-width: 150px;
            max-height: 150px;
            margin-bottom: 5px;
        }

        /* Autres styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
        }
        th.right, td.right {
            text-align: right;
        }
        thead {
            display: table-header-group;
        }
        tfoot {
            display: table-footer-group;
        }
        tr {
            page-break-inside: avoid;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .quote-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .footer-section {
            position: fixed;
            bottom: 0;
            left: 15mm;
            right: 15mm;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            padding-bottom: 0;
            margin-top: 0;
            margin-bottom: 0;
            text-align: center;
            font-size: 9pt;
            background: white;
            z-index: 1000;
        }
        .footer-section p, .footer-section div {
            margin: 3px 0;
            line-height: 1.2;
        }
        .uppercase {
            text-transform: uppercase;
        }
        .contact-info {
            color: #777;
            margin-top: 5px;
            margin-bottom: 5px;
        }
        .totals-section {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Espacement des sections */
        .content-section {
            margin-bottom: 20px;
        }

        h3 {
            margin-top: 15px;
            margin-bottom: 10px;
            font-size: 14pt;
        }

        p {
            margin: 5px 0;
        }

        /* Amélioration des tableaux */
        .items-table {
            margin-bottom: 30px;
            width: 100%;
        }

        .items-table th {
            background-color: #f2f2f2;
            padding: 8px;
        }

        .items-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        /* Numéro de devis sur les pages suivantes uniquement */
        .page-header {
            display: none; /* Caché sur la première page */
        }

        /* Configuration des règles @page */
        @page:not(:first) {
            @top-left {
                content: "DEVIS {{ $quote->quote_number }}";
                font-size: 10pt;
            }
        }

        /* Dates alignées à gauche */
        .dates-section {
            margin-bottom: 20px;
        }

        .date-item {
            display: inline-block;
            margin-right: 30px;
        }

        /* Empêcher les coupures dans les sections importantes */
        .no-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Styles spécifiques pour la section des totaux */
        .totals-wrapper {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            display: table;
            width: 100%;
        }

        .totals-section {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .client-info-container {
            display: inline-block;
            text-align: left;
            max-width: 250px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête avec structure de tableau -->
        <table class="header-table">
            <tr>
                <td class="company-cell">
                    @if($settings->logo_path)
                        <img src="{{ public_path('storage/' . $settings->logo_path) }}" alt="Logo de l'entreprise" class="logo">
                    @endif
                </td>
                <td class="client-cell">
                    <div class="client-info-container">
                        <div style="font-weight: bold;">{{ $quote->client->name }}</div>
                        <div>{{ $quote->client->address }}</div>
                        <div>{{ $quote->client->postal_code }} {{ $quote->client->city }}</div>
                        @if($quote->client->country)
                        <div>{{ $quote->client->country }}</div>
                        @endif
                        @if($quote->client->phone)
                        <div>Tél: {{ $quote->client->phone }}</div>
                        @endif
                        @if($quote->client->email)
                        <div>Email: {{ $quote->client->email }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- Titre du devis -->
        <div style="margin-bottom: 15px;">
            <div class="quote-title">{{ $quote->quote_number }}</div>
        </div>

        <!-- Informations de dates -->
        <div class="dates-section">
            <div class="date-item">
                <span style="font-weight: bold;">Date du devis:</span>
                {{ \Carbon\Carbon::parse($quote->quote_date)->format('d/m/Y') }}
            </div>
            <div class="date-item">
                <span style="font-weight: bold;">Date d'expiration:</span>
                {{ \Carbon\Carbon::parse($quote->expiry_date)->format('d/m/Y') }}
            </div>
        </div>

        <!-- Lignes de devis -->
        <div class="content-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="right">Quantité</th>
                        <th class="right">Prix unitaire</th>
                        <th class="right">TVA (%)</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quote->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="right">{{ $item->quantity }}</td>
                        <td class="right">{{ number_format($item->unit_price, 2, ',', ' ') }} {{ $settings->currency }}</td>
                        <td class="right">{{ $item->tax_rate }} %</td>
                        <td class="right">{{ number_format($item->total, 2, ',', ' ') }} {{ $settings->currency }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Section des totaux séparée pour éviter les coupures -->
            <table class="items-table totals-wrapper">
                <tbody class="totals-section">
                    <tr>
                        <td colspan="3" class="text-right"></td>
                        <td class="right font-bold" style="width: 15%;">Sous-total:</td>
                        <td class="right" style="width: 15%;">{{ number_format($quote->subtotal, 2, ',', ' ') }} {{ $settings->currency }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-right"></td>
                        <td class="right font-bold">TVA:</td>
                        <td class="right">{{ number_format($quote->tax_amount, 2, ',', ' ') }} {{ $settings->currency }}</td>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <td colspan="3" class="text-right"></td>
                        <td class="right font-bold">Total:</td>
                        <td class="right font-bold">{{ number_format($quote->total, 2, ',', ' ') }} {{ $settings->currency }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Notes et conditions -->
        <div class="content-section">
            @if($quote->notes)
            <div style="margin-bottom: 20px;">
                <h3>Notes:</h3>
                <p style="white-space: pre-line;">{{ $quote->notes }}</p>
            </div>
            @endif

            <div class="no-break">
                <h3>Conditions:</h3>
                <p>Ce devis est valable jusqu'au {{ \Carbon\Carbon::parse($quote->expiry_date)->format('d/m/Y') }}.</p>
                <p>En cas d'acceptation, veuillez nous retourner ce devis signé.</p>
                @if($settings->quote_footer)
                <p style="white-space: pre-line; margin-top: 10px;">{{ $settings->quote_footer }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Pied de page fixe -->
    <div class="footer-section">
        <p class="text-center" style="margin-bottom: 8px;">Merci pour votre confiance !</p>

        <!-- Infos confidentielles en majuscules et plus gros -->
        <div class="text-center font-bold uppercase">
            @if($settings->company_siret)
                IDE: {{ $settings->company_siret }}
            @endif

            @if($settings->company_vat && $settings->company_siret)
                -
            @endif

            @if($settings->company_vat)
                TVA: {{ $settings->company_vat }}
            @endif

            @if($settings->company_iban && ($settings->company_siret || $settings->company_vat))
                -
            @endif

            @if($settings->company_iban)
                IBAN: {{ $settings->company_iban }}
            @endif
        </div>

        <!-- Infos de contact en gris clair sur une ligne -->
        <p class="text-center contact-info">
            @if($settings->company_address)
                {{ $settings->company_address }}
            @endif

            @if($settings->company_postal_code || $settings->company_city)
                @if($settings->company_address), @endif
                {{ $settings->company_postal_code }} {{ $settings->company_city }}
            @endif

            @if($settings->company_phone)
                @if($settings->company_address || $settings->company_postal_code || $settings->company_city) - @endif
                Tél: {{ $settings->company_phone }}
            @endif

            @if($settings->company_email)
                @if($settings->company_address || $settings->company_postal_code || $settings->company_city || $settings->company_phone) - @endif
                Email: {{ $settings->company_email }}
            @endif
        </p>
    </div>
</body>
</html>
