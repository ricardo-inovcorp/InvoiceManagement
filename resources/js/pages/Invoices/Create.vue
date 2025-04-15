<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nova Fatura</h2>
        </template>

        <Head title="Nova Fatura" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <div class="flex justify-between items-center">
                            <CardTitle>Informações da Fatura</CardTitle>
                            <Link :href="route('invoices.index')">
                                <Button variant="outline">Voltar</Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Fornecedor -->
                            <div>
                                <Label for="supplier_id">Fornecedor</Label>
                                <Select
                                    id="supplier_id"
                                    v-model="form.supplier_id"
                                    :options="suppliers.map(s => ({ value: s.id, label: s.company_name }))"
                                    :error="form.errors.supplier_id"
                                />
                                <p v-if="form.errors.supplier_id" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.supplier_id }}
                                </p>
                            </div>

                            <!-- Número da Fatura -->
                            <div>
                                <Label for="invoice_number">Número da Fatura</Label>
                                <Input
                                    id="invoice_number"
                                    v-model="form.invoice_number"
                                    type="text"
                                    required
                                />
                                <p v-if="form.errors.invoice_number" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.invoice_number }}
                                </p>
                            </div>

                            <!-- Data de Emissão -->
                            <div>
                                <Label for="issue_date">Data de Emissão</Label>
                                <Input
                                    id="issue_date"
                                    v-model="form.issue_date"
                                    type="date"
                                    required
                                />
                                <p v-if="form.errors.issue_date" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.issue_date }}
                                </p>
                            </div>

                            <!-- Data de Vencimento -->
                            <div>
                                <Label for="due_date">Data de Vencimento</Label>
                                <Input
                                    id="due_date"
                                    v-model="form.due_date"
                                    type="date"
                                    required
                                />
                                <p v-if="form.errors.due_date" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.due_date }}
                                </p>
                            </div>

                            <!-- Valor da Fatura (alterado de "Valor Total") -->
                            <div>
                                <Label for="total_amount">Valor da Fatura</Label>
                                <Input
                                    id="total_amount"
                                    :value="form.total_amount ? `€ ${form.total_amount}` : '€ 0,00'"
                                    type="text"
                                    required
                                    readonly
                                />
                                <p v-if="form.errors.total_amount" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.total_amount }}
                                </p>
                            </div>

                            <!-- Valor do Imposto -->
                            <div>
                                <Label for="tax_amount">Valor do Imposto</Label>
                                <Input
                                    id="tax_amount"
                                    :value="form.tax_amount ? `€ ${form.tax_amount}` : '€ 0,00'"
                                    type="text"
                                    required
                                    readonly
                                />
                                <p v-if="form.errors.tax_amount" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.tax_amount }}
                                </p>
                            </div>

                            <!-- Total da Fatura + Impostos -->
                            <div>
                                <Label for="grand_total">Total (Valor da Fatura + Impostos)</Label>
                                <Input
                                    id="grand_total"
                                    :value="calculateGrandTotal"
                                    class="font-medium text-base"
                                    type="text"
                                    readonly
                                />
                            </div>

                            <!-- Status -->
                            <div>
                                <Label for="status">Status</Label>
                                <Select
                                    id="status"
                                    v-model="form.status"
                                    :options="[
                                        { label: 'Pendente', value: 'pending' },
                                        { label: 'Pago', value: 'paid' },
                                        { label: 'Atrasado', value: 'overdue' },
                                        { label: 'Cancelado', value: 'cancelled' },
                                    ]"
                                    option-label="label"
                                    option-value="value"
                                />
                                <p v-if="form.errors.status" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.status }}
                                </p>
                            </div>

                            <!-- Método de Pagamento -->
                            <div>
                                <Label for="payment_method">Método de Pagamento</Label>
                                <Select
                                    id="payment_method"
                                    v-model="form.payment_method"
                                    :options="[
                                        { value: 'credit_card', label: 'Cartão de Crédito' },
                                        { value: 'bank_transfer', label: 'Transferência Bancária' },
                                        { value: 'multibanco', label: 'Referência Multibanco' },
                                        { value: 'cash', label: 'Dinheiro' },
                                        { value: 'other', label: 'Outro' }
                                    ]"
                                    :error="form.errors.payment_method"
                                />
                                <p v-if="form.errors.payment_method" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.payment_method }}
                                </p>
                            </div>

                            <!-- Data do Pagamento -->
                            <div>
                                <Label for="payment_date">Data do Pagamento</Label>
                                <Input
                                    id="payment_date"
                                    v-model="form.payment_date"
                                    type="date"
                                />
                                <p v-if="form.errors.payment_date" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.payment_date }}
                                </p>
                            </div>

                            <!-- Arquivo -->
                            <div>
                                <Label for="file">Ficheiro da Fatura</Label>
                                <Input
                                    id="file"
                                    type="file"
                                    @change="handleFileChange"
                                    accept=".pdf"
                                />
                                <p v-if="form.errors.file" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.file }}
                                </p>
                            </div>

                            <!-- Observações -->
                            <div>
                                <Label for="notes">Observações</Label>
                                <Textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                />
                                <p v-if="form.errors.notes" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.notes }}
                                </p>
                            </div>

                            <!-- Itens da Fatura -->
                            <div class="border rounded-md p-4 space-y-4">
                                <h3 class="font-medium text-lg">Itens da Fatura</h3>
                                
                                <!-- Lista de itens -->
                                <div v-if="items.length > 0" class="mb-4">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Descrição</TableHead>
                                                <TableHead>Quantidade</TableHead>
                                                <TableHead>Preço Unitário</TableHead>
                                                <TableHead>Taxa de Imposto (%)</TableHead>
                                                <TableHead>Valor do Imposto</TableHead>
                                                <TableHead>Total</TableHead>
                                                <TableHead>Ações</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="(item, index) in items" :key="index">
                                                <TableCell>{{ item.description }}</TableCell>
                                                <TableCell>{{ item.quantity }}</TableCell>
                                                <TableCell>{{ formatCurrency(item.unit_price) }}</TableCell>
                                                <TableCell>{{ item.tax_rate }}%</TableCell>
                                                <TableCell>{{ formatCurrency(item.tax_amount) }}</TableCell>
                                                <TableCell>{{ formatCurrency(item.total) }}</TableCell>
                                                <TableCell>
                                                    <Button 
                                                        variant="destructive" 
                                                        size="sm" 
                                                        @click="removeItem(index)">
                                                        Remover
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                                
                                <!-- Formulário para adicionar item -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <Label for="item_input_type">Tipo de Entrada</Label>
                                        <Select
                                            id="item_input_type"
                                            v-model="itemInputType"
                                            :options="[
                                                { value: 'manual', label: 'Descrição Manual' },
                                                { value: 'article', label: 'Selecionar Artigo' },
                                            ]"
                                        />
                                    </div>
                                    
                                    <!-- Entrada manual de descrição -->
                                    <div v-if="itemInputType === 'manual'">
                                        <Label for="item_description_manual">Descrição</Label>
                                        <Input
                                            id="item_description_manual"
                                            v-model="currentItem.description"
                                            type="text"
                                            placeholder="Ex: 5 Resmas de Papel A4"
                                        />
                                    </div>
                                    
                                    <!-- Seleção de artigo -->
                                    <div v-if="itemInputType === 'article'">
                                        <Label for="item_description">Artigo</Label>
                                        <Select
                                            id="item_description"
                                            v-model="selectedArticle"
                                            :options="articles.map(a => ({ value: a.id, label: a.code + ' - ' + a.name }))"
                                            @update:model-value="handleArticleSelect"
                                        />
                                    </div>
                                    
                                    <div>
                                        <Label for="item_quantity">Quantidade</Label>
                                        <Input
                                            id="item_quantity"
                                            v-model="currentItem.quantity"
                                            type="number"
                                            min="1"
                                            @input="calculateItemTotals"
                                        />
                                    </div>
                                    <div>
                                        <Label for="item_unit_price">Preço Unitário</Label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2.5">€</span>
                                            <Input
                                                id="item_unit_price"
                                                v-model="currentItem.unit_price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="pl-8"
                                                @input="calculateItemTotals"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <Label for="item_tax_rate">Taxa de Imposto (%)</Label>
                                        <Input
                                            id="item_tax_rate"
                                            v-model="currentItem.tax_rate"
                                            type="number"
                                            min="0"
                                            @input="calculateItemTotals"
                                        />
                                    </div>
                                    <div>
                                        <Label for="item_tax_amount">Valor do Imposto</Label>
                                        <Input
                                            id="item_tax_amount"
                                            :value="currentItem.tax_amount ? `€ ${currentItem.tax_amount}` : '€ 0,00'"
                                            type="text"
                                            readonly
                                        />
                                    </div>
                                    <div>
                                        <Label for="item_total">Total</Label>
                                        <Input
                                            id="item_total"
                                            :value="currentItem.total ? `€ ${currentItem.total}` : '€ 0,00'"
                                            type="text"
                                            readonly
                                        />
                                    </div>
                                </div>
                                
                                <!-- Botão para adicionar item -->
                                <div class="mt-4">
                                    <Button 
                                        type="button" 
                                        @click="addItem" 
                                        :disabled="!isItemValid"
                                        variant="outline">
                                        Adicionar Item
                                    </Button>
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="flex justify-end space-x-2">
                                <Link :href="route('invoices.index')">
                                    <Button variant="outline" type="button">Cancelar</Button>
                                </Link>
                                <Button type="submit" :disabled="form.processing">
                                    Criar Fatura
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    auth: Object,
    suppliers: {
        type: Array,
        required: true,
    },
    articles: {
        type: Array,
        required: true,
    }
});

