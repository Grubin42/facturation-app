<template>
  <div class="container mx-auto py-6 max-w-4xl bg-white pdf-container">

    <!-- En-tête -->
    <div class="flex justify-between items-start mb-10 px-8 header-section">
      <div>
        <h1 class="text-3xl font-bold mb-1">FACTURE</h1>
        <p class="text-lg font-semibold">{{ invoice.invoice_number }}</p>
      </div>
      <div class="text-right flex flex-col items-end">
        <div v-if="settings.logo_path" class="mb-2 max-w-[120px] max-h-[80px] flex justify-center">
          <img :src="logoUrl" alt="Logo de l'entreprise" class="object-contain" />
        </div>
        <h2 class="text-lg font-bold mt-1">{{ settings.company_name }}</h2>
      </div>
    </div>

    <!-- Informations de facturation -->
    <div class="flex justify-between mb-10 px-8 client-info-section">
      <div class="w-1/2 client-info" style="line-height: 1;">
        <h3 class="text-lg font-semibold mb-1">Facturé à:</h3>
        <div style="line-height: 1;">
          <p class="font-semibold mb-0" style="margin: 0; padding: 1px 0;">{{ invoice.client.name }}</p>
          <p class="mb-0" style="margin: 0; padding: 1px 0;">{{ invoice.client.address }}</p>
          <p class="mb-0" style="margin: 0; padding: 1px 0;">{{ invoice.client.postal_code }} {{ invoice.client.city }}</p>
          <p class="mb-0" style="margin: 0; padding: 1px 0;">{{ invoice.client.country }}</p>
          <p class="mb-0" style="margin: 0; padding: 1px 0;">Tél: {{ invoice.client.phone }}</p>
          <p class="mb-0" style="margin: 0; padding: 1px 0;">Email: {{ invoice.client.email }}</p>
        </div>
      </div>
      <div class="w-1/2 text-right">
        <div style="margin-bottom: 5px;">
          <span style="font-weight: bold;">Date de facture:</span>
          {{ formatDate(invoice.invoice_date) }}
        </div>
        <div>
          <span style="font-weight: bold;">Date d'échéance:</span>
          {{ formatDate(invoice.due_date) }}
        </div>
      </div>
    </div>

    <!-- Lignes de facture -->
    <div class="px-8 mb-10">
      <table class="min-w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="border px-4 py-2 text-left">Description</th>
            <th class="border px-4 py-2 text-right w-20">Quantité</th>
            <th class="border px-4 py-2 text-right w-32">Prix unitaire</th>
            <th class="border px-4 py-2 text-right w-24">TVA (%)</th>
            <th class="border px-4 py-2 text-right w-32">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in invoice.items" :key="item.id" class="border-b">
            <td class="border-x px-4 py-2">{{ item.description }}</td>
            <td class="border-x px-4 py-2 text-right">{{ item.quantity }}</td>
            <td class="border-x px-4 py-2 text-right">{{ formatAmount(item.unit_price) }}</td>
            <td class="border-x px-4 py-2 text-right">{{ item.tax_rate }} %</td>
            <td class="border-x px-4 py-2 text-right">{{ formatAmount(item.total) }}</td>
          </tr>
        </tbody>
        <tfoot class="totals-section">
          <tr>
            <td colspan="3" class="border px-4 py-2"></td>
            <td class="border px-4 py-2 text-right font-semibold">Sous-total:</td>
            <td class="border px-4 py-2 text-right">{{ formatAmount(invoice.subtotal) }}</td>
          </tr>
          <tr>
            <td colspan="3" class="border px-4 py-2"></td>
            <td class="border px-4 py-2 text-right font-semibold">TVA:</td>
            <td class="border px-4 py-2 text-right">{{ formatAmount(invoice.tax_amount) }}</td>
          </tr>
          <tr class="bg-gray-100">
            <td colspan="3" class="border px-4 py-2"></td>
            <td class="border px-4 py-2 text-right font-bold">Total:</td>
            <td class="border px-4 py-2 text-right font-bold">{{ formatAmount(invoice.total) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Notes et conditions -->
    <div class="px-8 mb-10">
      <div v-if="invoice.notes" class="mb-6">
        <h3 class="text-lg font-semibold mb-2">Notes:</h3>
        <p class="whitespace-pre-line">{{ invoice.notes }}</p>
      </div>

      <div>
        <h3 class="text-lg font-semibold mb-2">Conditions de paiement:</h3>
        <p>Paiement à réception de facture.</p>
        <p>Tout retard de paiement entraînera des pénalités au taux annuel de 10%.</p>
      </div>
    </div>

    <!-- Pied de page -->
    <div class="px-8 pt-10 border-t mt-10 text-sm footer-section">
      <p class="text-center mb-4">Merci pour votre confiance !</p>

      <!-- Infos confidentielles en majuscules et plus gros -->
      <div class="text-center my-2 font-semibold">
        <p class="uppercase">
          <span v-if="settings.company_siret">IDE: {{ settings.company_siret }}</span>
          <span v-if="settings.company_vat && settings.company_siret"> - </span>
          <span v-if="settings.company_vat">TVA: {{ settings.company_vat }}</span>
          <span v-if="settings.company_iban && (settings.company_siret || settings.company_vat)"> - </span>
          <span v-if="settings.company_iban">IBAN: {{ settings.company_iban }}</span>
        </p>
      </div>

      <!-- Infos de contact en gris clair sur une ligne -->
      <p class="text-center text-gray-500 mt-4">
        <span v-if="settings.company_address">{{ settings.company_address }}</span>
        <span v-if="settings.company_postal_code || settings.company_city">
          <span v-if="settings.company_address">, </span>
          {{ settings.company_postal_code }} {{ settings.company_city }}
        </span>
        <span v-if="settings.company_phone">
          <span v-if="settings.company_address || settings.company_postal_code || settings.company_city"> - </span>
          Tél: {{ settings.company_phone }}
        </span>
        <span v-if="settings.company_email">
          <span v-if="settings.company_address || settings.company_postal_code || settings.company_city || settings.company_phone"> - </span>
          Email: {{ settings.company_email }}
        </span>
      </p>

      <p v-if="settings.invoice_footer" class="whitespace-pre-line text-center mt-2">{{ settings.invoice_footer }}</p>
    </div>

    <!-- Boutons d'impression (visible uniquement à l'écran) -->
    <div class="fixed top-4 right-4 print:hidden flex gap-2">
      <button
        @click="goBack()"
        class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-gray-700"
      >
        Retour
      </button>
      <button
        @click="downloadPdf()"
        class="bg-primary text-white px-4 py-2 rounded shadow hover:bg-primary/90"
      >
        Télécharger PDF
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  invoice: Object,
  settings: Object
});

