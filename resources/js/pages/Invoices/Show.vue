<template>
    <AuthenticatedLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalhes da Fatura</h2>
        </template>

        <Head :title="`Fatura - ${invoice.invoice_number}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="space-y-6">
                    <!-- Card de Informações da Fatura -->
                    <Card>
                        <CardHeader>
                            <div class="flex justify-between items-center">
                                <CardTitle>Informações da Fatura</CardTitle>
                                <div class="flex space-x-2">
                                    <Link :href="route('invoices.edit', invoice.id)">
                                        <Button variant="outline">Editar</Button>
                                    </Link>
                                    <Link :href="route('invoices.index')">
                                        <Button variant="outline">Voltar</Button>
                                    </Link>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Dados da Fatura -->
                                <div>
                                    <h3 class="font-medium mb-2">Dados da Fatura</h3>
                                    <dl class="space-y-2">
                                        <div>
                                            <dt class="text-sm text-gray-500">Número da Fatura</dt>
                                            <dd class="font-medium">{{ invoice.invoice_number }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Data de Emissão</dt>
                                            <dd class="font-medium">{{ formatDate(invoice.issue_date) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Data de Vencimento</dt>
                                            <dd class="font-medium">{{ formatDate(invoice.due_date) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Status</dt>
                                            <dd>
                                                <Badge :variant="getStatusBadgeVariant(invoice.status)">
                                                    {{ getStatusLabel(invoice.status) }}
                                                </Badge>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Dados do Fornecedor -->
                                <div>
                                    <h3 class="font-medium mb-2">Fornecedor</h3>
                                    <dl class="space-y-2">
                                        <div>
                                            <dt class="text-sm text-gray-500">Nome</dt>
                                            <dd class="font-medium">{{ invoice.supplier.name }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Documento</dt>
                                            <dd class="font-medium">{{ invoice.supplier.document }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <!-- Informações de Pagamento -->
                            <div v-if="invoice.payment_method" class="mt-6">
                                <h3 class="font-medium mb-2">Informações de Pagamento</h3>
                                <dl class="space-y-2">
                                    <div>
                                        <dt class="text-sm text-gray-500">Método de Pagamento</dt>
                                        <dd class="font-medium">{{ invoice.payment_method }}</dd>
                                    </div>
                                    <div v-if="invoice.payment_date">
                                        <dt class="text-sm text-gray-500">Data do Pagamento</dt>
                                        <dd class="font-medium">{{ formatDate(invoice.payment_date) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Observações -->
                            <div v-if="invoice.notes" class="mt-6">
                                <h3 class="font-medium mb-2">Observações</h3>
                                <p class="text-gray-600">{{ invoice.notes }}</p>
                            </div>

                            <!-- Arquivo da Fatura -->
                            <div v-if="invoice.file_path" class="mt-6">
                                <h3 class="font-medium mb-2">Arquivo da Fatura</h3>
                                <a
                                    :href="route('invoices.download', invoice.id)"
                                    class="text-blue-600 hover:text-blue-800"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    Baixar PDF
                                </a>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Card de Itens da Fatura -->
                    <Card>
                        <CardHeader>
                            <div class="flex justify-between items-center">
                                <CardTitle>Itens da Fatura</CardTitle>
                                <Link :href="route('invoices.items.create', invoice.id)">
                                    <Button>Adicionar Item</Button>
                                </Link>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Descrição</TableHead>
                                        <TableHead>Quantidade</TableHead>
                                        <TableHead>Preço Unitário</TableHead>
                                        <TableHead>Taxa de Imposto</TableHead>
                                        <TableHead>Valor do Imposto</TableHead>
                                        <TableHead>Total</TableHead>
                                        <TableHead>Ações</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="item in invoice.items" :key="item.id">
                                        <TableCell>{{ item.description }}</TableCell>
                                        <TableCell>{{ item.quantity }}</TableCell>
                                        <TableCell>{{ formatCurrency(item.unit_price) }}</TableCell>
                                        <TableCell>{{ item.tax_rate }}%</TableCell>
                                        <TableCell>{{ formatCurrency(item.tax_amount) }}</TableCell>
                                        <TableCell>{{ formatCurrency(item.total_price) }}</TableCell>
                                        <TableCell>
                                            <div class="flex space-x-2">
                                                <Link :href="route('invoices.items.edit', [invoice.id, item.id])">
                                                    <Button variant="outline" size="sm">
                                                        Editar
                                                    </Button>
                                                </Link>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>

                            <!-- Resumo dos Valores -->
                            <div class="mt-6 flex justify-end">
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">Subtotal</div>
                                    <div class="text-lg font-medium">
                                        {{ formatCurrency(invoice.total_amount - invoice.tax_amount) }}
                                    </div>
                                </div>
                                <div class="ml-8 text-right">
                                    <div class="text-sm text-gray-500">Impostos</div>
                                    <div class="text-lg font-medium">
                                        {{ formatCurrency(invoice.tax_amount) }}
                                    </div>
                                </div>
                                <div class="ml-8 text-right">
                                    <div class="text-sm text-gray-500">Total</div>
                                    <div class="text-lg font-medium">
                                        {{ formatCurrency(invoice.total_amount) }}
                                    </div>
                                </div>
                            </div>
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
    invoice: {
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