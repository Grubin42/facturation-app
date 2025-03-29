<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import {
    User,
    Home,
    BarChart,
    FileText,
    Users,
    Receipt,
    Settings
} from 'lucide-vue-next'

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const mainNavItems = [
    {
        title: 'Factures',
        href: '/invoices',
        icon: FileText,
    },
    {
        title: 'Paramètres',
        href: '/settings',
        icon: Settings,
    },
]
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
