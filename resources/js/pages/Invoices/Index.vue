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
                            v-if="form.search"
                        >
                            Limpar
                        </Button>
                        <Link :href="route('invoices.create')">
                            <Button>Nova Fatura</Button>
                        </Link>
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
                                    <TableHead class="whitespace-nowrap">Status</TableHead>
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
import { reactive } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
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
            search: ''
        })
    }
});

const form = reactive({
    search: props.filters.search || '',
});

function search() {
    router.get(route('invoices.index'), {
        search: form.search,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetSearch() {
    form.search = '';
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