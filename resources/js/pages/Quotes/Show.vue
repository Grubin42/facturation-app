<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-6">
      <div class="flex justify-between items-center mb-6 px-6">
        <h1 class="text-2xl font-bold">Devis {{ quote.quote_number }}</h1>
        <div class="flex space-x-3">
          <Link :href="route('quotes.edit', quote.id)" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
            Modifier
          </Link>
          <Link :href="route('quotes.pdf', quote.id)" class="bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-700">
            PDF
          </Link>
          <button
            v-if="quote.status !== 'converted'"
            @click="confirmConvert"
            class="bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700"
          >
            Convertir en facture
          </button>
          <button @click="confirmDelete" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700">
            Supprimer
          </button>
        </div>
      </div>

      <!-- Statut et actions spécifiques -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center">
          <div class="flex items-center">
            <span class="mr-3">Statut:</span>
            <span :class="getStatusClass(quote.status)">
              {{ formatStatus(quote.status) }}
            </span>
          </div>
          <div class="flex space-x-3">
            <Button
              v-if="quote.status === 'draft' || quote.status === 'sent'"
              variant="outline"
              @click="markAsAccepted"
            >
              Marquer comme accepté
            </Button>
            <Button
              v-if="quote.status === 'draft' || quote.status === 'sent'"
              variant="outline"
              @click="markAsRejected"
            >
              Marquer comme refusé
            </Button>
          </div>
        </div>
      </div>

      <!-- Informations du devis -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 p-6 gap-6">
          <!-- Client et dates -->
          <div>
            <h2 class="text-lg font-semibold mb-3">Client</h2>
            <div class="border rounded-md p-4">
              <div class="mb-2">
                <Link :href="route('clients.show', quote.client.id)" class="text-primary hover:underline font-medium">
                  {{ quote.client.name }}
                </Link>
              </div>
              <div v-if="quote.client.company_name" class="text-gray-600">
                {{ quote.client.company_name }}
              </div>
              <div v-if="quote.client.email" class="text-gray-600">
                {{ quote.client.email }}
              </div>
              <div v-if="quote.client.address" class="text-gray-600 mt-2">
                {{ quote.client.address }}
              </div>
              <div v-if="quote.client.postal_code || quote.client.city" class="text-gray-600">
                {{ quote.client.postal_code }} {{ quote.client.city }}
              </div>
              <div v-if="quote.client.country" class="text-gray-600">
                {{ quote.client.country }}
              </div>
            </div>
          </div>

          <div>
            <h2 class="text-lg font-semibold mb-3">Informations</h2>
            <div class="border rounded-md p-4">
              <div class="grid grid-cols-2 gap-2">
                <div class="text-gray-600">Numéro de devis:</div>
                <div>{{ quote.quote_number }}</div>

                <div class="text-gray-600">Date du devis:</div>
                <div>{{ formatDate(quote.quote_date) }}</div>

                <div class="text-gray-600">Date d'expiration:</div>
                <div>{{ formatDate(quote.expiry_date) }}</div>

                <div class="text-gray-600">Total:</div>
                <div class="font-semibold">{{ formatAmount(quote.total) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Éléments du devis -->
        <div class="px-6 pb-6">
          <h2 class="text-lg font-semibold mb-3">Éléments du devis</h2>
          <div class="border rounded-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Description
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Quantité
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Prix unitaire
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    TVA (%)
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in quote.items" :key="item.id">
                  <td class="px-6 py-4 whitespace-pre-wrap">
                    {{ item.description }}
                  </td>
                  <td class="px-6 py-4 text-right">
                    {{ item.quantity }}
                  </td>
                  <td class="px-6 py-4 text-right">
                    {{ formatAmount(item.unit_price) }}
                  </td>
                  <td class="px-6 py-4 text-right">
                    {{ item.tax_rate }}%
                  </td>
                  <td class="px-6 py-4 text-right font-medium">
                    {{ formatAmount(item.total) }}
                  </td>
                </tr>
              </tbody>
              <tfoot class="bg-gray-50">
                <tr>
                  <td colspan="3" class="px-6 py-3"></td>
                  <td class="px-6 py-3 text-right text-sm font-medium">Sous-total:</td>
                  <td class="px-6 py-3 text-right text-sm font-medium">
                    {{ formatAmount(quote.subtotal) }}
                  </td>
                </tr>
                <tr>
                  <td colspan="3" class="px-6 py-3"></td>
                  <td class="px-6 py-3 text-right text-sm font-medium">TVA:</td>
                  <td class="px-6 py-3 text-right text-sm font-medium">
                    {{ formatAmount(quote.tax_amount) }}
                  </td>
                </tr>
                <tr>
                  <td colspan="3" class="px-6 py-3"></td>
                  <td class="px-6 py-3 text-right text-sm font-bold">Total:</td>
                  <td class="px-6 py-3 text-right text-sm font-bold">
                    {{ formatAmount(quote.total) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Notes -->
        <div class="px-6 pb-6" v-if="quote.notes">
          <h2 class="text-lg font-semibold mb-3">Notes</h2>
          <div class="border rounded-md p-4 whitespace-pre-line">
            {{ quote.notes }}
          </div>
        </div>
      </div>

      <!-- Facture associée si convertie -->
      <div v-if="quote.status === 'converted' && quote.invoice_id" class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-3">Facture associée</h2>
        <p>Ce devis a été converti en facture.</p>
        <div class="mt-2">
          <Link :href="route('invoices.show', quote.invoice_id)" class="text-primary hover:underline">
            Voir la facture
          </Link>
        </div>
      </div>

      <!-- Modal de confirmation de suppression -->
      <Dialog :open="deleteModal" @update:open="deleteModal = $event">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Supprimer le devis</DialogTitle>
            <DialogDescription>
              Êtes-vous sûr de vouloir supprimer ce devis ? Cette action est irréversible.
            </DialogDescription>
          </DialogHeader>
          <div class="flex justify-end space-x-2">
            <Button variant="outline" @click="deleteModal = false">Annuler</Button>
            <Button variant="destructive" @click="deleteQuote">Supprimer</Button>
          </div>
        </DialogContent>
      </Dialog>

      <!-- Modal de confirmation de conversion -->
      <Dialog :open="convertModal" @update:open="convertModal = $event">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Convertir en facture</DialogTitle>
            <DialogDescription>
              Voulez-vous convertir ce devis en facture ? Une nouvelle facture sera créée avec les mêmes éléments.
            </DialogDescription>
          </DialogHeader>
          <div class="flex justify-end space-x-2">
            <Button variant="outline" @click="convertModal = false">Annuler</Button>
            <Button variant="default" @click="convertToInvoice">Convertir</Button>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
  quote: Object,
  currency: String
});

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Devis', href: route('quotes.index') },
  { title: props.quote.quote_number, href: route('quotes.show', props.quote.id) }
];

const deleteModal = ref(false);
const convertModal = ref(false);

const confirmDelete = () => {
  deleteModal.value = true;
};

const deleteQuote = () => {
  router.delete(route('quotes.destroy', props.quote.id), {
    onSuccess: () => {
      deleteModal.value = false;
    }
  });
};

const confirmConvert = () => {
  convertModal.value = true;
};

const convertToInvoice = () => {
  router.post(route('quotes.convert', props.quote.id), {}, {
    onSuccess: () => {
      convertModal.value = false;
    }
  });
};

const markAsAccepted = () => {
  router.post(route('quotes.accept', props.quote.id));
};

const markAsRejected = () => {
  router.post(route('quotes.reject', props.quote.id));
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(date);
};

const formatAmount = (amount) => {
  if (amount === undefined || amount === null) return '-';
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: props.currency }).format(amount);
};

const formatStatus = (status) => {
  const statusMap = {
    'draft': 'Brouillon',
    'sent': 'Envoyé',
    'accepted': 'Accepté',
    'rejected': 'Refusé',
    'expired': 'Expiré',
    'converted': 'Converti'
  };
  return statusMap[status] || status;
};

const getStatusClass = (status) => {
  const classMap = {
    'draft': 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800',
    'sent': 'px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800',
    'accepted': 'px-2 py-1 text-xs rounded-full bg-green-100 text-green-800',
    'rejected': 'px-2 py-1 text-xs rounded-full bg-red-100 text-red-800',
    'expired': 'px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800',
    'converted': 'px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800'
  };
  return classMap[status] || 'px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800';
};
</script>
