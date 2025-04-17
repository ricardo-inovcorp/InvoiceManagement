<template>
        <Head :title="`Fatura - ${invoice.invoice_number}`" />

    <AppLayout :user="auth.user">
        <div class="py-12">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <!-- Cabeçalho com número da fatura e ações -->
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Fatura #{{ invoice.invoice_number }}</h1>
                                <div class="flex space-x-2">
                        <Link :href="route('invoices.index')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            Voltar
                                    </Link>
                        <button @click="goToValidationPage" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            Validar Itens
                        </button>
                        <Link :href="route('invoices.edit', invoice.id)" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            Editar
                                    </Link>
                        <button @click="showDeleteModal = true" class="px-4 py-2 bg-red-500 text-white border border-red-600 rounded-md hover:bg-red-600 transition dark:bg-red-700 dark:border-red-800 dark:hover:bg-red-800">
                            Excluir
                        </button>
                    </div>
                                </div>

                <!-- Informações da fatura -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Detalhes da fatura -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 lg:col-span-2">
                        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Detalhes da Fatura</h2>
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Número da Fatura</dt>
                                <dd class="dark:text-white">{{ invoice.invoice_number }}</dd>
                            </div>
                                <div>
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Status do Pagamento</dt>
                                <dd>
                                    <span 
                                        :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            getBadgeVariant(invoice.status) === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                            getBadgeVariant(invoice.status) === 'secondary' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                            getBadgeVariant(invoice.status) === 'destructive' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                        ]"
                                    >
                                        {{ getStatusText(invoice.status) }}
                                    </span>
                                </dd>
                            </div>
                                        <div>
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Status de Validação</dt>
                                <dd>
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
                                </dd>
                                        </div>
                                        <div>
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Data de Emissão</dt>
                                <dd class="dark:text-white">{{ formatDate(invoice.issue_date) }}</dd>
                                        </div>
                                        <div>
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Data de Vencimento</dt>
                                <dd class="dark:text-white">{{ formatDate(invoice.due_date) }}</dd>
                                        </div>
                                        <div>
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Valor Base</dt>
                                <dd class="dark:text-white">{{ formatCurrency(calculateBaseAmount()) }}</dd>
                                </div>
                                <div>
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Valor do Imposto</dt>
                                <dd class="dark:text-white">{{ formatCurrency(invoice.tax_amount) }}</dd>
                                        </div>
                                        <div>
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Valor Total</dt>
                                <dd class="font-bold dark:text-white">{{ formatCurrency(invoice.total_amount) }}</dd>
                            </div>
                                    <div>
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Método de Pagamento</dt>
                                <dd class="dark:text-white">{{ invoice.payment_method || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Data de Pagamento</dt>
                                <dd class="dark:text-white">{{ formatDate(invoice.payment_date) || '-' }}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Ficheiro da Fatura</dt>
                                <dd v-if="invoice.file_path">
                                    <a :href="route('invoices.view-file', invoice.id)" target="_blank" class="text-blue-600 hover:underline flex items-center dark:text-blue-400">
                                        <span class="mr-1">Visualizar Ficheiro</span>
                                    </a>
                                </dd>
                                <dd v-else class="dark:text-gray-400">Nenhum arquivo anexado</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-sm font-medium text-muted-foreground dark:text-gray-400 mb-1">Observações</dt>
                                <dd class="whitespace-pre-wrap dark:text-white">{{ invoice.notes || '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Dados do fornecedor -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Fornecedor</h2>
                        <div v-if="invoice.supplier">
                            <h4 class="text-base font-medium mb-2 dark:text-white">{{ invoice.supplier.name }}</h4>
                            <p class="text-sm text-muted-foreground dark:text-gray-400 mb-1">NIF: {{ invoice.supplier.document }}</p>
                            <p class="text-sm text-muted-foreground dark:text-gray-400 mb-1">{{ invoice.supplier.email }}</p>
                            <p class="text-sm text-muted-foreground dark:text-gray-400 mb-1">{{ invoice.supplier.phone }}</p>
                            <p class="text-sm text-muted-foreground dark:text-gray-400 mb-4">
                                {{ invoice.supplier.address }}<br>
                                {{ invoice.supplier.city }}, {{ invoice.supplier.state }}<br>
                                {{ invoice.supplier.zip_code }}
                            </p>
                            <Link :href="route('suppliers.show', invoice.supplier.id)" class="px-3 py-1.5 text-sm bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition inline-block dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                                Ver detalhes do fornecedor
                                                </Link>
                                            </div>
                        <div v-else>
                            <p class="text-muted-foreground dark:text-gray-400">Fornecedor não encontrado</p>
                        </div>
                    </div>
                </div>

                <!-- Itens da fatura -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Itens da Fatura</h2>
                    <div v-if="invoice.items && invoice.items.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descrição</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantidade</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Preço Unitário</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Taxa de Imposto</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor do Imposto</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="item in invoice.items" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium dark:text-white">{{ item.description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right dark:text-white">{{ item.quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right dark:text-white">{{ formatCurrency(item.unit_price) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right dark:text-white">{{ item.tax_rate }}%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right dark:text-white">{{ formatCurrency(item.tax_amount) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right dark:text-white">{{ formatCurrency(item.total_price) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Resumo da fatura -->
                            <div class="mt-6 flex justify-end">
                            <div class="w-full max-w-xs bg-gray-50 dark:bg-gray-700 p-4 rounded-md">
                                <div class="flex justify-between py-2">
                                    <span class="font-medium dark:text-white">Subtotal:</span>
                                    <span class="dark:text-white">{{ formatCurrency(calculateBaseAmount()) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-t border-gray-200 dark:border-gray-600">
                                    <span class="font-medium dark:text-white">Impostos:</span>
                                    <span class="dark:text-white">{{ formatCurrency(invoice.tax_amount) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-t border-gray-200 dark:border-gray-600 font-bold">
                                    <span class="dark:text-white">Total:</span>
                                    <span class="dark:text-white">{{ formatCurrency(invoice.total_amount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <p class="text-muted-foreground dark:text-gray-400">Nenhum item encontrado para esta fatura</p>
                    </div>
                </div>

                <!-- Histórico de Logs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Histórico de Atividades</h2>
                    <div v-if="invoice.logs && invoice.logs.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Utilizador</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ação</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descrição</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="log in invoice.logs" :key="log.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-white">{{ formatDateTime(log.created_at) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm dark:text-white">{{ log.user ? log.user.name : 'Sistema' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span
                                                :class="[
                                                    'px-2 py-1 text-xs rounded-full',
                                                    getLogBadgeVariant(log.action) === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                    getLogBadgeVariant(log.action) === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                                    getLogBadgeVariant(log.action) === 'destructive' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                                ]"
                                            >
                                                {{ getLogActionText(log.action) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm dark:text-white">
                                            <div>
                                                <p>{{ log.description }}</p>
                                                <button 
                                                    v-if="log.action === 'updated' && log.old_values && log.new_values"
                                                    class="mt-1 text-xs text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300" 
                                                    @click="toggleLogDetails(log)"
                                                >
                                                    {{ expandedLogs.includes(log.id) ? 'Ocultar detalhes' : 'Ver detalhes completos' }}
                                                </button>
                                                <div v-if="expandedLogs.includes(log.id)" class="mt-2 text-sm bg-gray-100 dark:bg-gray-700 p-3 rounded-md">
                                                    <h5 class="font-semibold mb-2 dark:text-white">Detalhes das alterações:</h5>
                                                    <div class="grid grid-cols-1 gap-2">
                                                        <div v-for="(newValue, field) in log.new_values" :key="field" class="flex flex-col">
                                                            <template v-if="!isIgnoredField(field) && fieldChanged(field, log.old_values, log.new_values)">
                                                                <div class="font-medium dark:text-white">{{ getFieldLabel(field) }}</div>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="bg-red-50 text-red-800 p-1 rounded dark:bg-red-900 dark:text-red-200">
                                                                        <span class="text-xs text-red-500 font-medium dark:text-red-300">Anterior:</span> 
                                                                        {{ formatLogValue(field, log.old_values[field]) }}
                                                                    </div>
                                                                    <div class="text-muted dark:text-gray-400">→</div>
                                                                    <div class="bg-green-50 text-green-800 p-1 rounded dark:bg-green-900 dark:text-green-200">
                                                                        <span class="text-xs text-green-500 font-medium dark:text-green-300">Novo:</span> 
                                                                        {{ formatLogValue(field, newValue) }}
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else class="text-center py-4">
                        <p class="text-muted-foreground dark:text-gray-400">Nenhum registo de atividade para esta fatura</p>
                    </div>
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
    return new Date(date).toLocaleDateString('pt-PT');
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