<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Paramètres</h1>
      </div>

      <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ $page.props.flash.success }}
      </div>

      <form @submit.prevent="submit">
        <!-- Informations de l'entreprise -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 class="text-lg font-semibold mb-4">Informations de l'entreprise</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <Label for="company_name">Nom de l'entreprise</Label>
              <Input
                id="company_name"
                v-model="form.company_name"
                type="text"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.company_name }"
                required
              />
              <div v-if="form.errors.company_name" class="text-red-500 text-sm mt-1">
                {{ form.errors.company_name }}
              </div>
            </div>

            <div>
              <Label for="company_siret">IDE</Label>
              <Input
                id="company_siret"
                v-model="form.company_siret"
                type="text"
                placeholder="CHE-123.456.789"
                :class="{ 'border-red-500': form.errors.company_siret }"
              />
              <div v-if="form.errors.company_siret" class="text-red-500 text-sm mt-1">
                {{ form.errors.company_siret }}
              </div>
            </div>

            <div>
              <Label for="company_vat">Numéro de TVA</Label>
              <Input
                id="company_vat"
                v-model="form.company_vat"
                type="text"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.company_vat }"
              />
              <div v-if="form.errors.company_vat" class="text-red-500 text-sm mt-1">
                {{ form.errors.company_vat }}
              </div>
            </div>

            <div>
              <Label for="company_phone">Téléphone</Label>
              <Input
                id="company_phone"
                v-model="form.company_phone"
                type="text"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.company_phone }"
              />
              <div v-if="form.errors.company_phone" class="text-red-500 text-sm mt-1">
                {{ form.errors.company_phone }}
              </div>
            </div>

            <div>
              <Label for="company_email">Email</Label>
              <Input
                id="company_email"
                v-model="form.company_email"
                type="email"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.company_email }"
              />
              <div v-if="form.errors.company_email" class="text-red-500 text-sm mt-1">
                {{ form.errors.company_email }}
              </div>
            </div>

            <div>
              <Label for="company_website">Site web</Label>
              <Input
                id="company_website"
                v-model="form.company_website"
                type="url"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.company_website }"
              />
              <div v-if="form.errors.company_website" class="text-red-500 text-sm mt-1">
                {{ form.errors.company_website }}
              </div>
            </div>

            <div>
              <Label for="company_address">Adresse</Label>
              <Input
                id="company_address"
                v-model="form.company_address"
                type="text"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.company_address }"
              />
              <div v-if="form.errors.company_address" class="text-red-500 text-sm mt-1">
                {{ form.errors.company_address }}
              </div>
            </div>

            <div>
              <Label for="company_city">Ville</Label>
              <Input
                id="company_city"
                v-model="form.company_city"
                type="text"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.company_city }"
              />
              <div v-if="form.errors.company_city" class="text-red-500 text-sm mt-1">
                {{ form.errors.company_city }}
              </div>
            </div>

            <div>
              <Label for="company_postal_code">Code postal</Label>
              <Input
                id="company_postal_code"
                v-model="form.company_postal_code"
                type="text"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.company_postal_code }"
              />
              <div v-if="form.errors.company_postal_code" class="text-red-500 text-sm mt-1">
                {{ form.errors.company_postal_code }}
              </div>
            </div>

            <div>
              <Label for="company_country">Pays</Label>
              <Input
                id="company_country"
                v-model="form.company_country"
                type="text"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.company_country }"
              />
              <div v-if="form.errors.company_country" class="text-red-500 text-sm mt-1">
                {{ form.errors.company_country }}
              </div>
            </div>
          </div>
        </div>

        <!-- Paramètres de facturation -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 class="text-lg font-semibold mb-4">Paramètres de facturation</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <Label for="invoice_prefix">Préfixe des factures</Label>
              <Input
                id="invoice_prefix"
                v-model="form.invoice_prefix"
                type="text"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.invoice_prefix }"
                required
              />
              <div v-if="form.errors.invoice_prefix" class="text-red-500 text-sm mt-1">
                {{ form.errors.invoice_prefix }}
              </div>
            </div>

            <div>
              <Label for="invoice_next_number">Prochain numéro de facture</Label>
              <Input
                id="invoice_next_number"
                v-model="form.invoice_next_number"
                type="number"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.invoice_next_number }"
                min="1"
                required
              />
              <div v-if="form.errors.invoice_next_number" class="text-red-500 text-sm mt-1">
                {{ form.errors.invoice_next_number }}
              </div>
            </div>

            <div>
              <Label for="quote_prefix">Préfixe des devis</Label>
              <Input
                id="quote_prefix"
                v-model="form.quote_prefix"
                type="text"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.quote_prefix }"
                required
              />
              <div v-if="form.errors.quote_prefix" class="text-red-500 text-sm mt-1">
                {{ form.errors.quote_prefix }}
              </div>
            </div>

            <div>
              <Label for="quote_next_number">Prochain numéro de devis</Label>
              <Input
                id="quote_next_number"
                v-model="form.quote_next_number"
                type="number"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.quote_next_number }"
                min="1"
                required
              />
              <div v-if="form.errors.quote_next_number" class="text-red-500 text-sm mt-1">
                {{ form.errors.quote_next_number }}
              </div>
            </div>

            <div>
              <Label for="default_tax_rate">Taux de TVA par défaut (%)</Label>
              <Input
                id="default_tax_rate"
                v-model="form.default_tax_rate"
                type="number"
                step="0.01"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.default_tax_rate }"
                min="0"
                required
              />
              <div v-if="form.errors.default_tax_rate" class="text-red-500 text-sm mt-1">
                {{ form.errors.default_tax_rate }}
              </div>
            </div>

            <div>
              <Label for="payment_terms">Délai de paiement (jours)</Label>
              <Input
                id="payment_terms"
                v-model="form.payment_terms"
                type="number"
                class="w-full mt-1"
                :class="{ 'border-red-500': form.errors.payment_terms }"
                min="0"
                required
              />
              <div v-if="form.errors.payment_terms" class="text-red-500 text-sm mt-1">
                {{ form.errors.payment_terms }}
              </div>
            </div>

            <div>
              <Label for="currency">Devise</Label>
              <select
                id="currency"
                v-model="form.currency"
                class="w-full mt-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
                :class="{ 'border-red-500': form.errors.currency }"
                required
              >
                <option value="EUR">Euro (EUR)</option>
                <option value="USD">Dollar US (USD)</option>
                <option value="GBP">Livre Sterling (GBP)</option>
                <option value="CHF">Franc Suisse (CHF)</option>
                <option value="CAD">Dollar Canadien (CAD)</option>
              </select>
              <div v-if="form.errors.currency" class="text-red-500 text-sm mt-1">
                {{ form.errors.currency }}
              </div>
            </div>
          </div>
        </div>

        <!-- Textes personnalisés -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 class="text-lg font-semibold mb-4">Textes personnalisés</h2>
          <div class="space-y-4">
            <div>
              <Label for="invoice_footer">Pied de page des factures</Label>
              <textarea
                id="invoice_footer"
                v-model="form.invoice_footer"
                rows="3"
                class="w-full mt-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
                :class="{ 'border-red-500': form.errors.invoice_footer }"
              ></textarea>
              <div v-if="form.errors.invoice_footer" class="text-red-500 text-sm mt-1">
                {{ form.errors.invoice_footer }}
              </div>
            </div>

            <div>
              <Label for="quote_footer">Pied de page des devis</Label>
              <textarea
                id="quote_footer"
                v-model="form.quote_footer"
                rows="3"
                class="w-full mt-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/50"
                :class="{ 'border-red-500': form.errors.quote_footer }"
              ></textarea>
              <div v-if="form.errors.quote_footer" class="text-red-500 text-sm mt-1">
                {{ form.errors.quote_footer }}
              </div>
            </div>
          </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex justify-end space-x-3">
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
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  settings: Object
});

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Paramètres', href: route('settings.edit'), current: true }
];

const form = useForm({
  company_name: props.settings.company_name || '',
  company_address: props.settings.company_address || '',
  company_city: props.settings.company_city || '',
  company_postal_code: props.settings.company_postal_code || '',
  company_country: props.settings.company_country || '',
  company_phone: props.settings.company_phone || '',
  company_email: props.settings.company_email || '',
  company_website: props.settings.company_website || '',
  company_siret: props.settings.company_siret || '',
  company_vat: props.settings.company_vat || '',
  invoice_prefix: props.settings.invoice_prefix || 'INV',
  invoice_next_number: props.settings.invoice_next_number || 1,
  quote_prefix: props.settings.quote_prefix || 'QUO',
  quote_next_number: props.settings.quote_next_number || 1,
  payment_terms: props.settings.payment_terms || 30,
  default_tax_rate: props.settings.default_tax_rate || 20,
  currency: props.settings.currency || 'EUR',
  invoice_footer: props.settings.invoice_footer || '',
  quote_footer: props.settings.quote_footer || '',
});

const submit = () => {
  form.patch(route('settings.update'));
};
</script>
