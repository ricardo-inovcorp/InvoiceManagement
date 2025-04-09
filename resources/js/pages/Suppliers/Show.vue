<template>
    <AuthenticatedLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalhes do Fornecedor</h2>
        </template>

        <Head :title="`Fornecedor - ${supplier.name}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="space-y-6">
                    <!-- Card de Informações do Fornecedor -->
                    <Card>
                        <CardHeader>
                            <div class="flex justify-between items-center">
                                <CardTitle>Informações do Fornecedor</CardTitle>
                                <div class="flex space-x-2">
                                    <Link :href="route('suppliers.edit', supplier.id)">
                                        <Button variant="outline">Editar</Button>
                                    </Link>
                                    <Link :href="route('suppliers.index')">
                                        <Button variant="outline">Voltar</Button>
                                    </Link>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 class="font-medium mb-2">Dados Principais</h3>
                                    <dl class="space-y-2">
                                        <div>
                                            <dt class="text-sm text-gray-500">Nome</dt>
                                            <dd class="font-medium">{{ supplier.name }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">NIF</dt>
                                            <dd class="font-medium">{{ supplier.document }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Email</dt>
                                            <dd class="font-medium">{{ supplier.email }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Telefone</dt>
                                            <dd class="font-medium">{{ supplier.phone }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <div>
                                    <h3 class="font-medium mb-2">Morada e Informações Adicionais</h3>
                                    <dl class="space-y-2">
                                        <div>
                                            <dt class="text-sm text-gray-500">Morada</dt>
                                            <dd class="font-medium">{{ supplier.address }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Código Postal</dt>
                                            <dd class="font-medium">{{ supplier.zip_code }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Localidade</dt>
                                            <dd class="font-medium">{{ supplier.city }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Concelho</dt>
                                            <dd class="font-medium">{{ supplier.county }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Distrito</dt>
                                            <dd class="font-medium">{{ supplier.state }}</dd>
                                        </div>
                                        <div v-if="supplier.notes">
                                            <dt class="text-sm text-gray-500">Observações</dt>
                                            <dd class="font-medium">{{ supplier.notes }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Card de Faturas do Fornecedor -->
                    <Card>
                        <CardHeader>
                            <div class="flex justify-between items-center">
                                <CardTitle>Faturas do Fornecedor</CardTitle>
                                <Link :href="route('invoices.create', { supplier_id: supplier.id })">
                                    <Button>Nova Fatura</Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Número</TableHead>
                                        <TableHead>Data de Emissão</TableHead>
                                        <TableHead>Data de Vencimento</TableHead>
                                        <TableHead>Valor Total</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="invoice in supplier.invoices" :key="invoice.id">
                                        <TableCell>{{ invoice.invoice_number }}</TableCell>
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
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';

const props = defineProps({
    auth: Object,
    supplier: {
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
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR'
    }).format(value);
};
</script> 