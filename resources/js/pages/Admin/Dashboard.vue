<script setup>
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  stats: Object,
});
</script>

<template>
  <Head title="Tableau de bord administrateur" />

  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Tableau de bord administrateur
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Statistiques des utilisateurs</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <div class="bg-blue-50 p-6 rounded-lg shadow">
                <h4 class="font-semibold text-gray-700">Total des utilisateurs</h4>
                <p class="text-3xl font-bold text-blue-600">{{ stats.total_users }}</p>
              </div>

              <div class="bg-green-50 p-6 rounded-lg shadow">
                <h4 class="font-semibold text-gray-700">Utilisateurs actifs</h4>
                <p class="text-3xl font-bold text-green-600">{{ stats.active_users }}</p>
              </div>

              <div class="bg-red-50 p-6 rounded-lg shadow">
                <h4 class="font-semibold text-gray-700">Utilisateurs en attente</h4>
                <p class="text-3xl font-bold text-red-600">{{ stats.inactive_users }}</p>
              </div>
            </div>

            <div class="flex justify-end">
              <a
                href="/admin/users"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition"
              >
                Gérer les utilisateurs
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
