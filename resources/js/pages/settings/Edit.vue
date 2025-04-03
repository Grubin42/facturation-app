<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Paramètres</h1>
      </div>

      <div v-if="$page.props.flash && $page.props.flash.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ $page.props.flash.success }}
      </div>

      <form @submit.prevent="submit" enctype="multipart/form-data">
        <!-- Informations de l'entreprise -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 class="text-lg font-semibold mb-4">Informations de l'entreprise</h2>

          <!-- Logo de l'entreprise -->
          <div class="mb-6">
            <Label for="logo">Logo de l'entreprise</Label>
            <div class="flex items-start space-x-4 mt-1">
              <div v-if="logoPreview || settings.logo_url" class="w-40 h-40 relative flex-shrink-0">
                <img
                  :src="logoPreview || settings.logo_url"
                  alt="Logo de l'entreprise"
                  class="object-contain w-full h-full border rounded-md p-2"
                />
                <button
                  @click.prevent="removeLogo"
                  type="button"
                  class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600"
                  aria-label="Supprimer le logo"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
              <div class="flex-grow">
                <div class="flex items-center justify-center w-full">
                  <label for="logo" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <svg class="w-8 h-8 mb-3 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                      </svg>
                      <p class="mb-1 text-sm text-gray-500">Cliquez ou glissez-déposez</p>
                      <p class="text-xs text-gray-500">PNG, JPG, GIF (Max. 2Mo)</p>
                    </div>
                    <input
                      id="logo"
                      type="file"
                      ref="logoInput"
                      @change="handleLogoUpload"
                      accept="image/*"
                      class="hidden"
                    />
                  </label>
                </div>
                <div v-if="form.errors.logo" class="text-red-500 text-sm mt-1">
                  {{ form.errors.logo }}
                </div>
              </div>
            </div>
          </div>

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
              <Label for="company_iban">IBAN</Label>
              <Input
                id="company_iban"
                v-model="form.company_iban"
                type="text"
                class="w-full mt-1"
                placeholder="CH00 0000 0000 0000 0000 0"
                :class="{ 'border-red-500': form.errors.company_iban }"
              />
              <div v-if="form.errors.company_iban" class="text-red-500 text-sm mt-1">
                {{ form.errors.company_iban }}
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
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  settings: Object
});

const breadcrumbs = [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Paramètres', href: route('settings.edit'), current: true }
];

const logoInput = ref(null);
const logoPreview = ref(null);

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
  company_iban: props.settings.company_iban || '',
  invoice_prefix: props.settings.invoice_prefix || 'INV',
  invoice_next_number: props.settings.invoice_next_number || 1,
  quote_prefix: props.settings.quote_prefix || 'QUO',
  quote_next_number: props.settings.quote_next_number || 1,
  payment_terms: props.settings.payment_terms || 30,
  default_tax_rate: props.settings.default_tax_rate || 20,
  currency: props.settings.currency || 'EUR',
  invoice_footer: props.settings.invoice_footer || '',
  quote_footer: props.settings.quote_footer || '',
  logo: null,
});

const handleLogoUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.logo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const removeLogo = () => {
  form.logo = null;
  logoPreview.value = null;
  if (logoInput.value) {
    logoInput.value.value = '';
  }
};

const submit = () => {
  form.post(route('settings.update'), {
    forceFormData: true,
    preserveScroll: true,
  });
};
</script>