const logoUrl = computed(() => {
  if (props.settings?.logo_path) {
    return `/storage/${props.settings.logo_path}`;
  }
  return null;
});

const goBack = () => {
  window.history.back();
};

const downloadPdf = () => {
  // URL de l'API pour générer le PDF avec le paramètre de téléchargement
  const pdfUrl = route('invoices.pdf', props.invoice.id) + '?download=true';

  // Ouvrir le PDF dans un nouvel onglet
  window.open(pdfUrl, '_blank');
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(date);
};

const formatAmount = (amount) => {
  if (amount === undefined || amount === null) return '-';
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: props.settings?.currency || 'EUR' }).format(amount);
};

// Déclencher l'impression automatiquement si en mode impression
onMounted(() => {
  // Vérifier si nous sommes dans un contexte d'impression (URL contient print=true)
  const urlParams = new URLSearchParams(window.location.search);
  const shouldPrint = urlParams.get('print') === 'true';

  if (shouldPrint) {
    // Attendre que la page soit complètement chargée avant d'imprimer
    setTimeout(() => {
      window.print();
      // Fermer la fenêtre après l'impression (ou après un délai si l'utilisateur annule)
      setTimeout(() => {
        window.close();
      }, 1000);
    }, 500);
  }
});
</script>

<style>
@page {
  margin: 15mm 15mm 30mm 15mm;
  size: A4;
}

.pdf-container {
  margin: 0;
  padding: 20px;
}

/* Configuration des sauts de page */
thead {
  display: table-header-group;
}

tfoot {
  display: table-footer-group;
}

tr {
  page-break-inside: avoid;
}

/* Éviter les sauts de page dans certaines sections */
.header-section {
  page-break-inside: avoid;
  margin-top: 10px;
}

.client-info-section {
  page-break-inside: avoid;
}

.totals-section {
  page-break-inside: avoid;
}

/* Forcer des sauts de page si nécessaire */
.page-break-before {
  page-break-before: always;
}

.page-break-after {
  page-break-after: always;
}

.no-break {
  page-break-inside: avoid;
}

@media print {
  body {
    background-color: white !important;
    margin: 0 !important;
    padding: 0 !important;
    font-size: 12pt !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  body::before, body::after {
    display: none !important;
    content: none !important;
  }

  body > *:not(.pdf-container) {
    display: none !important;
  }

  .container {
    max-width: 100% !important;
    box-shadow: none !important;
    padding: 20px !important;
    margin: 0 !important;
  }

  img {
    max-width: 100% !important;
    height: auto !important;
  }

  .print\:hidden {
    display: none !important;
  }

  .bg-gray-100, .bg-blue-100, .bg-green-100, .bg-red-100 {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
