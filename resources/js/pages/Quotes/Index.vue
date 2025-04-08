<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Devis</h1>
        <Link :href="route('quotes.create')" class="bg-primary text-white py-2 px-4 rounded-md hover:bg-primary/90">
          Nouveau devis
        </Link>
      </div>

      <!-- Filtres -->
      <div class="bg-white p-4 rounded-lg shadow mb-6">
        <div class="flex flex-wrap gap-4">
          <div>
            <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
            <select
              id="status-filter"
              v-model="filters.status"
              class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
            >
              <option value="">Tous</option>
              <option value="draft">Brouillon</option>
              <option value="sent">Envoyé</option>
              <option value="accepted">Accepté</option>
              <option value="rejected">Refusé</option>
              <option value="expired">Expiré</option>
              <option value="converted">Converti</option>
            </select>
          </div>

          <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Recherche</label>
            <Input
              id="search"
              v-model="filters.search"
              placeholder="Numéro ou client..."
              class="w-full sm:w-80"
            />
          </div>
        </div>
      </div>

      <!-- Liste de devis -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="filteredQuotes.length === 0" class="text-center py-8 text-gray-500">
          Aucun devis trouvé. <Link :href="route('quotes.create')" class="text-primary hover:underline">Créer un devis</Link>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Numéro
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Client
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
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="quote in filteredQuotes" :key="quote.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <Link :href="route('quotes.show', quote.id)" class="text-primary hover:underline">
                    {{ quote.quote_number }}
                  </Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <Link :href="route('clients.show', quote.client_id)" class="hover:underline">
                    {{ quote.client.name }}
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
                  <span :class="getStatusClass(quote.status)">
                    {{ formatStatus(quote.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <Link :href="route('quotes.show', quote.id)" class="text-indigo-600 hover:text-indigo-900">
                      Voir
                    </Link>
                    <Link :href="route('quotes.edit', quote.id)" class="text-blue-600 hover:text-blue-900">
                      Modifier
                    </Link>
                    <button
                      @click="() => confirmDelete(quote)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Supprimer
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
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
  </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription
} from '@/components/ui/dialog';

const props = defineProps({
  quotes: Array,
  currency: String
});

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Devis', href: route('quotes.index') }
];

const filters = ref({
  status: '',
  search: ''
});

const deleteModal = ref(false);
const quoteToDelete = ref(null);

const confirmDelete = (quote) => {
  quoteToDelete.value = quote;
  deleteModal.value = true;
};

const deleteQuote = () => {
  if (!quoteToDelete.value) return;

  router.delete(route('quotes.destroy', quoteToDelete.value.id), {
    onSuccess: () => {
      deleteModal.value = false;
      quoteToDelete.value = null;
    }
  });
};

const filteredQuotes = computed(() => {
  let filtered = [...props.quotes];

  // Filtre par statut
  if (filters.value.status) {
    filtered = filtered.filter(quote => quote.status === filters.value.status);
  }

  // Filtre par recherche
  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase();
    filtered = filtered.filter(quote =>
      quote.quote_number.toLowerCase().includes(searchLower) ||
      quote.client.name.toLowerCase().includes(searchLower)
    );
  }

  return filtered;
});

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
