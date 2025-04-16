<template>
        <Head title="Faturas" />

    <AppLayout :user="auth.user">
        <div class="py-12">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Faturas</h1>
                    <div class="flex space-x-4">
                        <Link :href="route('invoices.create')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            Nova Fatura
                    </Link>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Filtros -->
                        <div class="mb-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-md">
                            <h3 class="text-lg font-medium mb-3">Filtros</h3>
                            <form @submit.prevent="search">
                                <div class="flex flex-col space-y-4">
                                        <div class="flex space-x-2">
                                        <div class="md:w-1/2 lg:w-1/3">
                                            <Input 
                                                type="text" 
                                                placeholder="Pesquisar por fornecedor ou número da fatura..." 
                                                v-model="form.search"
                                                class="w-full"
                                            />
                                        </div>
                                        <Button type="submit" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                                            Aplicar Filtros
                                        </Button>
                                        <Button 
                                            type="button" 
                                            variant="outline" 
                                            @click="resetSearch"
                                            v-if="hasFilters"
                                            class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600"
                                        >
                                            Limpar
                                        </Button>
                                        <Button 
                                            type="button" 
                                            variant="outline" 
                                            @click="showFilters = !showFilters"
                                            class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600"
                                        >
                                            {{ showFilters ? 'Esconder Filtros' : 'Filtros Avançados' }}
                                        </Button>
                                    </div>
                                    
                                    <!-- Filtros avançados -->
                                    <div v-if="showFilters" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                        <div>
                                            <Label for="due_date_start">Vencimento - Início</Label>
                                            <Input 
                                                id="due_date_start" 
                                                type="date" 
                                                v-model="form.due_date_start"
                                                class="w-full"
                                            />
                                        </div>
                                        
                                        <div>
                                            <Label for="due_date_end">Vencimento - Fim</Label>
                                            <Input 
                                                id="due_date_end" 
                                                type="date" 
                                                v-model="form.due_date_end"
                                                class="w-full"
                                            />
                                        </div>
                                    
                                        <div>
                                            <Label for="issue_date_start">Emissão - Início</Label>
                                            <Input 
                                                id="issue_date_start" 
                                                type="date" 
                                                v-model="form.issue_date_start"
                                                class="w-full"
                                            />
                                        </div>
                                        
                                        <div>
                                            <Label for="issue_date_end">Emissão - Fim</Label>
                                            <Input 
                                                id="issue_date_end" 
                                                type="date" 
                                                v-model="form.issue_date_end"
                                                class="w-full"
                                            />
                                        </div>
                                    
                                        <div>
                                            <Label for="status">Status do Pagamento</Label>
                                            <select
                                                id="status"
                                                v-model="form.status"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-900"
                                            >
                                                <option value="">Todos</option>
                                                <option value="pending">Pendente</option>
                                                <option value="paid">Pago</option>
                                                <option value="overdue">Atrasado</option>
                                                <option value="cancelled">Cancelado</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <Label for="validation_status">Status de Validação</Label>
                                            <select
                                                id="validation_status"
                                                v-model="form.validation_status"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-900"
                                            >
                                                <option value="">Todos</option>
                                                <option value="pending">Pendente de Validação</option>
                                                <option value="validated">Validado</option>
                                                <option value="verified">Verificado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Lista de faturas -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Fatura
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Fornecedor
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Emissão
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Vencimento
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Valor Total
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status Pagamento
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status Validação
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="invoice in invoices.data" :key="invoice.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            {{ invoice.invoice_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ invoice.supplier ? invoice.supplier.company_name : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ formatDate(invoice.issue_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ formatDate(invoice.due_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ formatCurrency(invoice.total_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span
                                                :class="[
                                                    'px-2 py-1 text-xs rounded-full',
                                                    getBadgeVariant(invoice.status) === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                    getBadgeVariant(invoice.status) === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                                    getBadgeVariant(invoice.status) === 'destructive' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                                ]"
                                            >
                                                {{ getStatusText(invoice.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span
                                                :class="[
                                                    'px-2 py-1 text-xs rounded-full',
                                                    getValidationBadgeVariant(invoice.validation_status) === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                    getValidationBadgeVariant(invoice.validation_status) === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                                    getValidationBadgeVariant(invoice.validation_status) === 'secondary' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                                ]"
                                            >
                                                {{ getValidationStatusText(invoice.validation_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Link
                                                    :href="route('invoices.show', invoice.id)"
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    Ver
                                                </Link>
                                                <Link
                                                    :href="route('invoices.edit', invoice.id)"
                                                    class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                                >
                                                    Editar
                                            </Link>
                                        </div>
                                        </td>
                                    </tr>
                                    <tr v-if="invoices.data.length === 0">
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Nenhuma fatura encontrada.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <div class="mt-6 flex justify-end">
                            <Pagination :links="invoices.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    auth: Object,
    invoices: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            validation_status: '',
            due_date_start: '',
            due_date_end: '',
            issue_date_start: '',
            issue_date_end: ''
        })
    }
});

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
    validation_status: props.filters.validation_status || '',
    due_date_start: props.filters.due_date_start || '',
    due_date_end: props.filters.due_date_end || '',
    issue_date_start: props.filters.issue_date_start || '',
    issue_date_end: props.filters.issue_date_end || ''
});

const showFilters = ref(false);

const hasFilters = computed(() => {
    return form.search || form.status || form.validation_status || form.due_date_start || form.due_date_end || 
           form.issue_date_start || form.issue_date_end;
});

function search() {
    router.get(route('invoices.index'), {
        search: form.search,
        status: form.status,
        validation_status: form.validation_status,
        due_date_start: form.due_date_start,
        due_date_end: form.due_date_end,
        issue_date_start: form.issue_date_start,
        issue_date_end: form.issue_date_end
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetSearch() {
    form.search = '';
    form.status = '';
    form.validation_status = '';
    form.due_date_start = '';
    form.due_date_end = '';
    form.issue_date_start = '';
    form.issue_date_end = '';
    router.get(route('invoices.index'), {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('pt-PT');
}

function formatCurrency(value) {
    if (!value) return '€0,00';
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
    }).format(value);
}

function getBadgeVariant(status) {
    switch (status) {
        case 'paid':
            return 'success';
        case 'pending':
            return 'warning';
        case 'overdue':
            return 'destructive';
        case 'cancelled':
            return 'secondary';
        default:
            return 'default';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'paid':
            return 'Pago';
        case 'pending':
            return 'Pendente';
        case 'overdue':
            return 'Atrasado';
        case 'cancelled':
            return 'Cancelado';
        default:
            return status;
    }
}

function getValidationBadgeVariant(status) {
    switch (status) {
        case 'pending':
            return 'warning';
        case 'validated':
            return 'secondary';
        case 'verified':
            return 'success';
        default:
            return 'default';
    }
}

function getValidationStatusText(status) {
    switch (status) {
        case 'pending':
            return 'Pendente de Validação';
        case 'validated':
            return 'Validado';
        case 'verified':
            return 'Verificado';
        default:
            return status || 'Pendente de Validação';
    }
}
</script> 

<style scoped>
/* Estilos específicos, se necessário */
</style> 