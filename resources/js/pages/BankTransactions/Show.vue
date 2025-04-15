<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalhes da Transação
            </h2>
        </template>

        <Head title="Detalhes da Transação" />

        <div class="w-full py-4">
            <div class="flex justify-between items-center mb-6">
                <Button variant="outline" @click="$inertia.visit(route('bank-accounts.show', transaction.bank_account_id))" class="h-8">
                    <ChevronLeft class="h-4 w-4 mr-1" />
                    Voltar para Conta
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Detalhes da Transação -->
                <Card class="md:col-span-2">
                    <CardHeader class="pb-2">
                        <div class="flex items-center justify-between">
                            <CardTitle>Detalhes da Transação</CardTitle>
                            <Badge :variant="transaction.type === 'credit' ? 'default' : 'destructive'">
                                {{ transaction.type === 'credit' ? 'Crédito' : 'Débito' }}
                            </Badge>
                        </div>
                        <CardDescription>ID: {{ transaction.id }}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">Data e Hora</div>
                                <div class="font-medium">{{ formatDateTime(transaction.created_at) }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">Conta Bancária</div>
                                <div class="font-medium">{{ transaction.bank_account?.name || '-' }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">Descrição</div>
                                <div>{{ transaction.description }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">Valor</div>
                                <div :class="transaction.type === 'debit' ? 'text-destructive font-bold' : 'text-emerald-600 font-bold'">
                                    {{ formatCurrency(transaction.amount) }}
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <div class="text-sm font-medium text-muted-foreground mb-1">Notas</div>
                                <div class="whitespace-pre-wrap bg-muted/40 p-3 rounded-md">{{ transaction.notes || 'Nenhuma nota adicional.' }}</div>
                            </div>
                            
                            <div v-if="transaction.invoice">
                                <div class="text-sm font-medium text-muted-foreground mb-1">Associada à Fatura</div>
                                <Link :href="route('invoices.show', transaction.invoice.id)" class="text-primary hover:underline">
                                    {{ transaction.invoice.invoice_number }}
                                </Link>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card de Informações Adicionais -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Informações Adicionais</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">
                                    Criada em
                                </div>
                                <div>{{ formatDateTime(transaction.created_at) }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">
                                    Última atualização
                                </div>
                                <div>{{ formatDateTime(transaction.updated_at) }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription
} from '@/components/ui/card';

const props = defineProps({
    auth: Object,
    transaction: Object
});

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR'
    }).format(value || 0);
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    
    // Criar objeto Date a partir da string da data
    const date = new Date(dateString);
    
    // Verificar se a data é válida
    if (isNaN(date.getTime())) return '-';
    
    // Formatar a data e hora no padrão português
    return new Intl.DateTimeFormat('pt-PT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    }).format(date);
}
</script> 