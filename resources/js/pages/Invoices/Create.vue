<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Créer une facture</h1>
        <Link :href="route('invoices.index')" class="text-gray-600 hover:text-gray-900">
          Retour à la liste
        </Link>
      </div>

      <form @submit.prevent="submit">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Informations client -->
            <div>
              <Label for="client_id">Client</Label>
              <select
                id="client_id"
                v-model="form.client_id"
                class="w-full mt-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
                :class="{ 'border-red-500': form.errors.client_id }"
                required
              >
                <option value="">Sélectionnez un client</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.name }}
                </option>
              </select>
              <div v-if="form.errors.client_id" class="text-red-500 text-sm mt-1">
                {{ form.errors.client_id }}
              </div>
            </div>

            <!-- Numéro de facture -->
            <div>
              <Label for="invoice_number">Numéro de facture</Label>
              <Input
                id="invoice_number"
                v-model="form.invoice_number"
                type="text"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.invoice_number }"
                required
              />
              <div v-if="form.errors.invoice_number" class="text-red-500 text-sm mt-1">
                {{ form.errors.invoice_number }}
              </div>
            </div>

            <!-- Statut -->
            <div>
              <Label for="status">Statut</Label>
              <select
                id="status"
                v-model="form.status"
                class="w-full mt-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
                :class="{ 'border-red-500': form.errors.status }"
                required
              >
                <option value="draft">Brouillon</option>
                <option value="sent">Envoyée</option>
                <option value="paid">Payée</option>
              </select>
              <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">
                {{ form.errors.status }}
              </div>
            </div>

            <!-- Date de facture -->
            <div>
              <Label for="invoice_date">Date de facture</Label>
              <Input
                id="invoice_date"
                v-model="form.invoice_date"
                type="date"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.invoice_date }"
                required
              />
              <div v-if="form.errors.invoice_date" class="text-red-500 text-sm mt-1">
                {{ form.errors.invoice_date }}
              </div>
            </div>

            <!-- Date d'échéance -->
            <div>
              <Label for="due_date">Date d'échéance</Label>
              <Input
                id="due_date"
                v-model="form.due_date"
                type="date"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.due_date }"
                required
              />
              <div v-if="form.errors.due_date" class="text-red-500 text-sm mt-1">
                {{ form.errors.due_date }}
              </div>
            </div>

            <!-- Méthode de paiement (visible uniquement si statut = payé) -->
            <div v-if="form.status === 'paid'">
              <Label for="payment_method">Méthode de paiement</Label>
              <Input
                id="payment_method"
                v-model="form.payment_method"
                type="text"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.payment_method }"
              />
              <div v-if="form.errors.payment_method" class="text-red-500 text-sm mt-1">
                {{ form.errors.payment_method }}
              </div>
            </div>
          </div>
        </div>

        <!-- Lignes de facture -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Lignes de facture</h2>
            <Button type="button" variant="outline" @click="addItem">Ajouter une ligne</Button>
          </div>

          <div v-if="form.errors.items" class="text-red-500 text-sm mt-1 mb-4">
            {{ form.errors.items }}
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead>
                <tr class="border-b">
                  <th class="px-4 py-2 w-1/3">Description</th>
                  <th class="px-4 py-2 w-1/6">Quantité</th>
                  <th class="px-4 py-2 w-1/6">Prix unitaire</th>
                  <th class="px-4 py-2 w-1/6">TVA (%)</th>
                  <th class="px-4 py-2 w-1/6">Total</th>
                  <th class="px-4 py-2 w-16"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="form.items.length === 0">
                  <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    Aucune ligne de facture. Cliquez sur "Ajouter une ligne" pour commencer.
                  </td>
                </tr>
                <tr v-for="(item, index) in form.items" :key="index" class="border-b">
                  <td class="px-4 py-2">
                    <Input
                      v-model="item.description"
                      type="text"
                      class="w-full"
                      placeholder="Description"
                      required
                    />
                  </td>
                  <td class="px-4 py-2">
                    <Input
                      v-model="item.quantity"
                      type="number"
                      min="0.01"
                      step="0.01"
                      class="w-full"
                      placeholder="Quantité"
                      @input="calculateItemTotal(item)"
                      required
                    />
                  </td>
                  <td class="px-4 py-2">
                    <Input
                      v-model="item.unit_price"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-full"
                      placeholder="Prix unitaire"
                      @input="calculateItemTotal(item)"
                      required
                    />
                  </td>
                  <td class="px-4 py-2">
                    <Input
                      v-model="item.tax_rate"
                      type="number"
                      min="0"
                      step="0.1"
                      class="w-full"
                      placeholder="TVA"
                      @input="calculateItemTotal(item)"
                      required
                    />
                  </td>
                  <td class="px-4 py-2">
                    {{ formatAmount(item.total) }}
                  </td>
                  <td class="px-4 py-2">
                    <button
                      type="button"
                      @click="removeItem(index)"
                      class="text-red-600 hover:text-red-900"
                      title="Supprimer"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Totaux -->
          <div class="flex justify-end mt-6">
            <div class="w-1/3">
              <div class="grid grid-cols-2 gap-2">
                <div class="text-right">Sous-total :</div>
                <div class="text-right">{{ formatAmount(form.subtotal) }}</div>

                <div class="text-right">TVA :</div>
                <div class="text-right">{{ formatAmount(form.tax_amount) }}</div>

                <div class="text-right font-bold">Total :</div>
                <div class="text-right font-bold">{{ formatAmount(form.total) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 class="text-lg font-semibold mb-4">Notes</h2>
          <textarea
            v-model="form.notes"
            rows="4"
            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
            :class="{ 'border-red-500': form.errors.notes }"
            placeholder="Notes ou informations supplémentaires (optionnel)"
          ></textarea>
          <div v-if="form.errors.notes" class="text-red-500 text-sm mt-1">
            {{ form.errors.notes }}
          </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex justify-end space-x-3">
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
  </AppLayout>
</template>

<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  clients: Array,
  selectedClient: Object,
  invoiceNumber: String,
  defaultTaxRate: Number,
  today: String,
  dueDate: String,
  currency: String
});

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Factures', href: route('invoices.index') },
  { title: 'Créer', href: route('invoices.create') }
];

