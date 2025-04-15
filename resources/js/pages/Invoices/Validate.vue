<template>
    <Head title="Validação de Fatura" />
    <AppLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold">Validação de Fatura #{{ invoice.invoice_number }}</h2>
                    <div class="flex space-x-2">
                        <Link :href="route('invoices.show', invoice.id)">
                            <Button variant="outline">Voltar</Button>
                        </Link>
                        <Button 
                            variant="default" 
                            @click="markAsValidated" 
                            :disabled="!allItemsAssociated || invoice.validation_status !== 'pending' || isValidating"
                            title="Confirma que todos os itens estão corretamente associados aos artigos do sistema"
                        >
                            {{ isValidating ? 'Processando...' : 'Marcar como Validada' }}
                        </Button>
                        <Button 
                            variant="success" 
                            @click="markAsVerified" 
                            :disabled="invoice.validation_status !== 'validated' || isVerifying"
                            title="Confirma que a fatura foi revisada por um gestor e está pronta para pagamento"
                        >
                            {{ isVerifying ? 'Processando...' : 'Marcar como Verificada' }}
                        </Button>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Status de Validação</CardTitle>
                        <CardDescription>
                            <div class="flex items-center space-x-2">
                                <Badge :variant="getValidationBadgeVariant(invoice.validation_status)">
                                    {{ getValidationStatusText(invoice.validation_status) }}
                                </Badge>
                                <div v-if="!allItemsAssociated" class="text-red-500 text-sm">
                                    {{ unassociatedItems.length }} itens precisam ser associados a artigos
                                </div>
                                <div v-else-if="invoice.validation_status === 'pending'" class="text-green-500 text-sm">
                                    Todos os itens estão associados a artigos. Clique em "Marcar como Validada".
                                </div>
                            </div>
                        </CardDescription>
                    </CardHeader>
                </Card>

                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle>Detalhes da Fatura</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium mb-2">Fornecedor</h3>
                                <div class="bg-muted p-4 rounded-md">
                                    <p>{{ invoice.supplier.name }}</p>
                                    <p v-if="invoice.supplier.taxid">NIF: {{ invoice.supplier.taxid }}</p>
                                    <p v-if="invoice.supplier.email">Email: {{ invoice.supplier.email }}</p>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium mb-2">Informações</h3>
                                <div class="bg-muted p-4 rounded-md">
                                    <p>Data de Emissão: {{ formatDate(invoice.issue_date) }}</p>
                                    <p>Data de Vencimento: {{ formatDate(invoice.due_date) }}</p>
                                    <p>Valor Total: {{ formatCurrency(invoice.total_amount) }}</p>
                                    <p>Status: {{ getStatusText(invoice.status) }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle>Itens da Fatura</CardTitle>
                        <CardDescription>
                            Associe cada item da fatura a um artigo no sistema
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Descrição</TableHead>
                                    <TableHead>Quantidade</TableHead>
                                    <TableHead>Valor Unit.</TableHead>
                                    <TableHead>Total</TableHead>
                                    <TableHead>Artigo Associado</TableHead>
                                    <TableHead>Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="item in invoice.items" 
                                    :key="item.id"
                                    :class="{ 'bg-red-50 dark:bg-red-950/20': !item.article_id }"
                                >
                                    <TableCell>{{ item.description }}</TableCell>
                                    <TableCell>{{ item.quantity }}</TableCell>
                                    <TableCell>{{ formatCurrency(item.unit_price) }}</TableCell>
                                    <TableCell>{{ formatCurrency(item.total) }}</TableCell>
                                    <TableCell>
                                        <div v-if="item.article">
                                            <Badge variant="outline">{{ item.article.name }}</Badge>
                                        </div>
                                        <div v-else class="text-red-500">
                                            Não associado
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex space-x-2">
                                            <Button 
                                                variant="outline" 
                                                size="sm" 
                                                @click="openAssociateDialog(item)"
                                                :disabled="invoice.validation_status !== 'pending'"
                                            >
                                                {{ item.article_id ? 'Alterar' : 'Associar' }}
                                            </Button>
                                            <Button 
                                                v-if="!item.article_id"
                                                variant="outline" 
                                                size="sm" 
                                                @click="openNewArticleDialog(item)"
                                                :disabled="invoice.validation_status !== 'pending'"
                                            >
                                                Criar Novo
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Dialog para associar artigos existentes -->
        <Dialog :open="associateDialogOpen" @update:open="associateDialogOpen = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Associar Artigo</DialogTitle>
                    <DialogDescription>
                        Selecione um artigo para associar a este item da fatura
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitAssociateForm">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="article">Artigo</Label>
                            <select 
                                id="article"
                                v-model="associateForm.article_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="">Selecione um artigo</option>
                                <option 
                                    v-for="article in articles" 
                                    :key="article.id" 
                                    :value="article.id"
                                >
                                    {{ article.name }} ({{ article.code }})
                                </option>
                            </select>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="associateDialogOpen = false">
                            Cancelar
                        </Button>
                        <Button type="submit">Associar</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Dialog para criar novo artigo -->
        <Dialog :open="newArticleDialogOpen" @update:open="newArticleDialogOpen = $event">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Criar Novo Artigo</DialogTitle>
                    <DialogDescription>
                        Crie um novo artigo para associar a este item da fatura
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitNewArticleForm">
                    <div class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="name">Nome</Label>
                            <Input id="name" v-model="newArticleForm.name" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="code">Código</Label>
                            <Input id="code" v-model="newArticleForm.code" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="price">Preço</Label>
                            <Input id="price" type="number" step="0.01" v-model="newArticleForm.price" required />
                        </div>
                        <div class="grid gap-2">
                            <Label for="description">Descrição</Label>
                            <Textarea id="description" v-model="newArticleForm.description" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="category">Categoria</Label>
                            <select 
                                id="category"
                                v-model="newArticleForm.category_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                required
                            >
                                <option value="">Selecione uma categoria</option>
                                <option 
                                    v-for="category in categories" 
                                    :key="category.id" 
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="newArticleDialogOpen = false">
                            Cancelar
                        </Button>
                        <Button type="submit">Criar e Associar</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';
import axios from 'axios';

// Configurar axios para incluir o token CSRF
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Adicionar uma configuração global para o axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const props = defineProps({
    invoice: {
        type: Object,
        required: true,
        default: () => ({
            items: [],
            supplier: {},
            validation_status: 'pending'
        })
    },
    articles: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    invalidItemsCount: {
        type: Number,
        default: 0
    }
});

const { toast } = useToast();
const associateDialogOpen = ref(false);
const newArticleDialogOpen = ref(false);
const currentItem = ref(null);
const isValidating = ref(false);
const isVerifying = ref(false);

const associateForm = ref({
    article_id: '',
    invoice_item_id: null
});

const newArticleForm = ref({
    name: '',
    code: '',
    description: '',
    category_id: '',
    price: '',
    invoice_item_id: null
});

const unassociatedItems = computed(() => {
    if (!props.invoice || !props.invoice.items) return [];
    return props.invoice.items.filter(item => !item.article_id);
});

const allItemsAssociated = computed(() => {
    return unassociatedItems.value.length === 0;
});

function openAssociateDialog(item) {
    currentItem.value = item;
    associateForm.value.invoice_item_id = item.id;
    associateForm.value.article_id = item.article_id || '';
    associateDialogOpen.value = true;
}

function openNewArticleDialog(item) {
    currentItem.value = item;
    newArticleForm.value = {
        name: item.description,
        code: '',
        description: item.description,
        category_id: '',
        price: item.unit_price || '',
        invoice_item_id: item.id
    };
    newArticleDialogOpen.value = true;
}

function submitAssociateForm() {
    router.post(route('invoice-items.associate-article', associateForm.value.invoice_item_id), {
        article_id: associateForm.value.article_id
    }, {
        onSuccess: () => {
            associateDialogOpen.value = false;
            toast.success("O item foi associado com sucesso ao artigo selecionado.");
        }
    });
}

function submitNewArticleForm() {
    // Validar os dados do formulário antes de enviar
    if (!newArticleForm.value.name || !newArticleForm.value.code || !newArticleForm.value.category_id) {
        showFeedback("Por favor, preencha todos os campos obrigatórios.", 'error');
        return;
    }
    
    // Usar o método router do Inertia em vez do axios diretamente
    router.post(route('invoice-items.create-and-associate', newArticleForm.value.invoice_item_id), {
        name: newArticleForm.value.name,
        code: newArticleForm.value.code,
        description: newArticleForm.value.description,
        category_id: newArticleForm.value.category_id,
        price: newArticleForm.value.price
    }, {
        onSuccess: () => {
            newArticleDialogOpen.value = false;
            toast.success("O novo artigo foi criado e associado ao item com sucesso.");
            // Recarregar a página após o sucesso
            window.location.reload();
        },
        onError: (errors) => {
            console.error("Erro ao criar e associar artigo:", errors);
            // Mostrar mensagem específica de erro se disponível
            if (errors && Object.keys(errors).length > 0) {
                const errorMsg = Object.values(errors).flat().join(", ");
                showFeedback(`Erro: ${errorMsg}`, 'error');
            } else {
                showFeedback("Ocorreu um erro ao criar o artigo. Verifique os dados e tente novamente.", 'error');
            }
        }
    });
}

function markAsValidated() {
    if (!allItemsAssociated.value) {
        const errorMsg = "Todos os itens devem estar associados a artigos antes de validar a fatura.";
        showFeedback(errorMsg, 'error');
        return;
    }

    // Prevenir múltiplos cliques
    if (isValidating.value) return;
    
    isValidating.value = true;
    console.log("Tentando validar a fatura ID:", props.invoice.id);
    showFeedback("Processando validação...", 'info');
    
    axios.post(route('invoices.mark-validated', props.invoice.id))
        .then(response => {
            console.log("Resposta do servidor:", response);
            showFeedback("Fatura validada com sucesso.", 'success');
            
            // Pequeno atraso antes do reload para permitir que o toast seja exibido
            setTimeout(() => {
                window.location.href = route('invoices.validate', props.invoice.id);
            }, 500);
        })
        .catch(error => {
            console.error("Erro ao validar a fatura:", error);
            showFeedback("Ocorreu um erro ao validar a fatura. Tente novamente.", 'error');
            isValidating.value = false;
        });
}

function markAsVerified() {
    // Prevenir múltiplos cliques
    if (isVerifying.value) return;
    
    isVerifying.value = true;
    console.log("Tentando verificar a fatura ID:", props.invoice.id);
    showFeedback("Processando verificação...", 'info');
    
    axios.post(route('invoices.mark-verified', props.invoice.id))
        .then(response => {
            console.log("Resposta do servidor:", response);
            showFeedback("Fatura verificada com sucesso.", 'success');
            
            // Pequeno atraso antes do reload para permitir que o toast seja exibido
            setTimeout(() => {
                window.location.href = route('invoices.show', props.invoice.id);
            }, 500);
        })
        .catch(error => {
            console.error("Erro ao verificar a fatura:", error);
            showFeedback("Ocorreu um erro ao verificar a fatura. Tente novamente.", 'error');
            isVerifying.value = false;
        });
}

// Função auxiliar para exibir mensagens de feedback
function showFeedback(message, type = 'success') {
    console.log(type === 'success' ? "✅ " : "❌ ", message);
    
    try {
        // Usar o toast nativo do Vue-Toast-Notification
        if (toast && typeof toast[type] === 'function') {
            toast[type](message);
            return;
        }
    } catch (e) {
        console.warn("Toast não disponível:", e);
    }
    
    // Apenas logar no console se o toast falhar, sem usar alert
    console.log(`Mensagem de feedback (${type}): ${message}`);
}

// Determinar o tipo de badge para o status de validação
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

// Traduzir status de validação para texto amigável
function getValidationStatusText(status) {
    const statusMap = {
        pending: 'Pendente de Validação',
        validated: 'Validado',
        verified: 'Verificado'
    };
    return statusMap[status] || status;
}

// Traduzir status para texto amigável
function getStatusText(status) {
    const statusMap = {
        pending: 'Pendente',
        paid: 'Pago',
        overdue: 'Atrasado',
        cancelled: 'Cancelado'
    };
    return statusMap[status] || status;
}

// Formatar data para exibição
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-PT');
}

// Formatar valor monetário
function formatCurrency(value) {
    if (value === null || value === undefined) return '';
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR'
    }).format(value);
}
</script> 