<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestion des clients</h1>
        <Link :href="route('clients.create')" class="bg-primary text-white py-2 px-4 rounded hover:bg-primary/90">
          Ajouter un client
        </Link>
      </div>

      <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.success }}
      </div>

      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div v-if="clients.length === 0" class="p-6 text-center text-gray-500">
          Aucun client trouvé. Créez votre premier client !
        </div>
        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nom
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Entreprise
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Email
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Téléphone
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="client in clients" :key="client.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <Link :href="route('clients.show', client.id)" class="text-primary hover:underline">
                  {{ client.name }}
                </Link>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ client.company_name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ client.email || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ client.phone || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <Link :href="route('clients.edit', client.id)" class="text-blue-600 hover:text-blue-900">
                    Modifier
                  </Link>
                  <button @click="confirmDelete(client)" class="text-red-600 hover:text-red-900">
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
            <DialogTitle>Supprimer le client</DialogTitle>
            <DialogDescription>
              Êtes-vous sûr de vouloir supprimer ce client ? Cette action est irréversible.
            </DialogDescription>
          </DialogHeader>
          <div class="flex justify-end space-x-2">
            <Button variant="outline" @click="deleteModal.isOpen = false">Annuler</Button>
            <Button variant="destructive" @click="deleteClient">Supprimer</Button>
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
  clients: Array
});

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Clients', href: route('clients.index'), current: true }
];

const deleteModal = ref({
  isOpen: false,
  client: null
});

const confirmDelete = (client) => {
  deleteModal.value.client = client;
  deleteModal.value.isOpen = true;
};

const deleteClient = () => {
  router.delete(route('clients.destroy', deleteModal.value.client.id), {
    onSuccess: () => {
      deleteModal.value.isOpen = false;
    }
  });
};
</script>
