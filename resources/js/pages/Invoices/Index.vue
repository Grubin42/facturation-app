<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestion des factures</h1>
        <Link :href="route('invoices.create')" class="bg-primary text-white py-2 px-4 rounded hover:bg-primary/90">
          Créer une facture
        </Link>
      </div>

      <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.success }}
      </div>

      <!-- Filtres -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Filtres</h2>
        <form @submit.prevent="applyFilters">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <Label for="client_id">Client</Label>
              <select
                id="client_id"
                v-model="filters.client_id"
                class="w-full mt-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
              >
                <option value="">Tous les clients</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.name }}
                </option>
              </select>
            </div>

            <div>
              <Label for="status">Statut</Label>
              <select
                id="status"
                v-model="filters.status"
                class="w-full mt-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
              >
                <option value="">Tous les statuts</option>
                <option v-for="(label, value) in statuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <Label for="start_date">Date de début</Label>
              <Input
                id="start_date"
                v-model="filters.start_date"
                type="date"
                class="w-full mt-1"
              />
            </div>

            <div>
              <Label for="end_date">Date de fin</Label>
              <Input
                id="end_date"
                v-model="filters.end_date"
                type="date"
                class="w-full mt-1"
              />
            </div>
          </div>

          <div class="flex justify-end mt-4 space-x-2">
            <Button
              type="button"
              variant="outline"
              @click="resetFilters"
            >
              Réinitialiser
            </Button>
            <Button
              type="submit"
            >
              Filtrer
            </Button>
          </div>
        </form>
      </div>

      <!-- Liste des factures -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div v-if="invoices.length === 0" class="p-6 text-center text-gray-500">
          Aucune facture trouvée. Créez votre première facture !
        </div>
        <table v-else class="min-w-full divide-y divide-gray-200">
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
                Échéance
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Montant
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Statut
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in invoices" :key="invoice.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <Link :href="route('invoices.show', invoice.id)" class="text-primary hover:underline">
                  {{ invoice.invoice_number }}
                </Link>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ invoice.client.name }}
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
                  {{ statuses[invoice.status] }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <Link :href="route('invoices.show', invoice.id)" class="text-blue-600 hover:text-blue-900">
                    Voir
                  </Link>
                  <Link :href="route('invoices.edit', invoice.id)" class="text-blue-600 hover:text-blue-900">
                    Modifier
                  </Link>
                  <button @click="confirmDelete(invoice)" class="text-red-600 hover:text-red-900">
                    Supprimer
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Dialog :open="deleteModal.isOpen" @update:open="deleteModal.isOpen = $event">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Supprimer la facture</DialogTitle>
            <DialogDescription>
              Êtes-vous sûr de vouloir supprimer cette facture ? Cette action est irréversible.
            </DialogDescription>
          </DialogHeader>
          <div class="flex justify-end space-x-2">
            <Button variant="outline" @click="deleteModal.isOpen = false">Annuler</Button>
            <Button variant="destructive" @click="deleteInvoice">Supprimer</Button>
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  invoices: Array,
  clients: Array,
  filters: Object,
  statuses: Object
});

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Factures', href: route('invoices.index'), current: true }
];

const filters = ref({
  client_id: props.filters.client_id || '',
  status: props.filters.status || '',
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || ''
});

const deleteModal = ref({
  isOpen: false,
  invoice: null
});

const applyFilters = () => {
  router.get(route('invoices.index'), {
    client_id: filters.value.client_id,
    status: filters.value.status,
    start_date: filters.value.start_date,
    end_date: filters.value.end_date
  }, {
    preserveState: true
  });
};

const resetFilters = () => {
  filters.value = {
    client_id: '',
    status: '',
    start_date: '',
    end_date: ''
  };

  // Appliquer les filtres vides
  applyFilters();
};

const confirmDelete = (invoice) => {
  deleteModal.value.invoice = invoice;
  deleteModal.value.isOpen = true;
};

const deleteInvoice = () => {
  router.delete(route('invoices.destroy', deleteModal.value.invoice.id), {
    onSuccess: () => {
      deleteModal.value.isOpen = false;
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
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
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
