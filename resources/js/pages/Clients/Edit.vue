<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Modifier le client</h1>
        <Link :href="route('clients.index')" class="text-gray-600 hover:text-gray-900">
          Retour à la liste
        </Link>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
        <form @submit.prevent="submit">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informations personnelles -->
            <div class="space-y-4">
              <h2 class="text-lg font-semibold border-b pb-2">Informations personnelles</h2>

              <div>
                <Label for="name">Nom <span class="text-red-500">*</span></Label>
                <Input
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="w-full mt-1"
                  :class="{ 'border-red-500': form.errors.name }"
                  required
                />
                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                  {{ form.errors.name }}
                </div>
              </div>

              <div>
                <Label for="email">Email</Label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="w-full mt-1"
                  :class="{ 'border-red-500': form.errors.email }"
                />
                <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">
                  {{ form.errors.email }}
                </div>
              </div>

              <div>
                <Label for="phone">Téléphone</Label>
                <Input
                  id="phone"
                  v-model="form.phone"
                  type="text"
                  class="w-full mt-1"
                  :class="{ 'border-red-500': form.errors.phone }"
                />
                <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">
                  {{ form.errors.phone }}
                </div>
              </div>
            </div>

            <!-- Adresse -->
            <div class="space-y-4">
              <h2 class="text-lg font-semibold border-b pb-2">Adresse</h2>

              <div>
                <Label for="address">Adresse</Label>
                <Input
                  id="address"
                  v-model="form.address"
                  type="text"
                  class="w-full mt-1"
                  :class="{ 'border-red-500': form.errors.address }"
                />
                <div v-if="form.errors.address" class="text-red-500 text-sm mt-1">
                  {{ form.errors.address }}
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label for="postal_code">Code postal</Label>
                  <Input
                    id="postal_code"
                    v-model="form.postal_code"
                    type="text"
                    class="w-full mt-1"
                    :class="{ 'border-red-500': form.errors.postal_code }"
                  />
                  <div v-if="form.errors.postal_code" class="text-red-500 text-sm mt-1">
                    {{ form.errors.postal_code }}
                  </div>
                </div>

                <div>
                  <Label for="city">Ville</Label>
                  <Input
                    id="city"
                    v-model="form.city"
                    type="text"
                    class="w-full mt-1"
                    :class="{ 'border-red-500': form.errors.city }"
                  />
                  <div v-if="form.errors.city" class="text-red-500 text-sm mt-1">
                    {{ form.errors.city }}
                  </div>
                </div>
              </div>

              <div>
                <Label for="country">Pays</Label>
                <Input
                  id="country"
                  v-model="form.country"
                  type="text"
                  class="w-full mt-1"
                  :class="{ 'border-red-500': form.errors.country }"
                />
                <div v-if="form.errors.country" class="text-red-500 text-sm mt-1">
                  {{ form.errors.country }}
                </div>
              </div>
            </div>

            <!-- Informations professionnelles -->
            <div class="space-y-4">
              <h2 class="text-lg font-semibold border-b pb-2">Informations professionnelles</h2>

              <div>
                <Label for="company_name">Nom de l'entreprise</Label>
                <Input
                  id="company_name"
                  v-model="form.company_name"
                  type="text"
                  class="w-full mt-1"
                  :class="{ 'border-red-500': form.errors.company_name }"
                />
                <div v-if="form.errors.company_name" class="text-red-500 text-sm mt-1">
                  {{ form.errors.company_name }}
                </div>
              </div>

              <div>
                <Label for="siret">IDE</Label>
                <Input
                  id="siret"
                  v-model="form.siret"
                  type="text"
                  placeholder="CHE-123.456.789"
                  :class="{ 'border-red-500': form.errors.siret }"
                />
                <div v-if="form.errors.siret" class="text-red-500 text-sm mt-1">
                  {{ form.errors.siret }}
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div class="space-y-4">
              <h2 class="text-lg font-semibold border-b pb-2">Notes</h2>

              <div>
                <Label for="notes">Notes</Label>
                <textarea
                  id="notes"
                  v-model="form.notes"
                  class="w-full mt-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
                  :class="{ 'border-red-500': form.errors.notes }"
                  rows="5"
                ></textarea>
                <div v-if="form.errors.notes" class="text-red-500 text-sm mt-1">
                  {{ form.errors.notes }}
                </div>
              </div>
            </div>
          </div>

          <div class="mt-8 flex justify-end space-x-3">
            <Button
              type="button"
              variant="outline"
              @click="cancel"
            >
              Annuler
            </Button>
            <Button
              type="submit"
              :disabled="form.processing"
            >
              Enregistrer
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  client: Object
});

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Clients', href: route('clients.index') },
  { title: props.client.name, href: route('clients.edit', props.client.id) }
];

const form = useForm({
  name: props.client.name,
  email: props.client.email || '',
  phone: props.client.phone || '',
  address: props.client.address || '',
  city: props.client.city || '',
  postal_code: props.client.postal_code || '',
  country: props.client.country || '',
  company_name: props.client.company_name || '',
  siret: props.client.siret || '',
  notes: props.client.notes || '',
});

const cancel = () => {
  router.visit(route('clients.index'));
};

const submit = () => {
  form.put(route('clients.update', props.client.id));
};
</script>
