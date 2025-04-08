<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Users, FileText, Settings, ShieldCheck, FileCheck } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.role === 'admin');

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Clients',
        href: '/clients',
        icon: Users,
    },
    {
        title: 'Devis',
        href: '/quotes',
        icon: FileCheck,
    },
    {
        title: 'Factures',
        href: '/invoices',
        icon: FileText,
    },
    {
        title: 'Paramètres',
        href: '/settings/company',
        icon: Settings,
    },
];

// Ajout du lien Admin si l'utilisateur est un administrateur
if (isAdmin.value) {
    mainNavItems.push({
        title: 'Administration',
        href: '/admin',
        icon: ShieldCheck,
    });
}

// Tableau vide pour les liens de footer
const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
