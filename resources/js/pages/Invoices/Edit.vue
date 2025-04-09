<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Fatura</h2>
        </template>

        <Head :title="`Editar Fatura - ${invoice.invoice_number}`" />

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
                                    :options="suppliers"
                                    option-label="name"
                                    option-value="id"
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

                            <!-- Valor Total -->
                            <div>
                                <Label for="total_amount">Valor Total</Label>
                                <Input
                                    id="total_amount"
                                    v-model="form.total_amount"
                                    type="number"
                                    step="0.01"
                                    required
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
                                    v-model="form.tax_amount"
                                    type="number"
                                    step="0.01"
                                    required
                                />
                                <p v-if="form.errors.tax_amount" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.tax_amount }}
                                </p>
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
                                        { label: 'Boleto', value: 'boleto' },
                                        { label: 'Cartão de Crédito', value: 'credit_card' },
                                        { label: 'Transferência', value: 'transfer' },
                                        { label: 'Dinheiro', value: 'cash' },
                                    ]"
                                    option-label="label"
                                    option-value="value"
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
                                <Label for="file">Arquivo da Fatura</Label>
                                <Input
                                    id="file"
                                    type="file"
                                    @change="handleFileChange"
                                    accept=".pdf"
                                />
                                <p v-if="form.errors.file" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.file }}
                                </p>
                                <div v-if="invoice.file_path" class="mt-2">
                                    <p class="text-sm">Arquivo atual: <a :href="invoice.file_path" target="_blank" class="text-blue-600 hover:underline">Ver arquivo</a></p>
                                </div>
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

                            <!-- Botões -->
                            <div class="flex justify-end space-x-2">
                                <Link :href="route('invoices.index')">
                                    <Button variant="outline" type="button">Cancelar</Button>
                                </Link>
                                <Button type="submit" :disabled="form.processing">
                                    Salvar Alterações
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
import AppLayout from '@/Layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const props = defineProps({
    auth: Object,
    invoice: {
        type: Object,
        required: true,
    },
    suppliers: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    supplier_id: props.invoice.supplier_id,
    invoice_number: props.invoice.invoice_number,
    issue_date: props.invoice.issue_date,
    due_date: props.invoice.due_date,
    total_amount: props.invoice.total_amount,
    tax_amount: props.invoice.tax_amount,
    status: props.invoice.status,
    payment_method: props.invoice.payment_method,
    payment_date: props.invoice.payment_date,
    file: null,
    notes: props.invoice.notes,
    _method: 'PUT',
});

const handleFileChange = (event) => {
    form.file = event.target.files[0];
};

const submit = () => {
    form.post(route('invoices.update', props.invoice.id));
};
</script> 