<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

interface User {
  id: number;
  name: string;
  email: string;
}

interface PageProps {
  auth: {
    user: User | null;
  };
  [key: string]: any;
}

const page = usePage<PageProps>();
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] lg:justify-center lg:p-8">
        <header class="fixed top-0 right-0 p-4 z-10">
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="page.props.auth.user"
                    :href="route('dashboard')"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="route('register')"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <main class="flex flex-col items-center w-full max-w-6xl mt-10">
            <div class="text-center mb-12">
                <h1 class="text-5xl font-bold mb-4">Application de Facturation</h1>
                <p class="text-lg text-[#1b1b1880] dark:text-[#EDEDEC80] max-w-2xl mx-auto">
                    Gérez facilement vos factures, clients et paiements avec notre solution intuitive.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full mb-16">
                <Card>
                    <CardHeader>
                        <CardTitle>Facturation Simplifiée</CardTitle>
                        <CardDescription>Créez des factures professionnelles en quelques clics</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-32 flex items-center justify-center text-4xl text-[#1b1b1840] dark:text-[#EDEDEC40]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Gestion des Clients</CardTitle>
                        <CardDescription>Centralisez vos données client dans un seul endroit</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-32 flex items-center justify-center text-4xl text-[#1b1b1840] dark:text-[#EDEDEC40]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Suivi des Paiements</CardTitle>
                        <CardDescription>Surveillez vos recettes et relancez automatiquement</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-32 flex items-center justify-center text-4xl text-[#1b1b1840] dark:text-[#EDEDEC40]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-line-chart"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="flex flex-col items-center gap-6 mb-16">
                <h2 class="text-3xl font-semibold text-center">Prêt à simplifier votre facturation ?</h2>
                <div class="flex gap-4">
                    <Button v-if="!page.props.auth.user" class="px-8" asChild>
                        <Link :href="route('register')">Commencer gratuitement</Link>
                    </Button>
                    <Button v-else class="px-8" asChild>
                        <Link :href="route('dashboard')">Accéder à mon tableau de bord</Link>
                    </Button>
                </div>
            </div>
        </main>

        <footer class="w-full max-w-6xl mt-12 pt-8 border-t border-[#19140035] dark:border-[#3E3E3A] text-center text-sm text-[#1b1b1880] dark:text-[#EDEDEC80]">
            <p>© {{ new Date().getFullYear() }} Application de Facturation. Tous droits réservés.</p>
        </footer>
    </div>
</template>
