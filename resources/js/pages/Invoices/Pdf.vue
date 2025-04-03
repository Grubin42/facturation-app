<template>
  <div class="container mx-auto py-6 max-w-4xl bg-white pdf-container">
    <title v-if="isPrintMode">Facture {{ invoice.invoice_number }} - {{ settings?.company_name || 'Facturation App' }}</title>

    <!-- En-tête -->
    <div class="flex justify-between items-start mb-10 px-8">
      <div>
        <h1 class="text-3xl font-bold mb-1">FACTURE</h1>
        <p class="text-lg font-semibold">{{ invoice.invoice_number }}</p>
      </div>
      <div class="text-right flex flex-col items-end">
        <div v-if="settings.logo_path" class="mb-2 max-w-[150px] max-h-[80px] flex justify-center">
          <img :src="logoUrl" alt="Logo de l'entreprise" class="object-contain" />
        </div>
        <h2 class="text-lg font-bold mt-1">{{ settings.company_name }}</h2>
      </div>
    </div>

    <!-- Informations de facturation -->
    <div class="flex justify-between mb-10 px-8">
      <div class="w-1/2">
        <h3 class="text-lg font-semibold mb-2">Facturé à:</h3>
        <p class="font-semibold">{{ invoice.client.name }}</p>
        <p>{{ invoice.client.address }}</p>
        <p>{{ invoice.client.postal_code }} {{ invoice.client.city }}</p>
        <p>{{ invoice.client.country }}</p>
        <p>Tél: {{ invoice.client.phone }}</p>
        <p>Email: {{ invoice.client.email }}</p>
      </div>
      <div class="w-1/2 text-right">
        <div class="flex justify-end">
          <table class="border-collapse">
            <tr>
              <td class="font-semibold pr-4 py-1 text-right">Date de facture:</td>
              <td class="py-1">{{ formatDate(invoice.invoice_date) }}</td>
            </tr>
            <tr>
              <td class="font-semibold pr-4 py-1 text-right">Date d'échéance:</td>
              <td class="py-1">{{ formatDate(invoice.due_date) }}</td>
            </tr>
            <tr v-if="invoice.status === 'paid'">
              <td class="font-semibold pr-4 py-1 text-right">Date de paiement:</td>
              <td class="py-1">{{ formatDate(invoice.paid_at) }}</td>
            </tr>
            <tr>
              <td class="font-semibold pr-4 py-1 text-right">Statut:</td>
              <td class="py-1">
                <span :class="getStatusClass(invoice.status)">
                  {{ getStatusLabel(invoice.status) }}
                </span>
              </td>
            </tr>
          </table>
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
        <tfoot>
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
    <div class="px-8 pt-10 border-t mt-10 text-sm">
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
  // Ouvrir une nouvelle fenêtre avec uniquement le contenu à imprimer
  const printWindow = window.open('', '_blank', 'width=800,height=900');

  if (!printWindow) {
    alert('Veuillez autoriser les fenêtres pop-up pour télécharger le PDF.');
    return;
  }

  // Construction du HTML pour la nouvelle fenêtre
  const htmlContent = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>${props.invoice.invoice_number} - ${props.invoice.client.name}</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <style>
        @page { size: A4; margin: 0; }
        body {
          font-family: Arial, sans-serif;
          margin: 0;
          padding: 0;
          background-color: white;
          color: #333;
        }
        .pdf-container {
          padding: 20px;
          max-width: 800px;
          margin: 0 auto;
        }
        /* Contrôle stricte de la taille du logo */
        img[alt="Logo de l'entreprise"] {
          max-width: 150px !important;
          max-height: 80px !important;
          object-fit: contain !important;
        }
        /* Cacher les boutons d'action */
        .fixed, .print\\:hidden {
          display: none !important;
        }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-start { align-items: flex-start; }
        .mb-10 { margin-bottom: 2.5rem; }
        .px-8 { padding-left: 2rem; padding-right: 2rem; }
        .text-3xl { font-size: 1.875rem; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 0.25rem; }
        .text-lg { font-size: 1.125rem; }
        .font-semibold { font-weight: 600; }
        .text-right { text-align: right; }
        .flex-col { flex-direction: column; }
        .items-end { align-items: flex-end; }
        .mb-2 { margin-bottom: 0.5rem; }
        .w-1/2 { width: 50%; }
        .border-collapse { border-collapse: collapse; }
        .pr-4 { padding-right: 1rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .min-w-full { min-width: 100%; }
        .border { border: 1px solid #e2e8f0; }
        .bg-gray-100 { background-color: #f7fafc; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .text-left { text-align: left; }
        .w-20 { width: 5rem; }
        .w-32 { width: 8rem; }
        .w-24 { width: 6rem; }
        .border-b { border-bottom: 1px solid #e2e8f0; }
        .border-x { border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; }
        .mb-6 { margin-bottom: 1.5rem; }
        .whitespace-pre-line { white-space: pre-line; }
        .pt-10 { padding-top: 2.5rem; }
        .border-t { border-top: 1px solid #e2e8f0; }
        .mt-10 { margin-top: 2.5rem; }
        .text-sm { font-size: 0.875rem; }
        .text-center { text-align: center; }
        .mb-4 { margin-bottom: 1rem; }
        .uppercase { text-transform: uppercase; }
        .text-gray-500 { color: #718096; }
        .mt-4 { margin-top: 1rem; }
        .mt-2 { margin-top: 0.5rem; }
      </style>
    </head>
    <body onload="setTimeout(function() {
      // Définir le nom du fichier pour l'enregistrement
      document.title = '${props.invoice.invoice_number} - ${props.invoice.client.name}';
      window.print();
      window.close();
    }, 500)">
      <div class="pdf-container">
        ${document.querySelector('.pdf-container').innerHTML.replace(/<div class="fixed.*?<\/div>/s, '')}
      </div>
    </body>
    </html>
  `;

  // Écriture du HTML dans la nouvelle fenêtre
  printWindow.document.open();
  printWindow.document.write(htmlContent);
  printWindow.document.close();
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

const getStatusLabel = (status) => {
  const statusMap = {
    'draft': 'Brouillon',
    'sent': 'Envoyée',
    'paid': 'Payée',
    'overdue': 'En retard',
    'cancelled': 'Annulée'
  };
  return statusMap[status] || status;
};

const getStatusClass = (status) => {
  const classMap = {
    'draft': 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800',
    'sent': 'px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800',
    'paid': 'px-2 py-1 text-xs rounded-full bg-green-100 text-green-800',
    'overdue': 'px-2 py-1 text-xs rounded-full bg-red-100 text-red-800',
    'cancelled': 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800'
  };
  return classMap[status] || 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800';
};

// Vérifier si on est en mode impression (via le paramètre d'URL)
const isPrintMode = computed(() => {
  return window.location.search.includes('print=true');
});

// Déclencher l'impression automatiquement en mode impression
onMounted(() => {
  if (isPrintMode.value) {
    // Cache les boutons en mode impression
    const actionButtons = document.querySelector('.fixed.print\\:hidden');
    if (actionButtons) {
      actionButtons.style.display = 'none';
    }

    // Lancer l'impression après un court délai
    setTimeout(() => {
      window.print();
    }, 1000);
  }
});
</script>

<style>
@page {
  margin: 0;
}

.pdf-container {
  margin: 0;
  padding: 20px;
}

@media print {
  /* Suppression des infos de l'en-tête du navigateur */
  @page {
    size: A4;
    margin: 0;
  }

  /* Supprimer la date et le texte "-Laravel" en haut de page */
  body::before, body::after {
    display: none !important;
    content: none !important;
  }

  /* Styles pour le contenu */
  body {
    background-color: white !important;
    margin: 0 !important;
    padding: 0 !important;
    font-size: 12pt !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  /* Force la suppression du header du navigateur */
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

  /* Assurer que les couleurs s'impriment correctement */
  .bg-gray-100, .bg-blue-100, .bg-green-100, .bg-red-100 {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
