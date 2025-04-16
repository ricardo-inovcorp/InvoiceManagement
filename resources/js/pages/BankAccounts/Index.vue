<template>
    <Head title="Contas Bancárias" />

    <AppLayout :user="auth.user">
        <div class="py-12">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Contas Bancárias</h1>
                    <div class="flex space-x-4">
                        <Link :href="route('bank-accounts.create')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            <PlusCircle class="h-4 w-4 mr-2 inline-block" />
                            Nova Conta
                        </Link>
                    </div>
                </div>
                <p class="text-muted-foreground dark:text-gray-400 mb-6">
                    Faça a gestão das suas contas bancárias e acompanhe os saldos
                </p>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6" v-if="bankAccounts.length === 0">
                    <div class="flex flex-col items-center justify-center py-8">
                        <CreditCard class="h-12 w-12 text-muted-foreground mb-4 dark:text-gray-400" />
                        <h3 class="font-semibold text-lg mb-2 dark:text-white">Nenhuma conta bancária</h3>
                        <p class="text-muted-foreground mb-4 max-w-md text-center dark:text-gray-400">
                            Adicione as suas contas bancárias para começar a gerir as suas finanças e associar pagamentos às facturas.
                        </p>
                        <Link :href="route('bank-accounts.create')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            <PlusCircle class="h-4 w-4 mr-2 inline-block" />
                            Nova Conta
                        </Link>
                    </div>
                </div>

                <div v-else>
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8">
                        <Link 
                            v-for="account in bankAccounts" 
                            :key="account.id" 
                            :href="route('bank-accounts.show', account.id)"
                            class="block"
                        >
                            <Card :class="{
                                'border-primary border-2': account.is_active, 
                                'opacity-70 border-dashed border-gray-300 dark:border-gray-600': !account.is_active
                            }">
                                <CardHeader class="pb-2">
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="truncate">{{ account.name }}</CardTitle>
                                        <span
                                            :class="[
                                                'px-2 py-1 text-xs rounded-full',
                                                account.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                            ]"
                                        >
                                            {{ account.is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </div>
                                    <CardDescription>{{ account.bank_name }}</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div class="flex flex-col space-y-1">
                                        <div class="text-sm font-medium text-muted-foreground dark:text-gray-400">IBAN</div>
                                        <div class="font-mono dark:text-gray-300">{{ formatIBAN(account.iban) }}</div>
                                    </div>
                                    <div class="flex flex-col space-y-1 mt-3">
                                        <div class="text-sm font-medium text-muted-foreground dark:text-gray-400">Estado</div>
                                        <div class="flex items-center">
                                            <div 
                                                class="size-2 rounded-full mr-2" 
                                                :class="account.is_active ? 'bg-green-500' : 'bg-gray-400'"
                                            ></div>
                                            <span :class="account.is_active ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                                                {{ account.is_active ? 'Conta Activa' : 'Conta Inactiva' }}
                                            </span>
                                        </div>
                                    </div>
                                </CardContent>
                                <CardFooter class="flex items-center justify-between">
                                    <div class="text-sm text-muted-foreground dark:text-gray-400">Saldo</div>
                                    <div class="text-xl font-bold dark:text-white">{{ formatCurrency(account.current_balance) }}</div>
                                </CardFooter>
                            </Card>
                        </Link>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="mb-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Resumo Financeiro</h2>
                            <p class="text-muted-foreground dark:text-gray-400">
                                Visão geral dos saldos em todas as contas ativas
                            </p>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md">
                                <div class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">
                                    Total em contas activas
                                </div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(totalBalance) }}
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md">
                                <div class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">
                                    Contas activas
                                </div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ activeAccountsCount }}
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-md">
                                <div class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">
                                    Total de contas
                                </div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ bankAccounts.length }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { PlusCircle, CreditCard } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Card, 
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle
} from '@/components/ui/card';

const props = defineProps({
    auth: Object,
    bankAccounts: {
        type: Array,
        default: () => []
    },
    debug: Object
});

onMounted(() => {
    console.log('BankAccounts/Index.vue mounted');
    console.log('bankAccounts:', props.bankAccounts);
    console.log('debug:', props.debug);
});

const activeAccountsCount = computed(() => {
    if (!props.bankAccounts || !Array.isArray(props.bankAccounts)) return 0;
    return props.bankAccounts.filter(account => account.is_active).length;
});

const totalBalance = computed(() => {
    if (!props.bankAccounts || !Array.isArray(props.bankAccounts)) return 0;
    return props.bankAccounts
        .filter(account => account.is_active)
        .reduce((sum, account) => sum + parseFloat(account.current_balance || 0), 0);
});

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR'
    }).format(value || 0);
}

function formatIBAN(iban) {
    if (!iban) return '';
    // Format IBAN with spaces every 4 characters for better readability
    return iban.replace(/(.{4})/g, '$1 ').trim();
}
</script> 