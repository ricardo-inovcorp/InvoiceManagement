<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Contas Bancárias
            </h2>
        </template>

        <Head title="Contas Bancárias" />

        <div class="w-full py-4">
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-bold">Contas Bancárias</h1>
                    <Link :href="route('bank-accounts.create')">
                        <Button>
                            <PlusCircle class="h-4 w-4 mr-2" />
                            Nova Conta
                        </Button>
                    </Link>
                </div>
                <p class="text-muted-foreground">
                    Faça a gestão das suas contas bancárias e acompanhe os saldos
                </p>
            </div>

            <Card v-if="bankAccounts.length === 0" class="mb-6">
                <CardContent class="pt-6 text-center">
                    <div class="flex flex-col items-center justify-center py-8">
                        <CreditCard class="h-12 w-12 text-muted-foreground mb-4" />
                        <h3 class="font-semibold text-lg mb-2">Nenhuma conta bancária</h3>
                        <p class="text-muted-foreground mb-4 max-w-md text-center">
                            Adicione as suas contas bancárias para começar a gerir as suas finanças e associar pagamentos às facturas.
                        </p>
                        <Link :href="route('bank-accounts.create')">
                            <Button>
                                <PlusCircle class="h-4 w-4 mr-2" />
                                Nova Conta
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Link 
                    v-for="account in bankAccounts" 
                    :key="account.id" 
                    :href="route('bank-accounts.show', account.id)"
                    class="block"
                >
                    <Card :class="{
                        'border-primary border-2': account.is_active, 
                        'opacity-70 border-dashed border-gray-300': !account.is_active
                    }">
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between">
                                <CardTitle class="truncate">{{ account.name }}</CardTitle>
                                <Badge v-if="!account.is_active" variant="outline">Inactiva</Badge>
                                <Badge v-else variant="default">Activa</Badge>
                            </div>
                            <CardDescription>{{ account.bank_name }}</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-col space-y-1">
                                <div class="text-sm font-medium text-muted-foreground">IBAN</div>
                                <div class="font-mono">{{ formatIBAN(account.iban) }}</div>
                            </div>
                            <div class="flex flex-col space-y-1 mt-3">
                                <div class="text-sm font-medium text-muted-foreground">Estado</div>
                                <div class="flex items-center">
                                    <div 
                                        class="size-2 rounded-full mr-2" 
                                        :class="account.is_active ? 'bg-green-500' : 'bg-gray-400'"
                                    ></div>
                                    <span :class="account.is_active ? 'text-green-600' : 'text-gray-500'">
                                        {{ account.is_active ? 'Conta Activa' : 'Conta Inactiva' }}
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="flex items-center justify-between">
                            <div class="text-sm text-muted-foreground">Saldo</div>
                            <div class="text-xl font-bold">{{ formatCurrency(account.current_balance) }}</div>
                        </CardFooter>
                    </Card>
                </Link>
            </div>

            <div class="mt-6" v-if="bankAccounts.length > 0">
                <Card>
                    <CardHeader>
                        <CardTitle>Resumo Financeiro</CardTitle>
                        <CardDescription>
                            Visão geral dos saldos em todas as contas ativas
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">
                                    Total em contas activas
                                </div>
                                <div class="text-2xl font-bold">
                                    {{ formatCurrency(totalBalance) }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">
                                    Contas activas
                                </div>
                                <div class="text-2xl font-bold">
                                    {{ activeAccountsCount }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">
                                    Total de contas
                                </div>
                                <div class="text-2xl font-bold">
                                    {{ bankAccounts.length }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
            
            <!-- Painel de Debug - visível apenas em desenvolvimento -->
            <!-- <div v-if="debug" class="debug-panel mt-6">
                <h3 class="font-bold mb-2">Debug Info:</h3>
                <div>
                    <strong>Contas:</strong> {{ debug.count }}
                </div>
                <div>
                    <strong>Rota:</strong> {{ debug.route }}
                </div>
                <div>
                    <strong>Componente:</strong> {{ debug.component }}
                </div>
                <div>
                    <strong>Timestamp:</strong> {{ debug.time }}
                </div>
            </div> -->
        </div>
    </AppLayout>
</template>

<style>
.debug-panel {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
    font-family: monospace;
    font-size: 0.875rem;
    line-height: 1.25rem;
}
</style>

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