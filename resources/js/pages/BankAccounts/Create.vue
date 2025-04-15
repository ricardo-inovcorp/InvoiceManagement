<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nova Conta Bancária</h2>
        </template>

        <Head title="Nova Conta Bancária" />

        <div class="w-full py-4">
            <div class="mb-6">
                <Link :href="route('bank-accounts.index')">
                    <Button variant="outline">Voltar</Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Adicionar Conta Bancária</CardTitle>
                    <CardDescription>Preencha os dados da nova conta bancária</CardDescription>
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
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    auth: Object
});

const form = useForm({
    name: '',
    bank_name: '',
    iban: '',
    initial_balance: '0.00',
    notes: ''
});

function submit() {
    form.post(route('bank-accounts.store'));
}
</script> 