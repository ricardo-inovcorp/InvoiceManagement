<template>
    <AuthenticatedLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Novo Fornecedor</h2>
        </template>

        <Head title="Novo Fornecedor" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <div class="flex justify-between items-center">
                            <CardTitle>Informações do Fornecedor</CardTitle>
                            <Link :href="route('suppliers.index')">
                                <Button variant="outline">Voltar</Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Nome -->
                            <div>
                                <Label for="company_name">Nome</Label>
                                <Input
                                    id="company_name"
                                    v-model="form.company_name"
                                    type="text"
                                    required
                                />
                                <p v-if="form.errors.company_name" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.company_name }}
                                </p>
                            </div>

                            <!-- Documento -->
                            <div>
                                <Label for="document">NIF</Label>
                                <Input
                                    id="document"
                                    v-model="form.document"
                                    type="text"
                                    required
                                />
                                <p v-if="form.errors.document" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.document }}
                                </p>
                            </div>

                            <!-- Email -->
                            <div>
                                <Label for="email">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                />
                                <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <!-- Telefone -->
                            <div>
                                <Label for="phone">Telefone</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                />
                                <p v-if="form.errors.phone" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <!-- Morada -->
                            <div>
                                <Label for="address">Morada</Label>
                                <Textarea
                                    id="address"
                                    v-model="form.address"
                                    rows="3"
                                    required
                                />
                                <p v-if="form.errors.address" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.address }}
                                </p>
                            </div>

                            <!-- Localidade -->
                            <div>
                                <Label for="city">Localidade</Label>
                                <Input
                                    id="city"
                                    v-model="form.city"
                                    type="text"
                                    required
                                />
                                <p v-if="form.errors.city" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.city }}
                                </p>
                            </div>

                            <!-- Concelho -->
                            <div>
                                <Label for="county_id">Concelho</Label>
                                <select
                                    id="county_id"
                                    v-model="form.county_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="">Selecione um concelho</option>
                                    <option v-for="county in counties" :key="county.id" :value="county.id">
                                        {{ county.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.county_id" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.county_id }}
                                </p>
                            </div>

                            <!-- Distrito -->
                            <div>
                                <Label for="district_id">Distrito</Label>
                                <select
                                    id="district_id"
                                    v-model="form.district_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    required
                                >
                                    <option value="">Selecione um distrito</option>
                                    <option v-for="district in districts" :key="district.id" :value="district.id">
                                        {{ district.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.district_id" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.district_id }}
                                </p>
                            </div>

                            <!-- Código Postal -->
                            <div>
                                <Label for="zip_code">Código Postal</Label>
                                <Input
                                    id="zip_code"
                                    v-model="form.zip_code"
                                    type="text"
                                    placeholder="1234-567"
                                    required
                                />
                                <p v-if="form.errors.zip_code" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.zip_code }}
                                </p>
                            </div>

                            <!-- Setor de Atividade -->
                            <div>
                                <Label for="sector_id">Setor de Atividade</Label>
                                <select
                                    id="sector_id"
                                    v-model="form.sector_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">Selecione um setor</option>
                                    <option v-for="sector in sectors" :key="sector.id" :value="sector.id">
                                        {{ sector.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.sector_id" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.sector_id }}
                                </p>
                            </div>

                            <!-- Tipo de Organização -->
                            <div>
                                <Label for="organization_type_id">Tipo de Organização</Label>
                                <select
                                    id="organization_type_id"
                                    v-model="form.organization_type_id"
                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option value="">Selecione um tipo</option>
                                    <option v-for="type in organizationTypes" :key="type.id" :value="type.id">
                                        {{ type.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.organization_type_id" class="text-sm text-red-600 mt-1">
                                    {{ form.errors.organization_type_id }}
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

                            <!-- Botões -->
                            <div class="flex justify-end space-x-2">
                                <Link :href="route('suppliers.index')">
                                    <Button variant="outline" type="button">Cancelar</Button>
                                </Link>
                                <Button type="submit" :disabled="form.processing" @click="() => console.log('Botão clicado')">
                                    Criar Fornecedor
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

const props = defineProps({
    auth: Object,
    districts: Array,
    counties: Array,
    sectors: Array,
    organizationTypes: Array,
});

const form = useForm({
    company_name: '',
    document: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    county_id: '',
    district_id: '',
    zip_code: '',
    sector_id: '',
    organization_type_id: '',
    notes: '',
    active: true,
});

const submit = () => {
    console.log('Formulário enviado', form);
    form.post(route('suppliers.store'), {
        onSuccess: () => {
            form.reset();
            console.log('Sucesso!');
        },
        onError: (errors) => {
            console.error('Erros:', errors);
        }
    });
};
</script> 