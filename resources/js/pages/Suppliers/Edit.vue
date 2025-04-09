<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Fornecedor</h2>
        </template>

        <Head :title="`Editar Fornecedor - ${supplier.name}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <CardHeader>
                        <div class="flex justify-between items-center">
                            <CardTitle>Dados do Fornecedor</CardTitle>
                            <Link :href="route('suppliers.index')">
                                <Button variant="outline">Voltar</Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Dados do Fornecedor -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <Label for="name">Nome</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <Label for="document">NIF</Label>
                                    <Input
                                        id="document"
                                        v-model="form.document"
                                        type="text"
                                        required
                                    />
                                    <div v-if="form.errors.document" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.document }}
                                    </div>
                                </div>

                                <div>
                                    <Label for="email">Email</Label>
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                    />
                                    <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.email }}
                                    </div>
                                </div>

                                <div>
                                    <Label for="phone">Telefone</Label>
                                    <Input
                                        id="phone"
                                        v-model="form.phone"
                                        type="text"
                                        required
                                    />
                                    <div v-if="form.errors.phone" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.phone }}
                                    </div>
                                </div>
                            </div>

                            <!-- Endereço -->
                            <div>
                                <h3 class="text-lg font-medium mb-4">Endereço</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="md:col-span-2">
                                        <Label for="address">Endereço</Label>
                                        <Input
                                            id="address"
                                            v-model="form.address"
                                            type="text"
                                            required
                                        />
                                        <div v-if="form.errors.address" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.address }}
                                        </div>
                                    </div>

                                    <div>
                                        <Label for="city">Cidade</Label>
                                        <Input
                                            id="city"
                                            v-model="form.city"
                                            type="text"
                                            required
                                        />
                                        <div v-if="form.errors.city" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.city }}
                                        </div>
                                    </div>

                                    <div>
                                        <Label for="zip_code">Código Postal</Label>
                                        <Input
                                            id="zip_code"
                                            v-model="form.zip_code"
                                            type="text"
                                            required
                                        />
                                        <div v-if="form.errors.zip_code" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.zip_code }}
                                        </div>
                                    </div>

                                    <div>
                                        <Label for="district_id">Distrito</Label>
                                        <Select
                                            id="district_id"
                                            v-model="form.district_id"
                                            required
                                        >
                                            <option value="">Selecione um distrito</option>
                                            <option v-for="district in districts" :key="district.id" :value="district.id">
                                                {{ district.name }}
                                            </option>
                                        </Select>
                                        <div v-if="form.errors.district_id" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.district_id }}
                                        </div>
                                    </div>

                                    <div>
                                        <Label for="county_id">Concelho</Label>
                                        <Select
                                            id="county_id"
                                            v-model="form.county_id"
                                            required
                                        >
                                            <option value="">Selecione um concelho</option>
                                            <option v-for="county in counties" :key="county.id" :value="county.id">
                                                {{ county.name }}
                                            </option>
                                        </Select>
                                        <div v-if="form.errors.county_id" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.county_id }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informações Adicionais -->
                            <div>
                                <h3 class="text-lg font-medium mb-4">Informações Adicionais</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label for="sector_id">Setor de Atividade</Label>
                                        <Select
                                            id="sector_id"
                                            v-model="form.sector_id"
                                        >
                                            <option value="">Selecione um setor</option>
                                            <option v-for="sector in sectors" :key="sector.id" :value="sector.id">
                                                {{ sector.name }}
                                            </option>
                                        </Select>
                                        <div v-if="form.errors.sector_id" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.sector_id }}
                                        </div>
                                    </div>

                                    <div>
                                        <Label for="organization_type_id">Tipo de Organização</Label>
                                        <Select
                                            id="organization_type_id"
                                            v-model="form.organization_type_id"
                                        >
                                            <option value="">Selecione um tipo</option>
                                            <option v-for="type in organizationTypes" :key="type.id" :value="type.id">
                                                {{ type.name }}
                                            </option>
                                        </Select>
                                        <div v-if="form.errors.organization_type_id" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.organization_type_id }}
                                        </div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <Label for="notes">Observações</Label>
                                        <Textarea
                                            id="notes"
                                            v-model="form.notes"
                                            rows="3"
                                        />
                                        <div v-if="form.errors.notes" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.notes }}
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex items-center">
                                            <input
                                                id="active"
                                                v-model="form.active"
                                                type="checkbox"
                                                class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                            />
                                            <Label for="active" class="ml-2">Ativo</Label>
                                        </div>
                                        <div v-if="form.errors.active" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.active }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="flex justify-end space-x-2">
                                <Link :href="route('suppliers.index')">
                                    <Button variant="outline" type="button">Cancelar</Button>
                                </Link>
                                <Button type="submit" :disabled="form.processing">Salvar Alterações</Button>
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
    supplier: Object,
    districts: Array,
    counties: Array,
    sectors: Array,
    organizationTypes: Array,
});

const form = useForm({
    name: props.supplier.name,
    document: props.supplier.document,
    email: props.supplier.email,
    phone: props.supplier.phone,
    address: props.supplier.address,
    city: props.supplier.city,
    county_id: props.supplier.county_id,
    state: props.supplier.state,
    district_id: props.supplier.district_id,
    zip_code: props.supplier.zip_code,
    sector_id: props.supplier.sector_id,
    organization_type_id: props.supplier.organization_type_id,
    notes: props.supplier.notes,
    active: props.supplier.active,
    _method: 'PUT',
});

function submit() {
    form.post(route('suppliers.update', props.supplier.id));
}
</script> 