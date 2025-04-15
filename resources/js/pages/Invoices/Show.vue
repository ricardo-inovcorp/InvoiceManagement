<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detalhes da Fatura</h2>
        </template>

        <Head :title="`Fatura - ${invoice.invoice_number}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Cabeçalho com número da fatura e ações -->
                <div class="mb-6 flex justify-between items-center">
                    <h3 class="text-lg font-medium">Fatura #{{ invoice.invoice_number }}</h3>
                    <div class="flex space-x-2">
                        <Link :href="route('invoices.index')">
                            <Button variant="outline">Voltar</Button>
                        </Link>
                        <Button variant="outline" @click="goToValidationPage">
                            Validar Itens
                        </Button>
                        <Link :href="route('invoices.edit', invoice.id)">
                            <Button variant="outline">Editar</Button>
                        </Link>
                        <Button variant="destructive" @click="showDeleteModal = true">
                            Excluir
                        </Button>
                    </div>
                </div>

                <!-- Informações da fatura -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Detalhes da fatura -->
                    <Card class="lg:col-span-2">
                        <CardHeader>
                            <CardTitle>Detalhes da Fatura</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <dl class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Número da Fatura</dt>
                                    <dd>{{ invoice.invoice_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Status do Pagamento</dt>
                                    <dd>
                                        <Badge :variant="getBadgeVariant(invoice.status)">
                                            {{ getStatusText(invoice.status) }}
                                        </Badge>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Status de Validação</dt>
                                    <dd>
                                        <Badge :variant="getValidationBadgeVariant(invoice.validation_status)">
                                            {{ getValidationStatusText(invoice.validation_status) }}
                                        </Badge>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Data de Emissão</dt>
                                    <dd>{{ formatDate(invoice.issue_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Data de Vencimento</dt>
                                    <dd>{{ formatDate(invoice.due_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Valor Base</dt>
                                    <dd>{{ formatCurrency(calculateBaseAmount()) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Valor do Imposto</dt>
                                    <dd>{{ formatCurrency(invoice.tax_amount) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Valor Total</dt>
                                    <dd class="font-bold">{{ formatCurrency(invoice.total_amount) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Método de Pagamento</dt>
                                    <dd>{{ invoice.payment_method || '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Data de Pagamento</dt>
                                    <dd>{{ formatDate(invoice.payment_date) || '-' }}</dd>
                                </div>
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Ficheiro da Fatura</dt>
                                    <dd v-if="invoice.file_path">
                                        <a :href="route('invoices.view-file', invoice.id)" target="_blank" class="text-blue-600 hover:underline flex items-center">
                                            <span class="mr-1">Visualizar Ficheiro</span>
                                        </a>
                                    </dd>
                                    <dd v-else>Nenhum arquivo anexado</dd>
                                </div>
                                <div class="col-span-2">
                                    <dt class="text-sm font-medium text-muted-foreground mb-1">Observações</dt>
                                    <dd class="whitespace-pre-wrap">{{ invoice.notes || '-' }}</dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <!-- Dados do fornecedor -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Fornecedor</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="invoice.supplier">
                                <h4 class="text-base font-medium mb-2">{{ invoice.supplier.name }}</h4>
                                <p class="text-sm text-muted-foreground mb-1">NIF: {{ invoice.supplier.document }}</p>
                                <p class="text-sm text-muted-foreground mb-1">{{ invoice.supplier.email }}</p>
                                <p class="text-sm text-muted-foreground mb-1">{{ invoice.supplier.phone }}</p>
                                <p class="text-sm text-muted-foreground mb-4">
                                    {{ invoice.supplier.address }}<br>
                                    {{ invoice.supplier.city }}, {{ invoice.supplier.state }}<br>
                                    {{ invoice.supplier.zip_code }}
                                </p>
                                <Link :href="route('suppliers.show', invoice.supplier.id)">
                                    <Button variant="outline" size="sm">Ver detalhes do fornecedor</Button>
                                </Link>
                            </div>
                            <div v-else>
                                <p class="text-muted-foreground">Fornecedor não encontrado</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Itens da fatura -->
                <div class="mt-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Itens da Fatura</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="invoice.items && invoice.items.length > 0">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Descrição</TableHead>
                                            <TableHead class="text-right">Quantidade</TableHead>
                                            <TableHead class="text-right">Preço Unitário</TableHead>
                                            <TableHead class="text-right">Taxa de Imposto</TableHead>
                                            <TableHead class="text-right">Valor do Imposto</TableHead>
                                            <TableHead class="text-right">Total</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="item in invoice.items" :key="item.id">
                                            <TableCell>{{ item.description }}</TableCell>
                                            <TableCell class="text-right">{{ item.quantity }}</TableCell>
                                            <TableCell class="text-right">{{ formatCurrency(item.unit_price) }}</TableCell>
                                            <TableCell class="text-right">{{ item.tax_rate }}%</TableCell>
                                            <TableCell class="text-right">{{ formatCurrency(item.tax_amount) }}</TableCell>
                                            <TableCell class="text-right">{{ formatCurrency(item.total_price) }}</TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>

                                <!-- Resumo da fatura -->
                                <div class="mt-6 flex justify-end">
                                    <div class="w-full max-w-xs">
                                        <div class="flex justify-between py-2">
                                            <span class="font-medium">Subtotal:</span>
                                            <span>{{ formatCurrency(calculateBaseAmount()) }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-t border-gray-200">
                                            <span class="font-medium">Impostos:</span>
                                            <span>{{ formatCurrency(invoice.tax_amount) }}</span>
                                        </div>
                                        <div class="flex justify-between py-2 border-t border-gray-200 font-bold">
                                            <span>Total:</span>
                                            <span>{{ formatCurrency(invoice.total_amount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <p class="text-muted-foreground">Nenhum item encontrado para esta fatura</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Histórico de Logs -->
                <div class="mt-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Histórico de Atividades</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="invoice.logs && invoice.logs.length > 0">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Data</TableHead>
                                            <TableHead>Utilizador</TableHead>
                                            <TableHead>Ação</TableHead>
                                            <TableHead>Descrição</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow v-for="log in invoice.logs" :key="log.id">
                                            <TableCell class="whitespace-nowrap">{{ formatDateTime(log.created_at) }}</TableCell>
                                            <TableCell>{{ log.user ? log.user.name : 'Sistema' }}</TableCell>
                                            <TableCell>
                                                <Badge :variant="getLogBadgeVariant(log.action)">
                                                    {{ getLogActionText(log.action) }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div>
                                                    <p>{{ log.description }}</p>
                                                    <Button 
                                                        v-if="log.action === 'updated' && log.old_values && log.new_values"
                                                        variant="ghost" 
                                                        size="sm" 
                                                        class="mt-1 text-xs" 
                                                        @click="toggleLogDetails(log)"
                                                    >
                                                        {{ expandedLogs.includes(log.id) ? 'Ocultar detalhes' : 'Ver detalhes completos' }}
                                                    </Button>
                                                    <div v-if="expandedLogs.includes(log.id)" class="mt-2 text-sm bg-muted p-3 rounded-md">
                                                        <h5 class="font-semibold mb-2">Detalhes das alterações:</h5>
                                                        <div class="grid grid-cols-1 gap-2">
                                                            <div v-for="(newValue, field) in log.new_values" :key="field" class="flex flex-col">
                                                                <template v-if="!isIgnoredField(field) && fieldChanged(field, log.old_values, log.new_values)">
                                                                    <div class="font-medium">{{ getFieldLabel(field) }}</div>
                                                                    <div class="flex items-center gap-2">
                                                                        <div class="bg-red-50 text-red-800 p-1 rounded">
                                                                            <span class="text-xs text-red-500 font-medium">Anterior:</span> 
                                                                            {{ formatLogValue(field, log.old_values[field]) }}
                                                                        </div>
                                                                        <div class="text-muted">→</div>
                                                                        <div class="bg-green-50 text-green-800 p-1 rounded">
                                                                            <span class="text-xs text-green-500 font-medium">Novo:</span> 
                                                                            {{ formatLogValue(field, newValue) }}
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <div v-else class="text-center py-4">
                                <p class="text-muted-foreground">Nenhum registo de atividade para esta fatura</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Modal de confirmação para excluir -->
        <Dialog :open="showDeleteModal" @update:open="showDeleteModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Confirmar exclusão</DialogTitle>
                    <DialogDescription>
                        Você tem certeza que deseja excluir a fatura #{{ invoice.invoice_number }}?<br>
                        Esta ação não poderá ser desfeita.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showDeleteModal = false">Cancelar</Button>
                    <Button variant="destructive" @click="deleteInvoice">Excluir</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps({
    auth: Object,
    invoice: {
        type: Object,
        required: true,
    },
});

const showDeleteModal = ref(false);
const expandedLogs = ref([]);

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('pt-BR');
}

function formatCurrency(value) {
    if (value === null || value === undefined) return '€ 0,00';
    
    // Formata com a localização correta
    const formatted = new Intl.NumberFormat('pt-PT', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
    
    // Retorna com o símbolo € no início
    return `€ ${formatted}`;
}

function getBadgeVariant(status) {
    switch (status) {
        case 'paid':
            return 'success';
        case 'pending':
            return 'secondary';
        case 'overdue':
            return 'destructive';
        case 'cancelled':
            return 'outline';
        default:
            return 'default';
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

function getStatusText(status) {
    const statusMap = {
        pending: 'Pendente',
        paid: 'Pago',
        overdue: 'Atrasado',
        cancelled: 'Cancelado'
    };
    return statusMap[status] || status;
}

function getValidationStatusText(status) {
    const statusMap = {
        pending: 'Pendente de Validação',
        validated: 'Validado',
        verified: 'Verificado'
    };
    return statusMap[status] || status;
}

function calculateBaseAmount() {
    // Se o valor base já estiver definido, usá-lo
    if (props.invoice.base_amount && props.invoice.base_amount > 0) {
        return props.invoice.base_amount;
    }
    
    // Se não, tentar calcular a partir do total e do imposto
    if (props.invoice.total_amount && props.invoice.tax_amount) {
        return props.invoice.total_amount - props.invoice.tax_amount;
    }
    
    // Se não for possível, calcular a partir dos itens
    if (props.invoice.items && props.invoice.items.length > 0) {
        return props.invoice.items.reduce((sum, item) => {
            // Base do item = preço unitário * quantidade
            const itemBase = (Number(item.unit_price) || 0) * (Number(item.quantity) || 0);
            return sum + itemBase;
        }, 0);
    }
    
    // Se nada funcionar, retornar 0
    return 0;
}

function deleteInvoice() {
    router.delete(route('invoices.destroy', props.invoice.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        }
    });
}

function formatDateTime(dateTime) {
    if (!dateTime) return '-';
    const date = new Date(dateTime);
    return date.toLocaleDateString('pt-PT') + ' ' + date.toLocaleTimeString('pt-PT');
}

function getLogBadgeVariant(action) {
    switch (action) {
        case 'created':
            return 'success';
        case 'updated':
            return 'warning';
        case 'deleted':
            return 'destructive';
        default:
            return 'default';
    }
}

function getLogActionText(action) {
    switch (action) {
        case 'created':
            return 'Created';
        case 'updated':
            return 'Updated';
        case 'deleted':
            return 'Deleted';
        default:
            return action;
    }
}

function toggleLogDetails(log) {
    if (expandedLogs.value.includes(log.id)) {
        expandedLogs.value = expandedLogs.value.filter(id => id !== log.id);
    } else {
        expandedLogs.value.push(log.id);
    }
}

function isIgnoredField(field) {
    return ['created_at', 'updated_at', 'id', 'deleted_at'].includes(field);
}

function fieldChanged(field, oldValues, newValues) {
    return oldValues && newValues && 
           oldValues[field] !== undefined && 
           newValues[field] !== undefined && 
           oldValues[field] !== newValues[field];
}

function getFieldLabel(field) {
    const fieldLabels = {
        'supplier_id': 'Fornecedor',
        'invoice_number': 'Número da Fatura',
        'issue_date': 'Data de Emissão',
        'due_date': 'Data de Vencimento',
        'total_amount': 'Valor Total',
        'tax_amount': 'Valor do Imposto',
        'status': 'Status',
        'payment_method': 'Método de Pagamento',
        'payment_date': 'Data de Pagamento',
        'file_path': 'Arquivo da Fatura',
        'notes': 'Observações'
    };
    
    return fieldLabels[field] || field;
}

function formatLogValue(field, value) {
    if (value === null || value === undefined) {
        return 'Não preenchido';
    }
    
    // Formata datas
    if (['issue_date', 'due_date', 'payment_date'].includes(field) && value) {
        return formatDate(value);
    }
    
    // Formata status
    if (field === 'status') {
        return getStatusText(value);
    }
    
    // Formata valores monetários
    if (['total_amount', 'tax_amount'].includes(field)) {
        return formatCurrency(value);
    }
    
    // Para arquivo da fatura, mostra apenas se existe ou não
    if (field === 'file_path') {
        return value ? 'Arquivo anexado' : 'Sem arquivo';
    }
    
    return value;
}

function goToValidationPage() {
    const validationUrl = route('invoices.validate', props.invoice.id);
    
    // Usar JavaScript nativo para navegar diretamente
    window.location.href = validationUrl;
}
</script> 