<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Faturas</h2>
        </template>

        <Head title="Faturas" />

        <div class="w-full py-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium">Faturas</h3>
            </div>

            <!-- Barra de pesquisa -->
            <div class="mb-6">
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
                            <Button type="submit">Pesquisar</Button>
                            <Button 
                                type="button" 
                                variant="outline" 
                                @click="resetSearch"
                                v-if="hasFilters"
                            >
                                Limpar
                            </Button>
                            <Button 
                                type="button" 
                                variant="outline" 
                                @click="showFilters = !showFilters"
                            >
                                {{ showFilters ? 'Esconder Filtros' : 'Filtros Avançados' }}
                            </Button>
                            <Link :href="route('invoices.create')">
                                <Button>Nova Fatura</Button>
                            </Link>
                        </div>
                        
                        <!-- Filtros avançados -->
                        <div v-if="showFilters" class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
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
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
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
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <Label for="status">Status do Pagamento</Label>
                                    <Select 
                                        id="status" 
                                        v-model="form.status"
                                        class="bg-black text-white"
                                        :options="[
                                            { value: '', label: 'Todos' },
                                            { value: 'pending', label: 'Pendente' },
                                            { value: 'paid', label: 'Pago' },
                                            { value: 'overdue', label: 'Atrasado' },
                                            { value: 'cancelled', label: 'Cancelado' }
                                        ]"
                                    />
                                </div>
                                <div>
                                    <Label for="validation_status">Status de Validação</Label>
                                    <Select 
                                        id="validation_status" 
                                        v-model="form.validation_status"
                                        class="bg-black text-white"
                                        :options="[
                                            { value: '', label: 'Todos' },
                                            { value: 'pending', label: 'Pendente de Validação' },
                                            { value: 'validated', label: 'Validado' },
                                            { value: 'verified', label: 'Verificado' }
                                        ]"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <Card class="w-full">
                <CardContent class="overflow-auto p-0">
                    <div class="w-full overflow-x-auto">
                        <Table class="w-full">
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="whitespace-nowrap">Fatura</TableHead>
                                    <TableHead class="whitespace-nowrap">Fornecedor</TableHead>
                                    <TableHead class="whitespace-nowrap">Emissão</TableHead>
                                    <TableHead class="whitespace-nowrap">Vencimento</TableHead>
                                    <TableHead class="whitespace-nowrap">Valor Total</TableHead>
                                    <TableHead class="whitespace-nowrap">Status do Pagamento</TableHead>
                                    <TableHead class="whitespace-nowrap">Status de Validação</TableHead>
                                    <TableHead class="whitespace-nowrap">Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="invoice in invoices.data" :key="invoice.id">
                                    <TableCell class="whitespace-nowrap">{{ invoice.invoice_number }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ invoice.supplier ? invoice.supplier.company_name : '-' }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ formatDate(invoice.issue_date) }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ formatDate(invoice.due_date) }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ formatCurrency(invoice.total_amount) }}</TableCell>
                                    <TableCell class="whitespace-nowrap">
                                        <Badge :variant="getBadgeVariant(invoice.status)">
                                            {{ getStatusText(invoice.status) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="whitespace-nowrap">
                                        <Badge :variant="getValidationBadgeVariant(invoice.validation_status)">
                                            {{ getValidationStatusText(invoice.validation_status) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <Link :href="route('invoices.edit', invoice.id)">
                                                <Button variant="outline" size="sm">
                                                    Editar
                                                </Button>
                                            </Link>
                                            <Link :href="route('invoices.show', invoice.id)">
                                                <Button variant="outline" size="sm">
                                                    Detalhes
                                                </Button>
                                            </Link>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Paginação -->
                    <div class="mt-4 pl-4 pb-4 flex justify-start">
                        <Pagination :links="invoices.links" />
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import Pagination from '@/components/Pagination.vue';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';

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
    return new Date(date).toLocaleDateString('pt-BR');
}

function formatCurrency(value) {
    if (!value) return 'R$ 0,00';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
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
/* Força a tabela a ocupar toda a largura disponível */
:deep(.table) {
    width: 100%;
    table-layout: auto;
}

:deep(.sidebar-inset) {
    max-width: 100% !important;
    padding: 0 !important;
}

:deep(.pagination) {
    justify-content: flex-start !important;
}
</style> 