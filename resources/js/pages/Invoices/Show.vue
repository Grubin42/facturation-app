<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Facture {{ invoice.invoice_number }}</h1>
        <div class="flex space-x-2">
          <Link :href="route('invoices.index')" class="text-gray-600 hover:text-gray-900">
            Retour à la liste
          </Link>
          <span class="text-gray-300">|</span>
          <Link :href="route('invoices.edit', invoice.id)" class="text-gray-600 hover:text-gray-900">
            Modifier
          </Link>
          <span class="text-gray-300">|</span>
          <Link :href="route('invoices.pdf', invoice.id)" class="text-gray-600 hover:text-gray-900" target="_blank">
            PDF
          </Link>
        </div>
      </div>

      <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ $page.props.flash.success }}
      </div>

      <!-- Statut et actions -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-4">
            <span class="text-lg font-semibold">Statut :</span>
            <span :class="getStatusClass(invoice.status)">
              {{ getStatusLabel(invoice.status) }}
            </span>
          </div>
          <div v-if="invoice.status !== 'paid' && invoice.status !== 'cancelled'" class="flex space-x-2">
            <Button v-if="invoice.status === 'draft'" @click="sendInvoice">
              Envoyer
            </Button>
            <Button @click="openPaymentModal">
              Marquer comme payée
            </Button>
            <Button variant="destructive" @click="openCancellationModal">
              Annuler la facture
            </Button>
          </div>
        </div>
      </div>

      <!-- Informations de la facture -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Client -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold mb-4">Client</h2>
          <div>
            <p class="font-semibold">{{ invoice.client.name }}</p>
            <p>{{ invoice.client.email }}</p>
            <p>{{ invoice.client.phone }}</p>
            <p>{{ invoice.client.address }}</p>
            <p>{{ invoice.client.city }}, {{ invoice.client.postal_code }}</p>
            <p>{{ invoice.client.country }}</p>
          </div>
        </div>

        <!-- Détails de la facture -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-lg font-semibold mb-4">Détails de la facture</h2>
          <div class="grid grid-cols-2 gap-2">
            <p class="font-semibold">Numéro :</p>
            <p>{{ invoice.invoice_number }}</p>

            <p class="font-semibold">Date de facture :</p>
            <p>{{ formatDate(invoice.invoice_date) }}</p>

            <p class="font-semibold">Date d'échéance :</p>
            <p>{{ formatDate(invoice.due_date) }}</p>

            <template v-if="invoice.status === 'paid'">
              <p class="font-semibold">Date de paiement :</p>
              <p>{{ formatDate(invoice.paid_at) }}</p>

              <p class="font-semibold">Méthode de paiement :</p>
              <p>{{ invoice.payment_method || 'Non spécifiée' }}</p>
            </template>

            <template v-if="invoice.status === 'cancelled'">
              <p class="font-semibold">Date d'annulation :</p>
              <p>{{ formatDate(invoice.cancelled_at) }}</p>

              <p class="font-semibold">Raison de l'annulation :</p>
              <p>{{ invoice.cancellation_reason || 'Non spécifiée' }}</p>
            </template>
          </div>
        </div>
      </div>

      <!-- Lignes de facture -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Lignes de facture</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead>
              <tr class="border-b">
                <th class="px-4 py-2 w-1/3">Description</th>
                <th class="px-4 py-2 w-1/6 text-right">Quantité</th>
                <th class="px-4 py-2 w-1/6 text-right">Prix unitaire</th>
                <th class="px-4 py-2 w-1/6 text-right">TVA (%)</th>
                <th class="px-4 py-2 w-1/6 text-right">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in invoice.items" :key="index" class="border-b">
                <td class="px-4 py-2">{{ item.description }}</td>
                <td class="px-4 py-2 text-right">{{ item.quantity }}</td>
                <td class="px-4 py-2 text-right">{{ formatAmount(item.unit_price) }}</td>
                <td class="px-4 py-2 text-right">{{ item.tax_rate }} %</td>
                <td class="px-4 py-2 text-right">{{ formatAmount(item.total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totaux -->
        <div class="flex justify-end mt-6">
          <div class="w-1/3">
            <div class="grid grid-cols-2 gap-2">
              <div class="text-right">Sous-total :</div>
              <div class="text-right">{{ formatAmount(invoice.subtotal) }}</div>

              <div class="text-right">TVA :</div>
              <div class="text-right">{{ formatAmount(invoice.tax_amount) }}</div>

              <div class="text-right font-bold">Total :</div>
              <div class="text-right font-bold">{{ formatAmount(invoice.total) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div v-if="invoice.notes" class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Notes</h2>
        <p class="whitespace-pre-line">{{ invoice.notes }}</p>
      </div>

      <!-- Modal pour marquer comme payée -->
      <Dialog :open="paymentModal.isOpen" @update:open="paymentModal.isOpen = $event">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Marquer la facture comme payée</DialogTitle>
            <DialogDescription>
              Veuillez indiquer la méthode de paiement utilisée.
            </DialogDescription>
          </DialogHeader>
          <form @submit.prevent="markAsPaid">
            <div class="grid gap-4 py-4">
              <div class="grid gap-2">
                <Label for="payment_method">Méthode de paiement</Label>
                <Input id="payment_method" v-model="paymentModal.paymentMethod" placeholder="ex: Carte de crédit, Virement bancaire, etc." />
              </div>
            </div>
            <div class="flex justify-end space-x-2">
              <Button variant="outline" type="button" @click="paymentModal.isOpen = false">Annuler</Button>
              <Button type="submit">Confirmer</Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>

      <!-- Modal pour annuler la facture -->
      <Dialog :open="cancellationModal.isOpen" @update:open="cancellationModal.isOpen = $event">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Annuler la facture</DialogTitle>
            <DialogDescription>
              Êtes-vous sûr de vouloir annuler cette facture ? Cette action ne peut pas être annulée.
            </DialogDescription>
          </DialogHeader>
          <form @submit.prevent="cancelInvoice">
            <div class="grid gap-4 py-4">
              <div class="grid gap-2">
                <Label for="cancellation_reason">Raison de l'annulation (optionnel)</Label>
                <Input id="cancellation_reason" v-model="cancellationModal.reason" placeholder="Indiquez la raison de l'annulation" />
              </div>
            </div>
            <div class="flex justify-end space-x-2">
              <Button variant="outline" type="button" @click="cancellationModal.isOpen = false">Annuler</Button>
              <Button variant="destructive" type="submit">Confirmer l'annulation</Button>
            </div>
          </form>
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
  invoice: Object,
  currency: String
});

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Factures', href: route('invoices.index') },
  { title: props.invoice.invoice_number, href: route('invoices.show', props.invoice.id), current: true }
];

const paymentModal = ref({
  isOpen: false,
  paymentMethod: ''
});

const cancellationModal = ref({
  isOpen: false,
  reason: ''
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

const openPaymentModal = () => {
  paymentModal.value.isOpen = true;
  paymentModal.value.paymentMethod = '';
};

const openCancellationModal = () => {
  cancellationModal.value.isOpen = true;
  cancellationModal.value.reason = '';
};

const markAsPaid = () => {
  router.post(route('invoices.mark-as-paid', props.invoice.id), {
    payment_method: paymentModal.value.paymentMethod
  }, {
    onSuccess: () => {
      paymentModal.value.isOpen = false;
    }
  });
};

const cancelInvoice = () => {
  router.post(route('invoices.mark-as-cancelled', props.invoice.id), {
    cancellation_reason: cancellationModal.value.reason
  }, {
    onSuccess: () => {
      cancellationModal.value.isOpen = false;
    }
  });
};

const sendInvoice = () => {
  // Pour l'instant, on va simplement mettre à jour le statut à "sent"
  router.patch(route('invoices.update', props.invoice.id), {
    ...props.invoice,
    status: 'sent'
  });
};
</script>
