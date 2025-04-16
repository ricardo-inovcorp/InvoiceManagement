<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalhes da Conta
            </h2>
        </template>

        <Head :title="`Conta - ${bankAccount.name}`" />

        <div class="w-full py-4">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-4">
                    <Button variant="outline" @click="$inertia.visit(route('bank-accounts.index'))" class="h-8">
                        <ChevronLeft class="h-4 w-4 mr-1" />
                        Voltar
                    </Button>
                    <h1 class="text-2xl font-bold">{{ bankAccount.name }}</h1>
                    <Badge v-if="!bankAccount.is_active" variant="outline">Inativa</Badge>
                </div>
                <div class="flex space-x-2">
                    <Link :href="route('bank-accounts.edit', bankAccount.id)">
                        <Button variant="outline" class="h-8">
                            <Pencil class="h-4 w-4 mr-1" />
                            Editar
                        </Button>
                    </Link>
                    <Button variant="destructive" @click="confirmDelete = true" class="h-8">
                        <Trash class="h-4 w-4 mr-1" />
                        Eliminar
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Informações da Conta -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Informações da Conta</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">Banco</div>
                                <div class="font-medium">{{ bankAccount.bank_name }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">IBAN</div>
                                <div class="font-mono">{{ formatIBAN(bankAccount.iban) }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">Saldo Inicial</div>
                                <div>{{ formatCurrency(bankAccount.initial_balance) }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">Saldo Atual</div>
                                <div class="font-bold text-lg">{{ formatCurrency(bankAccount.current_balance) }}</div>
                            </div>
                            <div class="md:col-span-2">
                                <div class="text-sm font-medium text-muted-foreground mb-1">Notas</div>
                                <div>{{ bankAccount.notes || 'Sem notas' }}</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Card de Resumo -->
                <Card>
                    <CardHeader>
                        <CardTitle>Resumo</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">
                                    Estado
                                </div>
                                <Badge :variant="bankAccount.is_active ? 'default' : 'outline'">
                                    {{ bankAccount.is_active ? 'Activa' : 'Inactiva' }}
                                </Badge>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">
                                    Criada em
                                </div>
                                <div>{{ formatDate(bankAccount.created_at) }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-muted-foreground mb-1">
                                    Última atualização
                                </div>
                                <div>{{ formatDate(bankAccount.updated_at) }}</div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button variant="outline" class="w-full" @click="exportTransactions" data-export-button>
                            <Download class="h-4 w-4 mr-2" />
                            Exportar Transações
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Transações -->
            <Card class="mt-6">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle>Transações Recentes</CardTitle>
                    <Button variant="outline" size="sm" @click="showNewTransactionDialog">
                        <Plus class="h-4 w-4 mr-2" />
                        Nova Transação
                    </Button>
                </CardHeader>
                <CardContent>
                    <div v-if="!transactions.data || transactions.data.length === 0" class="text-center py-8">
                        <BanknoteIcon class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                        <h3 class="font-semibold text-lg mb-2">Sem transacções</h3>
                        <p class="text-muted-foreground mb-4 max-w-md mx-auto">
                            Esta conta ainda não tem transacções registadas.
                        </p>
                    </div>
                    <Table v-else>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Data</TableHead>
                                <TableHead>Descrição</TableHead>
                                <TableHead>Tipo</TableHead>
                                <TableHead class="text-right">Valor</TableHead>
                                <TableHead>Notas</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow 
                                v-for="transaction in transactions.data" 
                                :key="transaction.id"
                                class="cursor-pointer hover:bg-muted/50"
                                @click="navigateToTransactionDetail(transaction.id)"
                            >
                                <TableCell>{{ formatDateTime(transaction.created_at) }}</TableCell>
                                <TableCell>{{ transaction.description }}</TableCell>
                                <TableCell>
                                    <Badge :variant="transaction.type === 'credit' ? 'default' : 'destructive'">
                                        {{ transaction.type === 'credit' ? 'Crédito' : 'Débito' }}
                                    </Badge>
                                </TableCell>
                                <TableCell :class="['text-right', transaction.type === 'debit' ? 'text-destructive' : 'text-emerald-600']">
                                    {{ formatCurrency(transaction.amount) }}
                                </TableCell>
                                <TableCell>{{ truncateText(transaction.notes, 20) }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    
                    <!-- Paginação -->
                    <div class="flex items-center justify-between mt-4">
                        <div class="text-sm text-muted-foreground">
                            Mostrando {{ transactions.from }} a {{ transactions.to }} de {{ transactions.total }} transações
                        </div>
                        <div class="flex items-center space-x-2">
                            <Button 
                                variant="outline" 
                                size="sm" 
                                :disabled="!transactions.prev_page_url"
                                @click="changePage(transactions.current_page - 1)"
                            >
                                Anterior
                            </Button>
                            <div class="text-sm">
                                Página {{ transactions.current_page }} de {{ transactions.last_page }}
                            </div>
                            <Button 
                                variant="outline" 
                                size="sm" 
                                :disabled="!transactions.next_page_url"
                                @click="changePage(transactions.current_page + 1)"
                            >
                                Próxima
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Diálogo de Confirmação de Exclusão -->
        <Dialog :open="confirmDelete" @update:open="confirmDelete = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Eliminar Conta</DialogTitle>
                    <DialogDescription>
                        Tem certeza que deseja eliminar esta conta bancária? Esta ação é irreversível.
                    </DialogDescription>
                </DialogHeader>
                <div class="text-muted-foreground">
                    <p v-if="transactions.length > 0" class="mb-2 font-semibold text-destructive">
                        Esta conta possui {{ transactions.length }} transações associadas.
                    </p>
                    <p>
                        Nome: <span class="font-semibold">{{ bankAccount.name }}</span>
                    </p>
                    <p>
                        IBAN: <span class="font-mono">{{ formatIBAN(bankAccount.iban) }}</span>
                    </p>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="confirmDelete = false">Cancelar</Button>
                    <Button variant="destructive" @click="deleteAccount">Confirmar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Diálogo de Nova Transação -->
        <Dialog :open="isNewTransactionDialogOpen" @update:open="isNewTransactionDialogOpen = $event">
            <DialogContent class="max-w-md sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Nova Transação</DialogTitle>
                    <DialogDescription>
                        Registrar uma nova transação para a conta {{ bankAccount.name }}
                    </DialogDescription>
                </DialogHeader>
                
                <!-- Formulário simplificado para funcionar com o router do Inertia -->
                <form @submit.prevent="handleFormSubmit">
                    <input type="hidden" name="bank_account_id" :value="bankAccount.id">
                    
                    <div class="grid gap-4 py-4">
                        <!-- Tipo de Transação -->
                        <div class="grid gap-2">
                            <Label for="type">Tipo de Transação</Label>
                            <div class="flex gap-4">
                                <div class="flex items-center">
                                    <input 
                                        type="radio" 
                                        id="credit" 
                                        value="credit" 
                                        v-model="newTransaction.type"
                                        name="type"
                                        class="mr-2"
                                    />
                                    <Label for="credit">Crédito (Entrada)</Label>
                                </div>
                                <div class="flex items-center">
                                    <input 
                                        type="radio" 
                                        id="debit" 
                                        value="debit" 
                                        v-model="newTransaction.type"
                                        name="type"
                                        class="mr-2"
                                    />
                                    <Label for="debit">Débito (Saída)</Label>
                                </div>
                            </div>
                            <p v-if="transactionErrors.type" class="text-sm text-destructive">
                                {{ transactionErrors.type }}
                            </p>
                        </div>
                        
                        <!-- Data da Transação -->
                        <div class="grid gap-2">
                            <Label for="transaction_date">Data da Transação</Label>
                            <Input 
                                type="date" 
                                id="transaction_date" 
                                v-model="newTransaction.transaction_date"
                                name="transaction_date"
                                required
                            />
                            <p v-if="transactionErrors.transaction_date" class="text-sm text-destructive">
                                {{ transactionErrors.transaction_date }}
                            </p>
                        </div>
                        
                        <!-- Descrição -->
                        <div class="grid gap-2">
                            <Label for="description">Descrição</Label>
                            <Input 
                                type="text" 
                                id="description" 
                                v-model="newTransaction.description"
                                name="description"
                                placeholder="Ex: Pagamento de Fatura, Transferência"
                                required
                            />
                            <p v-if="transactionErrors.description" class="text-sm text-destructive">
                                {{ transactionErrors.description }}
                            </p>
                        </div>
                        
                        <!-- Valor -->
                        <div class="grid gap-2">
                            <Label for="amount">Valor</Label>
                            <Input 
                                type="number" 
                                id="amount" 
                                v-model="newTransaction.amount"
                                name="amount"
                                step="0.01"
                                min="0.01"
                                required
                            />
                            <p v-if="transactionErrors.amount" class="text-sm text-destructive">
                                {{ transactionErrors.amount }}
                            </p>
                        </div>

                        <!-- Associar Fatura (opcional) -->
                        <div class="grid gap-2" v-if="newTransaction.type === 'debit'">
                            <Label for="invoice_id">Associar a uma Fatura (opcional)</Label>
                            <select
                                id="invoice_id"
                                v-model="newTransaction.invoice_id"
                                name="invoice_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">Nenhuma</option>
                                <option v-for="invoice in pendingInvoices" :key="invoice.id" :value="invoice.id">
                                    {{ invoice.invoice_number }} - {{ formatCurrency(invoice.total_amount) }}
                                </option>
                            </select>
                            <p v-if="transactionErrors.invoice_id" class="text-sm text-destructive">
                                {{ transactionErrors.invoice_id }}
                            </p>
                        </div>
                        
                        <!-- Observações -->
                        <div class="grid gap-2">
                            <Label for="notes">Observações (opcional)</Label>
                            <Textarea 
                                id="notes" 
                                v-model="newTransaction.notes"
                                name="notes"
                                placeholder="Observações adicionais sobre a transação"
                            ></Textarea>
                        </div>
                    </div>
                    
                    <!-- Mensagem de erro geral -->
                    <p v-if="transactionErrors.error" class="text-sm text-destructive mb-4">
                        {{ transactionErrors.error }}
                    </p>
                    
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isNewTransactionDialogOpen = false">Cancelar</Button>
                        <Button type="submit" :disabled="isSubmitting">
                            {{ isSubmitting ? 'Salvando...' : 'Salvar' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { ChevronLeft, Pencil, Trash, Download, Plus, Banknote as BanknoteIcon } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps({
    auth: Object,
    bankAccount: Object,
    transactions: {
        type: Array,
        default: () => []
    },
    pendingInvoices: {
        type: Array,
        default: () => []
    }
});

const confirmDelete = ref(false);
const isNewTransactionDialogOpen = ref(false);
const newTransaction = ref({
    type: 'credit',
    transaction_date: new Date().toISOString().split('T')[0],
    description: '',
    amount: '',
    invoice_id: '',
    notes: ''
});
const transactionErrors = ref({});
const isSubmitting = ref(false);

// Obter o token CSRF de forma mais confiável
const csrf = computed(() => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
});

function deleteAccount() {
    router.delete(route('bank-accounts.destroy', props.bankAccount.id), {
        onSuccess: () => {
            confirmDelete.value = false;
        }
    });
}

function exportTransactions() {
    // Mostrar indicador de carregamento
    const button = document.querySelector('button[data-export-button]');
    const originalText = button?.innerHTML || '';
    if (button) {
        button.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Exportando...';
    }
    
    try {
        // Abrir nova aba com a URL de exportação (abordagem direta)
        window.location.href = route('bank-accounts.export', props.bankAccount.id);
        
        // Restaurar texto do botão após um breve delay
        setTimeout(() => {
            if (button) {
                button.innerHTML = originalText;
            }
        }, 1000);
    } catch (error) {
        console.error('Erro ao exportar transações:', error);
        alert('Não foi possível exportar as transações. Por favor, tente novamente.');
        
        // Restaurar texto do botão
        if (button) {
            button.innerHTML = originalText;
        }
    }
}

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR'
    }).format(value);
}

function formatIBAN(iban) {
    if (!iban) return '';
    // Format IBAN with spaces every 4 characters for better readability
    return iban.replace(/(.{4})/g, '$1 ').trim();
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('pt-PT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(date);
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
        hour12: false
    }).format(date);
}

function showNewTransactionDialog() {
    // Resetar formulário
    newTransaction.value = {
        type: 'credit',
        transaction_date: new Date().toISOString().split('T')[0],
        description: '',
        amount: '',
        invoice_id: '',
        notes: ''
    };
    
    // Limpar erros
    transactionErrors.value = {};
    
    // Abrir o diálogo
    isNewTransactionDialogOpen.value = true;
}

function handleFormSubmit(event) {
    event.preventDefault();
    isSubmitting.value = true;
    transactionErrors.value = {};
    
    // Criar um objeto com os dados do formulário
    const form = event.target;
    const formData = new FormData(form);
    const data = {};
    
    // Converter FormData para objeto simples
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    // Usar o router do Inertia.js para enviar o formulário
    // (manipula automaticamente os tokens CSRF)
    router.post(route('bank-transactions.store'), data, {
        onSuccess: () => {
            // Fechar o modal e recarregar a página
            isNewTransactionDialogOpen.value = false;
            
            // Recarregar a página inteira após um pequeno delay
            setTimeout(() => {
                window.location.reload();
            }, 100);
        },
        onError: (errors) => {
            // Mostrar erros no formulário
            transactionErrors.value = errors;
            isSubmitting.value = false;
        }
    });
}

/**
 * Trunca o texto para o número especificado de caracteres e adiciona reticências
 */
function truncateText(text, maxLength) {
    if (!text) return '-';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
}

/**
 * Navega para a página de detalhes da transação
 */
function navigateToTransactionDetail(transactionId) {
    router.visit(route('bank-transactions.show', transactionId));
}

function changePage(page) {
    router.get(route('bank-accounts.show', props.bankAccount.id), {
        page: page
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['transactions']
    });
}
</script> 