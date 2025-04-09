<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalhes do Fornecedor</h2>
        </template>

        <Head :title="`Fornecedor - ${supplier.company_name || supplier.name}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-between items-center">
                    <h3 class="text-lg font-medium">{{ supplier.company_name || supplier.name }}</h3>
                    <div class="flex space-x-2">
                        <Link :href="route('suppliers.edit', supplier.id)">
                            <Button variant="outline">Editar</Button>
                        </Link>
                        <Link :href="route('suppliers.index')">
                            <Button variant="outline">Voltar</Button>
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Informações Básicas -->
                    <Card class="md:col-span-2">
                        <CardHeader>
                            <CardTitle>Informações Básicas</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Nome</h4>
                                    <p>{{ supplier.company_name || supplier.name }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">NIF</h4>
                                    <p>{{ supplier.document }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">E-mail</h4>
                                    <p>{{ supplier.email }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Telefone</h4>
                                    <p>{{ supplier.phone }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Status</h4>
                                    <Badge :variant="supplier.active ? 'success' : 'destructive'">
                                        {{ supplier.active ? 'Ativo' : 'Inativo' }}
                                    </Badge>
                                </div>
                                <div v-if="supplier.sector">
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Setor de Atividade</h4>
                                    <p>{{ supplier.sector ? supplier.sector.name : 'Não definido' }}</p>
                                </div>
                                <div v-if="supplier.organizationType">
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Tipo de Organização</h4>
                                    <p>{{ supplier.organizationType ? supplier.organizationType.name : 'Não definido' }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Informações de Endereço -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Endereço</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-2">
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Endereço</h4>
                                    <p>{{ supplier.address }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Cidade</h4>
                                    <p>{{ supplier.city }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Concelho</h4>
                                    <p>{{ supplier.county && supplier.county.name ? supplier.county.name : 'Não definido' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Distrito</h4>
                                    <p>{{ supplier.district && supplier.district.name ? supplier.district.name : 'Não definido' }}</p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-muted-foreground mb-1">Código Postal</h4>
                                    <p>{{ supplier.zip_code }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Histórico de Faturas -->
                <Card class="mt-6">
                    <CardHeader>
                        <div class="flex justify-between items-center">
                            <CardTitle>Faturas deste Fornecedor</CardTitle>
                            <Link :href="route('invoices.create', { supplier_id: supplier.id })">
                                <Button>Nova Fatura</Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="supplier.invoices && supplier.invoices.length > 0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Número</TableHead>
                                        <TableHead>Data de Emissão</TableHead>
                                        <TableHead>Data de Vencimento</TableHead>
                                        <TableHead>Total</TableHead>
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
                                            <Badge :variant="getStatusVariant(invoice.status)">
                                                {{ getStatusLabel(invoice.status) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div class="flex space-x-2">
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
                        <div v-else class="text-center py-4 text-muted-foreground">
                            Nenhuma fatura encontrada para este fornecedor.
                        </div>
                    </CardContent>
                </Card>

                <!-- Notas -->
                <Card class="mt-6" v-if="supplier.notes">
                    <CardHeader>
                        <CardTitle>Notas</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="whitespace-pre-wrap">{{ supplier.notes }}</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

const props = defineProps({
    auth: Object,
    supplier: {
        type: Object,
        required: true,
    },
});

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