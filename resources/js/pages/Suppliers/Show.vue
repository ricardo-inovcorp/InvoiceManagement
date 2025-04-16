<template>
    <Head :title="`Fornecedor - ${supplier.company_name || supplier.name}`" />

    <AppLayout :user="auth.user">
        <div class="py-12">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ supplier.company_name || supplier.name }}</h1>
                    <div class="flex space-x-2">
                        <Link :href="route('suppliers.edit', supplier.id)" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            Editar
                        </Link>
                        <Link :href="route('suppliers.index')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            Voltar
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Informações Gerais -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2">
                        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Informações Gerais</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Empresa</h4>
                                <p class="dark:text-white">{{ supplier.company_name || supplier.name }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">NIF</h4>
                                <p class="dark:text-white">{{ supplier.document }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">E-mail</h4>
                                <p class="dark:text-white">{{ supplier.email }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Telefone</h4>
                                <p class="dark:text-white">{{ supplier.phone }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Status</h4>
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs rounded-full',
                                        supplier.active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                    ]"
                                >
                                    {{ supplier.active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                            <div v-if="supplier.sector">
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Setor de Atividade</h4>
                                <p class="dark:text-white">{{ supplier.sector ? supplier.sector.name : 'Não definido' }}</p>
                            </div>
                            <div v-if="supplier.organizationType">
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Tipo de Organização</h4>
                                <p class="dark:text-white">{{ supplier.organizationType ? supplier.organizationType.name : 'Não definido' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Informações de Endereço -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Informações de Morada</h2>
                        <div class="space-y-2">
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Morada</h4>
                                <p class="dark:text-white">{{ supplier.address }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Localidade</h4>
                                <p class="dark:text-white">{{ supplier.city }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Concelho</h4>
                                <p class="dark:text-white">{{ supplier.county && supplier.county.name ? supplier.county.name : 'Não definido' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Distrito</h4>
                                <p class="dark:text-white">{{ supplier.district && supplier.district.name ? supplier.district.name : 'Não definido' }}</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Código Postal</h4>
                                <p class="dark:text-white">{{ supplier.zip_code }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Histórico de Faturas -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Faturas deste Fornecedor</h2>
                        <Link :href="route('invoices.create', { supplier_id: supplier.id })" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            Nova Fatura
                        </Link>
                    </div>
                    
                    <div v-if="supplier.invoices && supplier.invoices.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Número</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data de Emissão</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data de Vencimento</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="invoice in supplier.invoices" :key="invoice.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-white">{{ invoice.invoice_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-white">{{ formatDate(invoice.issue_date) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-white">{{ formatDate(invoice.due_date) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-white">{{ formatCurrency(invoice.total_amount) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span
                                                :class="[
                                                    'px-2 py-1 text-xs rounded-full',
                                                    getStatusVariant(invoice.status) === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                    getStatusVariant(invoice.status) === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                                    getStatusVariant(invoice.status) === 'destructive' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                                    getStatusVariant(invoice.status) === 'secondary' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                                ]"
                                            >
                                                {{ getStatusLabel(invoice.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('invoices.show', invoice.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                Ver
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else class="text-center py-4 text-muted-foreground dark:text-gray-400">
                        Nenhuma fatura encontrada para este fornecedor.
                    </div>
                </div>

                <!-- Notas -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6" v-if="supplier.notes">
                    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Notas</h2>
                    <p class="whitespace-pre-wrap dark:text-white">{{ supplier.notes }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';

const props = defineProps({
    auth: Object,
    supplier: {
        type: Object,
        required: true,
    },
});

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('pt-PT');
}

function formatCurrency(value) {
    if (!value) return '€ 0,00';
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
    }).format(value);
}

function getStatusVariant(status) {
    switch (status) {
        case 'paid': return 'success';
        case 'pending': return 'warning';
        case 'overdue': return 'destructive';
        case 'cancelled': return 'secondary';
        default: return 'default';
    }
}

function getStatusLabel(status) {
    switch (status) {
        case 'paid': return 'Pago';
        case 'pending': return 'Pendente';
        case 'overdue': return 'Atrasado';
        case 'cancelled': return 'Cancelado';
        default: return status;
    }
}
</script> 