const form = useForm({
  client_id: props.selectedClient ? props.selectedClient.id : '',
  invoice_number: props.invoiceNumber,
  invoice_date: props.today,
  due_date: props.dueDate,
  items: [],
  subtotal: 0,
  tax_rate: props.defaultTaxRate,
  tax_amount: 0,
  total: 0,
  status: 'draft',
  notes: '',
  payment_method: '',
});

// Ajouter une ligne vide par défaut
if (form.items.length === 0) {
  addItem();
}

function addItem() {
  form.items.push({
    description: '',
    quantity: 1,
    unit_price: 0,
    tax_rate: props.defaultTaxRate,
    tax_amount: 0,
    subtotal: 0,
    total: 0
  });
}

function removeItem(index) {
  form.items.splice(index, 1);
  calculateTotals();
}

function calculateItemTotal(item) {
  // Convertir les valeurs en nombres
  const quantity = parseFloat(item.quantity) || 0;
  const unitPrice = parseFloat(item.unit_price) || 0;
  const taxRate = parseFloat(item.tax_rate) || 0;

  // Calculer les totaux
  item.subtotal = quantity * unitPrice;
  item.tax_amount = item.subtotal * (taxRate / 100);
  item.total = item.subtotal + item.tax_amount;

  // Recalculer les totaux de la facture
  calculateTotals();
}

function calculateTotals() {
  form.subtotal = form.items.reduce((sum, item) => sum + (parseFloat(item.subtotal) || 0), 0);
  form.tax_amount = form.items.reduce((sum, item) => sum + (parseFloat(item.tax_amount) || 0), 0);
  form.total = form.subtotal + form.tax_amount;
}

const formatAmount = (amount) => {
  if (amount === undefined || amount === null) return '-';
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: props.currency }).format(amount);
};

const cancel = () => {
  router.visit(route('invoices.index'));
};

const submit = () => {
  form.post(route('invoices.store'));
};
</script>
