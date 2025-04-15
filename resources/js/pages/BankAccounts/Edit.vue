<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Conta Bancária</h2>
        </template>

        <Head title="Editar Conta Bancária" />

        <div class="w-full py-4">
            <div class="mb-6">
                <Link :href="route('bank-accounts.show', bankAccount.id)">
                    <Button variant="outline">Voltar</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Editar Conta Bancária</CardTitle>
                    <CardDescription>Atualize os dados da conta bancária</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <Label for="name">Nome da Conta <span class="text-red-500">*</span></Label>
                                    <Input 
                                        id="name" 
                                        v-model="form.name" 
                                        required
                                        placeholder="Ex: Conta Principal"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div>
                                    <Label for="bank_name">Banco <span class="text-red-500">*</span></Label>
                                    <Input 
                                        id="bank_name" 
                                        v-model="form.bank_name" 
                                        required
                                        placeholder="Ex: Banco Comercial Português"
                                    />
                                    <InputError :message="form.errors.bank_name" />
                                </div>

                                <div>
                                    <Label for="iban">IBAN <span class="text-red-500">*</span></Label>
                                    <Input 
                                        id="iban" 
                                        v-model="form.iban" 
                                        required
                                        placeholder="Ex: PT50000000000000000000000"
                                        class="font-mono"
                                    />
                                    <InputError :message="form.errors.iban" />
                                </div>

                                <div>
                                    <div class="flex items-center space-x-2">
                                        <input 
                                            type="checkbox" 
                                            id="is_active" 
                                            v-model="form.is_active"
                                            class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                        />
                                        <Label for="is_active">Conta Activa</Label>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Desmarque esta opção para arquivar a conta
                                    </p>
                                    <InputError :message="form.errors.is_active" />
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <Label for="initial_balance">Saldo Inicial (€) <span class="text-red-500">*</span></Label>
                                    <Input 
                                        id="initial_balance" 
                                        v-model="form.initial_balance" 
                                        type="number"
                                        step="0.01"
                                        required
                                        placeholder="0.00"
                                    />
                                    <p class="text-sm text-amber-600 mt-1" v-if="bankAccount.current_balance !== parseFloat(form.initial_balance)">
                                        <AlertTriangle class="h-4 w-4 inline-block mr-1" />
                                        Alterar o saldo inicial recalculará o saldo atual.
                                    </p>
                                    <InputError :message="form.errors.initial_balance" />
                                </div>

                                <div>
                                    <Label for="notes">Observações</Label>
                                    <Textarea 
                                        id="notes" 
                                        v-model="form.notes" 
                                        placeholder="Informações adicionais sobre a conta"
                                    />
                                    <InputError :message="form.errors.notes" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Salvando...' : 'Salvar' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';

const props = defineProps({
    auth: Object,
    bankAccount: Object
});

// Adicionar log para debug
console.log('bankAccount recebido:', props.bankAccount);

const form = useForm({
    name: props.bankAccount.name,
    bank_name: props.bankAccount.bank_name,
    iban: props.bankAccount.iban,
    initial_balance: props.bankAccount.initial_balance.toString(),
    notes: props.bankAccount.notes || '',
    is_active: props.bankAccount.is_active
});

// Log do formulário inicializado
console.log('Formulário inicializado:', form);

function submit() {
    console.log('Enviando formulário original:', form);
    
    // Garantir que is_active seja um booleano explícito
    form.is_active = form.is_active === true;
    
    console.log('Formulário com is_active corrigido:', form);
    
    form.put(route('bank-accounts.update', props.bankAccount.id), {
        onSuccess: () => {
            console.log('Formulário enviado com sucesso!');
        },
        onError: (errors) => {
            console.error('Erro ao enviar formulário:', errors);
        }
    });
}
</script> 