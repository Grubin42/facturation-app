<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { PlusCircle, Users } from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    recentInvoices: Array,
    overdueInvoices: Array,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const totalInvoices = computed(() => props.stats?.total_invoices || 0);
const paidInvoices = computed(() => props.stats?.paid_invoices || 0);
const overdueInvoices = computed(() => props.stats?.overdue_invoices || 0);
const totalRevenue = computed(() => props.stats?.total_revenue || 0);

const formatAmount = (amount) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(amount);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(date);
};

const getStatusClass = (status) => {
    const classMap = {
        'draft': 'bg-gray-100 text-gray-800',
        'sent': 'bg-blue-100 text-blue-800',
        'paid': 'bg-green-100 text-green-800',
        'overdue': 'bg-red-100 text-red-800',
        'cancelled': 'bg-gray-100 text-gray-800'
    };
    return classMap[status] || 'bg-gray-100 text-gray-800';
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

console.log('overdueInvoices reçu:', props.overdueInvoices);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-6">
            <!-- Actions rapides -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4">Actions rapides</h2>
                <div class="flex flex-wrap gap-4">
                    <Link :href="route('invoices.create')">
                        <Button class="flex items-center gap-2">
                            <PlusCircle class="h-4 w-4" />
                            Nouvelle facture
                        </Button>
                    </Link>
                    <Link :href="route('clients.create')">
                        <Button variant="outline" class="flex items-center gap-2">
                            <Users class="h-4 w-4" />
                            Nouveau client
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Total des factures</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ totalInvoices }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Factures payées</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ paidInvoices }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Factures en retard</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ overdueInvoices }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Chiffre d'affaires</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ formatAmount(totalRevenue) }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Factures récentes -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card class="col-span-1">
                    <CardHeader>
                        <CardTitle>Factures récentes</CardTitle>
                        <CardDescription>Les dernières factures créées</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table v-if="recentInvoices && recentInvoices.length > 0" class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="pb-2 text-left">N°</th>
                                        <th class="pb-2 text-left">Client</th>
                                        <th class="pb-2 text-left">Date</th>
                                        <th class="pb-2 text-left">Montant</th>
                                        <th class="pb-2 text-left">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="invoice in recentInvoices" :key="invoice.id" class="border-b">
                                        <td class="py-2">
                                            <Link :href="route('invoices.show', invoice.id)" class="text-primary hover:underline">
                                                {{ invoice.invoice_number }}
                                            </Link>
                                        </td>
                                        <td class="py-2">{{ invoice.client?.name || 'N/A' }}</td>
                                        <td class="py-2">{{ formatDate(invoice.invoice_date) }}</td>
                                        <td class="py-2">{{ formatAmount(invoice.total) }}</td>
                                        <td class="py-2">
                                            <span :class="`px-2 py-1 text-xs rounded-full ${getStatusClass(invoice.status)}`">
                                                {{ getStatusLabel(invoice.status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else class="text-center py-4 text-gray-500">
                                Aucune facture récente
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter v-if="recentInvoices && recentInvoices.length > 0">
                        <Link :href="route('invoices.index')" class="text-sm text-primary hover:underline">
                            Voir toutes les factures
                        </Link>
                    </CardFooter>
                </Card>

                <Card class="col-span-1">
                    <CardHeader>
                        <CardTitle>Factures en retard</CardTitle>
                        <CardDescription>Les factures qui nécessitent votre attention</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <pre v-if="false" class="text-xs">{{ JSON.stringify(props.overdueInvoices, null, 2) }}</pre>

                            <table v-if="props.overdueInvoices && props.overdueInvoices.length > 0" class="w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="pb-2 text-left">N°</th>
                                        <th class="pb-2 text-left">Client</th>
                                        <th class="pb-2 text-left">Échéance</th>
                                        <th class="pb-2 text-left">Montant</th>
                                        <th class="pb-2 text-left">Jours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="invoice in props.overdueInvoices" :key="invoice.id" class="border-b">
                                        <td class="py-2">
                                            <Link :href="route('invoices.show', invoice.id)" class="text-primary hover:underline">
                                                {{ invoice.invoice_number }}
                                            </Link>
                                        </td>
                                        <td class="py-2">{{ invoice.client?.name || 'N/A' }}</td>
                                        <td class="py-2">{{ formatDate(invoice.due_date) }}</td>
                                        <td class="py-2">{{ formatAmount(invoice.total) }}</td>
                                        <td class="py-2 text-red-600 font-semibold">{{ invoice.days_overdue }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else class="text-center py-4 text-gray-500">
                                Aucune facture en retard
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
