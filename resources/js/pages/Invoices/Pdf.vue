<template>
  <div class="container mx-auto py-6 max-w-4xl bg-white">
    <!-- En-tête -->
    <div class="flex justify-between items-start mb-10 px-8">
      <div>
        <h1 class="text-3xl font-bold mb-1">FACTURE</h1>
        <p class="text-lg font-semibold">{{ invoice.invoice_number }}</p>
      </div>
      <div class="text-right">
        <h2 class="text-lg font-bold mb-2">{{ settings.company_name }}</h2>
        <p v-if="settings.company_address">{{ settings.company_address }}</p>
        <p v-if="settings.company_postal_code || settings.company_city">
          {{ settings.company_postal_code }} {{ settings.company_city }}
        </p>
        <p v-if="settings.company_country">{{ settings.company_country }}</p>
        <p v-if="settings.company_phone">Tél: {{ settings.company_phone }}</p>
        <p v-if="settings.company_email">Email: {{ settings.company_email }}</p>
        <p v-if="settings.company_siret">IDE: {{ settings.company_siret }}</p>
        <p v-if="settings.company_vat">TVA: {{ settings.company_vat }}</p>
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
    <div class="px-8 pt-10 border-t mt-10 text-center text-sm text-gray-600">
      <p>Merci pour votre confiance !</p>
      <p v-if="settings.invoice_footer" class="whitespace-pre-line">{{ settings.invoice_footer }}</p>
      <p v-else>
        {{ settings.company_name }}
        <template v-if="settings.company_siret">- IDE: {{ settings.company_siret }}</template>
        <template v-if="settings.company_vat">- TVA: {{ settings.company_vat }}</template>
      </p>
      <p>
        <template v-if="settings.company_address">{{ settings.company_address }}, </template>
        <template v-if="settings.company_postal_code">{{ settings.company_postal_code }} </template>
        <template v-if="settings.company_city">{{ settings.company_city }}</template>
        <template v-if="settings.company_phone"> - Tél: {{ settings.company_phone }}</template>
        <template v-if="settings.company_email"> - Email: {{ settings.company_email }}</template>
      </p>
    </div>

    <!-- Boutons d'impression (visible uniquement à l'écran) -->
    <div class="fixed top-4 right-4 print:hidden flex gap-2">
      <button
        @click="window.print()"
        class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-gray-700"
      >
        Imprimer
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
const props = defineProps({
  invoice: Object,
  settings: Object
});

const downloadPdf = () => {
  // Dans une application réelle, nous utiliserions une bibliothèque comme jsPDF
  // ou une API backend pour générer et télécharger un vrai PDF
  // Pour simuler, on utilise window.print() qui ouvre la boîte de dialogue d'impression
  // L'utilisateur peut alors choisir "Enregistrer au format PDF" dans les options d'impression
  window.print();

  // Note: dans une implémentation complète, on pourrait utiliser:
  // 1. Une requête API vers un endpoint qui génère le PDF avec Dompdf ou Snappy PDF
  // 2. jsPDF ou html2pdf.js pour générer le PDF côté client
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
</script>

<style>
@media print {
  @page {
    size: A4;
    margin: 10mm;
  }

  body {
    background-color: white;
  }

  .container {
    max-width: 100%;
    box-shadow: none;
  }
}
</style>
