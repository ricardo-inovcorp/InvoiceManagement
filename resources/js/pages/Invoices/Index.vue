<template>
    <AuthenticatedLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Faturas</h2>
        </template>

        <Head title="Faturas" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium">Lista de Faturas</h3>
                    <Link :href="route('invoices.create')">
                        <Button>Nova Fatura</Button>
                    </Link>
                </div>

                <Card>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Número</TableHead>
                                    <TableHead>Fornecedor</TableHead>
                                    <TableHead>Data de Emissão</TableHead>
                                    <TableHead>Data de Vencimento</TableHead>
                                    <TableHead>Valor Total</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="invoice in invoices.data" :key="invoice.id">
                                    <TableCell>{{ invoice.invoice_number }}</TableCell>
                                    <TableCell>{{ invoice.supplier.name }}</TableCell>
                                    <TableCell>{{ formatDate(invoice.issue_date) }}</TableCell>
                                    <TableCell>{{ formatDate(invoice.due_date) }}</TableCell>
                                    <TableCell>{{ formatCurrency(invoice.total_amount) }}</TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusBadgeVariant(invoice.status)">
                                            {{ getStatusLabel(invoice.status) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
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

                        <!-- Paginação -->
                        <div class="mt-4">
                            <Pagination :links="invoices.links" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    auth: Object,
    invoices: {
        type: Object,
        required: true,
    },
});

const getStatusBadgeVariant = (status) => {
    switch (status) {
        case 'paid':
            return 'success';
        case 'pending':
            return 'warning';
        case 'overdue':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'paid':
            return 'Pago';
        case 'pending':
            return 'Pendente';
        case 'overdue':
            return 'Atrasado';
        default:
            return 'Cancelado';
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
};
</script> 