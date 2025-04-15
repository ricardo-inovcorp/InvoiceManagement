<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ isEditing ? 'Editar Conta' : 'Nova Conta' }}
            </h2>
        </template>

        <Head :title="isEditing ? `Editar Conta - ${form.name}` : 'Nova Conta'" />

        <div class="w-full py-4">
            <div class="flex items-center mb-6">
                <Button variant="outline" @click="$inertia.visit(isEditing ? route('bank-accounts.show', bankAccount.id) : route('bank-accounts.index'))" class="h-8">
                    <ChevronLeft class="h-4 w-4 mr-1" />
                    Voltar
                </Button>
                <h1 class="ml-4 text-2xl font-bold">{{ isEditing ? `Editar ${form.name}` : 'Nova Conta Bancária' }}</h1>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Informações da Conta</CardTitle>
                        <CardDescription>
                            Preencha os detalhes da conta bancária.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <!-- Nome da Conta -->
                                <div class="space-y-2">
                                    <Label for="name">Nome da Conta <span class="text-destructive">*</span></Label>
                                    <Input 
                                        id="name" 
                                        v-model="form.name" 
                                        placeholder="Ex: Conta Principal"
                                        :error="errors.name"
                                    />
                                    <div v-if="errors.name" class="text-sm text-destructive">
                                        {{ errors.name }}
                                    </div>
                                </div>

                                <!-- Nome do Banco -->
                                <div class="space-y-2">
                                    <Label for="bank_name">Banco <span class="text-destructive">*</span></Label>
                                    <Input 
                                        id="bank_name" 
                                        v-model="form.bank_name" 
                                        placeholder="Ex: Millennium BCP"
                                        :error="errors.bank_name"
                                    />
                                    <div v-if="errors.bank_name" class="text-sm text-destructive">
                                        {{ errors.bank_name }}
                                    </div>
                                </div>

                                <!-- IBAN -->
                                <div class="space-y-2">
                                    <Label for="iban">IBAN <span class="text-destructive">*</span></Label>
                                    <Input 
                                        id="iban" 
                                        v-model="form.iban" 
                                        placeholder="PT50 0000 0000 0000 0000 0000 0"
                                        class="font-mono"
                                        :error="errors.iban"
                                    />
                                    <div v-if="errors.iban" class="text-sm text-destructive">
                                        {{ errors.iban }}
                                    </div>
                                </div>

                                <!-- Saldo Inicial -->
                                <div class="space-y-2">
                                    <Label for="initial_balance">Saldo Inicial <span class="text-destructive">*</span></Label>
                                    <Input 
                                        id="initial_balance" 
                                        v-model="form.initial_balance" 
                                        type="number" 
                                        step="0.01"
                                        placeholder="0.00"
                                        :error="errors.initial_balance"
                                    />
                                    <div v-if="errors.initial_balance" class="text-sm text-destructive">
                                        {{ errors.initial_balance }}
                                    </div>
                                </div>

                                <!-- Status da Conta -->
                                <div class="space-y-2 md:col-span-2">
                                    <div class="flex items-center space-x-2">
                                        <Checkbox 
                                            id="is_active" 
                                            v-model:checked="form.is_active" 
                                        />
                                        <Label for="is_active">Conta Ativa</Label>
                                    </div>
                                    <div v-if="errors.is_active" class="text-sm text-destructive">
                                        {{ errors.is_active }}
                                    </div>
                                </div>

                                <!-- Notas -->
                                <div class="space-y-2 md:col-span-2">
                                    <Label for="notes">Notas</Label>
                                    <Textarea 
                                        id="notes" 
                                        v-model="form.notes" 
                                        placeholder="Observações adicionais sobre esta conta"
                                        rows="4"
                                        :error="errors.notes"
                                    />
                                    <div v-if="errors.notes" class="text-sm text-destructive">
                                        {{ errors.notes }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <Button 
                                    type="button" 
                                    variant="outline" 
                                    @click="$inertia.visit(isEditing ? route('bank-accounts.show', bankAccount.id) : route('bank-accounts.index'))"
                                >
                                    Cancelar
                                </Button>
                                <Button 
                                    type="submit" 
                                    :disabled="processing"
                                >
                                    <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                                    {{ isEditing ? 'Salvar Alterações' : 'Criar Conta' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Informações</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-medium mb-1">Gestão de Contas</h4>
                                <p class="text-sm text-muted-foreground">
                                    As contas bancárias são essenciais para o controle financeiro do seu negócio. Aqui você pode registrar todas as suas contas e acompanhar seus saldos.
                                </p>
                            </div>
                            <div>
                                <h4 class="font-medium mb-1">Formato do IBAN</h4>
                                <p class="text-sm text-muted-foreground">
                                    O IBAN (International Bank Account Number) para Portugal segue o formato: PT50 seguido de 21 dígitos. Ex: PT50 0000 0000 0000 0000 0000 0
                                </p>
                            </div>
                            <div>
                                <h4 class="font-medium mb-1">Saldo Inicial</h4>
                                <p class="text-sm text-muted-foreground">
                                    O saldo inicial é o valor que a conta tinha no momento do cadastro no sistema. Este valor será a base para o cálculo do saldo atual.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ChevronLeft, Loader2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle
} from '@/components/ui/card';

const props = defineProps({
    auth: Object,
    bankAccount: Object,
    errors: Object
});

const isEditing = computed(() => !!props.bankAccount);

const form = isEditing.value
    ? { 
        name: props.bankAccount.name,
        bank_name: props.bankAccount.bank_name,
        iban: props.bankAccount.iban,
        initial_balance: props.bankAccount.initial_balance,
        is_active: props.bankAccount.is_active,
        notes: props.bankAccount.notes
    }
    : {
        name: '',
        bank_name: '',
        iban: '',
        initial_balance: 0,
        is_active: true,
        notes: ''
    };

const processing = ref(false);

function submit() {
    processing.value = true;

    if (isEditing.value) {
        router.put(route('bank-accounts.update', props.bankAccount.id), form, {
            onSuccess: () => {
                processing.value = false;
            },
            onError: () => {
                processing.value = false;
            }
        });
    } else {
        router.post(route('bank-accounts.store'), form, {
            onSuccess: () => {
                processing.value = false;
            },
            onError: () => {
                processing.value = false;
            }
        });
    }
}
</script> 