<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-6">
      <div class="flex justify-between items-center mb-6 px-6">
        <h1 class="text-2xl font-bold">{{ client.name }}</h1>
        <div class="flex space-x-3">
          <Link :href="route('clients.edit', client.id)" class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
            Modifier
          </Link>
          <button @click="confirmDelete" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700">
            Supprimer
          </button>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informations personnelles -->
            <div class="space-y-4">
              <h2 class="text-lg font-semibold border-b pb-2">Informations personnelles</h2>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-sm text-gray-500">Nom</div>
                  <div>{{ client.name }}</div>
                </div>

                <div v-if="client.email">
                  <div class="text-sm text-gray-500">Email</div>
                  <div>{{ client.email }}</div>
                </div>

                <div v-if="client.phone">
                  <div class="text-sm text-gray-500">Téléphone</div>
                  <div>{{ client.phone }}</div>
                </div>
              </div>
            </div>

            <!-- Adresse -->
            <div class="space-y-4">
              <h2 class="text-lg font-semibold border-b pb-2">Adresse</h2>

              <div v-if="hasAddress" class="grid grid-cols-1 gap-1">
                <div v-if="client.address">{{ client.address }}</div>
                <div v-if="client.postal_code || client.city">
                  {{ client.postal_code || '' }} {{ client.city || '' }}
                </div>
                <div v-if="client.country">{{ client.country }}</div>
              </div>
              <div v-else class="text-gray-400 italic">Aucune adresse renseignée</div>
            </div>

            <!-- Informations professionnelles -->
            <div class="space-y-4">
              <h2 class="text-lg font-semibold border-b pb-2">Informations professionnelles</h2>

              <div class="grid grid-cols-1 gap-4">
                <div v-if="client.company_name">
                  <div class="text-sm text-gray-500">Entreprise</div>
                  <div>{{ client.company_name }}</div>
                </div>

                <div v-if="client.siret">
                  <div class="text-sm text-gray-500">IDE</div>
                  <div>{{ client.siret }}</div>
                </div>
              </div>
              <div v-if="!client.company_name && !client.siret" class="text-gray-400 italic">
                Aucune information professionnelle renseignée
              </div>
            </div>

            <!-- Notes -->
            <div class="space-y-4">
              <h2 class="text-lg font-semibold border-b pb-2">Notes</h2>

              <div v-if="client.notes" class="whitespace-pre-line">{{ client.notes }}</div>
              <div v-else class="text-gray-400 italic">Aucune note</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Factures -->
      <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4 px-6">Factures</h2>

        <div v-if="client.invoices && client.invoices.length" class="bg-white rounded-lg shadow-md overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Numéro
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Échéance
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Montant
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Statut
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="invoice in client.invoices" :key="invoice.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <Link :href="`/invoices/${invoice.id}`" class="text-primary hover:underline">
                    {{ invoice.invoice_number }}
                  </Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatDate(invoice.invoice_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatDate(invoice.due_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatAmount(invoice.total) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(invoice.status)">
                    {{ formatStatus(invoice.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="bg-white rounded-lg shadow-md p-6 text-center text-gray-500">
          Aucune facture pour ce client.
          <Link :href="`/invoices/create?client_id=${client.id}`" class="text-primary hover:underline ml-1">
            Créer une facture
          </Link>
        </div>
      </div>

      <!-- Devis -->
      <div class="mt-8">
        <h2 class="text-xl font-semibold mb-4 px-6">Devis</h2>

        <div v-if="client.quotes && client.quotes.length" class="bg-white rounded-lg shadow-md overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Numéro
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Expiration
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Montant
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Statut
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="quote in client.quotes" :key="quote.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <Link :href="`/quotes/${quote.id}`" class="text-primary hover:underline">
                    {{ quote.quote_number }}
                  </Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatDate(quote.quote_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatDate(quote.expiry_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatAmount(quote.total) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getQuoteStatusClass(quote.status)">
                    {{ formatQuoteStatus(quote.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="bg-white rounded-lg shadow-md p-6 text-center text-gray-500">
          Aucun devis pour ce client.
          <Link :href="`/quotes/create?client_id=${client.id}`" class="text-primary hover:underline ml-1">
            Créer un devis
          </Link>
        </div>
      </div>

      <Dialog :open="deleteModal" @update:open="deleteModal = $event">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Supprimer le client</DialogTitle>
            <DialogDescription>
              Êtes-vous sûr de vouloir supprimer ce client ? Cette action est irréversible.
            </DialogDescription>
          </DialogHeader>
          <div class="flex justify-end space-x-2">
            <Button variant="outline" @click="deleteModal = false">Annuler</Button>
            <Button variant="destructive" @click="deleteClient">Supprimer</Button>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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
  client: Object,
  currency: String
});

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Clients', href: route('clients.index') },
  { title: props.client.name, href: route('clients.show', props.client.id) }
];

const deleteModal = ref(false);

const hasAddress = computed(() => {
  return props.client.address || props.client.city || props.client.postal_code || props.client.country;
});

const confirmDelete = () => {
  deleteModal.value = true;
};

const deleteClient = () => {
  router.delete(route('clients.destroy', props.client.id), {
    onSuccess: () => {
      deleteModal.value = false;
    }
  });
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

const formatQuoteStatus = (status) => {
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

const getQuoteStatusClass = (status) => {
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