const items = ref([]);
const currentItem = ref({
    description: '',
    quantity: 1,
    unit_price: 0,
    tax_rate: 23, // IVA padrão em Portugal
    tax_amount: 0,
    total: 0
});

const selectedArticle = ref(null);
const itemInputType = ref('manual');

const form = useForm({
    supplier_id: '',
    invoice_number: '',
    issue_date: '',
    due_date: '',
    total_amount: '0',
    tax_amount: '0',
    status: 'pending',
    payment_method: '',
    payment_date: '',
    file: null,
    notes: '',
    items: []
});

const isItemValid = computed(() => {
    const hasValidQuantityAndPrice = currentItem.value.quantity > 0 && currentItem.value.unit_price > 0;
    
    // Se for entrada manual, a descrição deve estar preenchida
    if (itemInputType.value === 'manual') {
        return currentItem.value.description && hasValidQuantityAndPrice;
    }
    
    // Se for seleção de artigo, deve haver um artigo selecionado
    return selectedArticle.value && hasValidQuantityAndPrice;
});

const calculateGrandTotal = computed(() => {
    const totalAmount = parseFloat(form.total_amount) || 0;
    const taxAmount = parseFloat(form.tax_amount) || 0;
    return formatCurrency(totalAmount + taxAmount);
});

function calculateItemTotals() {
    // Certifique-se de que os valores são números
    const quantity = Number(currentItem.value.quantity) || 0;
    const unitPrice = Number(currentItem.value.unit_price) || 0;
    const taxRate = Number(currentItem.value.tax_rate) || 0;
    
    // Calcular o valor base (sem imposto)
    const baseAmount = quantity * unitPrice;
    
    // Calcular o valor do imposto
    const taxAmount = baseAmount * (taxRate / 100);
    
    // Calcular o total
    const total = baseAmount + taxAmount;
    
    // Atualizar os valores no item atual
    currentItem.value.tax_amount = taxAmount.toFixed(2);
    currentItem.value.total = total.toFixed(2);
}

function addItem() {
    if (!isItemValid.value) return;
    
    // Calcular os totais antes de adicionar
    calculateItemTotals();
    
    // Preparar o item para adicionar com ou sem article_id
    const newItem = {...currentItem.value};
    
    // Se a seleção for por artigo, adicionar o article_id
    if (itemInputType.value === 'article' && selectedArticle.value) {
        newItem.article_id = selectedArticle.value;
    }
    
    // Adicionar o item à lista
    items.value.push(newItem);
    
    // Atualizar os totais da fatura
    updateInvoiceTotals();
    
    // Limpar o formulário do item atual
    selectedArticle.value = null;
    currentItem.value = {
        description: '',
        quantity: 1,
        unit_price: 0,
        tax_rate: 23,
        tax_amount: 0,
        total: 0
    };
}

function removeItem(index) {
    items.value.splice(index, 1);
    updateInvoiceTotals();
}

function updateInvoiceTotals() {
    // Calcular totais da fatura
    let totalTaxAmount = 0;
    let totalAmount = 0;
    
    items.value.forEach(item => {
        totalTaxAmount += Number(item.tax_amount);
        totalAmount += Number(item.total);
    });
    
    // Atualizar o formulário
    form.tax_amount = totalTaxAmount.toFixed(2);
    form.total_amount = totalAmount.toFixed(2);
}

function formatCurrency(value) {
    // Forçar símbolo € antes do valor
    return '€ ' + new Intl.NumberFormat('pt-PT', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
}

const handleFileChange = (event) => {
    form.file = event.target.files[0];
};

const submit = () => {
    // Adicionar os itens ao formulário antes de enviar
    form.items = items.value;
    
    form.post(route('invoices.store'), {
        onSuccess: () => {
            form.reset();
            items.value = [];
        },
    });
};

// Função para atualizar a descrição e preço quando um artigo for selecionado
function handleArticleSelect(articleId) {
    console.log('Artigo selecionado:', articleId);
    
    const article = props.articles.find(a => a.id === parseInt(articleId));
    console.log('Dados do artigo:', article);
    
    if (article) {
        // Atualizar a descrição
        currentItem.value.description = `${article.code} - ${article.name}`;
        
        // Garantir que o preço seja um número válido e atualizar diretamente
        const price = typeof article.price === 'number' ? article.price : parseFloat(article.price || 0);
        console.log('Preço obtido do artigo:', price, 'Tipo:', typeof price);
        
        // Atualizar o preço unitário
        currentItem.value.unit_price = price;
        
        // Verificar se a atualização ocorreu corretamente
        console.log('Preço atualizado para:', currentItem.value.unit_price);
        
        // Recalcular totais
        calculateItemTotals();
    }
}

// Inicializar cálculos
calculateItemTotals();
</script> 