<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Fornecedor</h2>
        </template>

        <Head :title="`Editar Fornecedor - ${supplier.company_name || supplier.name}`" />

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
                                    <Label for="company_name">Nome</Label>
                                    <Input
                                        id="company_name"
                                        v-model="form.company_name"
                                        type="text"
                                        required
                                    />
                                    <div v-if="form.errors.company_name" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.company_name }}
                                    </div>
                                </div>

                                <div>
                                    <Label for="document">NIF</Label>
                                    <div class="flex space-x-2">
                                        <Input
                                            id="document"
                                            v-model="form.document"
                                            type="text"
                                            required
                                            class="flex-1"
                                        />
                                        <Button 
                                            type="button" 
                                            @click="fetchCompanyData"
                                            :disabled="!validateNif(form.document) || loading"
                                            variant="secondary"
                                        >
                                            <span v-if="loading" class="animate-spin mr-1">&#8635;</span>
                                            Consultar
                                        </Button>
                                    </div>
                                    <div v-if="form.errors.document" class="text-sm text-red-600 mt-1">
                                        {{ form.errors.document }}
                                    </div>
                                    <div v-if="nifError" class="text-sm text-red-600 mt-1">
                                        {{ nifError }}
                                    </div>
                                    <div v-if="successMessage" class="text-sm text-green-600 mt-1">
                                        {{ successMessage }}
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
                                        <div v-if="form.errors.district_id" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.district_id }}
                                        </div>
                                    </div>

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
                                        <div v-if="form.errors.sector_id" class="text-sm text-red-600 mt-1">
                                            {{ form.errors.sector_id }}
                                        </div>
                                    </div>

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
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ref, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
    auth: Object,
    supplier: Object,
    districts: Array,
    counties: Array,
    sectors: Array,
    organizationTypes: Array,
});

const loading = ref(false);
const nifError = ref(null);
const successMessage = ref(null);

// Convertemos os IDs para números para garantir compatibilidade
const form = useForm({
    company_name: props.supplier.company_name || props.supplier.name,
    document: props.supplier.document,
    email: props.supplier.email,
    phone: props.supplier.phone,
    address: props.supplier.address,
    city: props.supplier.city,
    county_id: props.supplier.county_id ? Number(props.supplier.county_id) : '',
    district_id: props.supplier.district_id ? Number(props.supplier.district_id) : '',
    zip_code: props.supplier.zip_code,
    sector_id: props.supplier.sector_id ? Number(props.supplier.sector_id) : '',
    organization_type_id: props.supplier.organization_type_id ? Number(props.supplier.organization_type_id) : '',
    notes: props.supplier.notes,
    active: Boolean(props.supplier.active),
    _method: 'PUT',
});

// Função para validar o NIF
const validateNif = (nif) => {
    return /^[0-9]{9}$/.test(nif);
};

// Função para buscar dados da empresa pelo NIF
const fetchCompanyData = async () => {
    if (!validateNif(form.document)) {
        nifError.value = 'O NIF deve conter exatamente 9 dígitos.';
        return;
    }
    
    try {
        loading.value = true;
        nifError.value = null;
        successMessage.value = null;
        
        const response = await axios.post('/api/nif/lookup', {
            nif: form.document
        });
        
        console.log('Resposta da API NIF:', response.data);
        
        if (response.data.success) {
            const data = response.data.data;
            console.log('Dados recebidos:', data);
            
            // Criar um objeto com os dados atualizados
            const updates = {};
            
            if (data.company_name) updates.company_name = data.company_name;
            if (data.email) updates.email = data.email;
            if (data.phone) updates.phone = data.phone;
            if (data.address) updates.address = data.address;
            if (data.city) updates.city = data.city;
            if (data.zip_code) {
                // Garantir o formato correto do código postal (0000-000)
                const zipPattern = /^(\d{4})-?(\d{3})$/;
                if (zipPattern.test(data.zip_code)) {
                    updates.zip_code = data.zip_code;
                } else if (/^\d{4}$/.test(data.zip_code) && data.zip_code.length === 4) {
                    // Se for apenas 4 dígitos, adiciona -000
                    updates.zip_code = `${data.zip_code}-000`;
                }
            }
            
            // Atualizar o formulário com os novos dados
            Object.assign(form, updates);
            
            // Buscar IDs correspondentes para distrito e concelho
            await findDistrictAndCounty(data.district, data.county);
            
            successMessage.value = 'Os dados da empresa foram atualizados com sucesso.';
            
            // Forçar uma atualização de todos os componentes
            nextTick(() => {
                console.log('Formulário atualizado:', form);
            });
        } else {
            nifError.value = response.data.message || 'Não foi possível encontrar dados para este NIF.';
        }
    } catch (error) {
        console.error('Erro ao buscar dados do NIF:', error);
        nifError.value = error.response?.data?.message || 'Ocorreu um erro ao consultar o NIF.';
    } finally {
        loading.value = false;
    }
};

// Função para encontrar IDs de distrito e concelho
const findDistrictAndCounty = async (districtName, countyName) => {
    console.log('Buscando distrito/concelho:', { districtName, countyName });
    console.log('Distritos disponíveis:', props.districts);
    console.log('Concelhos disponíveis:', props.counties);
    
    if (districtName && props.districts) {
        // Primeiro tenta correspondência exata
        let district = props.districts.find(d => 
            d.name.toLowerCase() === districtName.toLowerCase());
        
        // Se não encontrar, tenta correspondência parcial
        if (!district) {
            district = props.districts.find(d => 
                districtName.toLowerCase().includes(d.name.toLowerCase()) || 
                d.name.toLowerCase().includes(districtName.toLowerCase()));
        }
        
        if (district) {
            console.log('Distrito encontrado:', district);
            form.district_id = district.id;
        } else {
            console.log('Nenhum distrito correspondente encontrado');
        }
    }
    
    if (countyName && props.counties) {
        // Primeiro tenta correspondência exata
        let county = props.counties.find(c => 
            c.name.toLowerCase() === countyName.toLowerCase());
        
        // Se não encontrar, tenta correspondência parcial
        if (!county) {
            county = props.counties.find(c => 
                countyName.toLowerCase().includes(c.name.toLowerCase()) || 
                c.name.toLowerCase().includes(countyName.toLowerCase()));
        }
        
        if (county) {
            console.log('Concelho encontrado:', county);
            form.county_id = county.id;
        } else {
            console.log('Nenhum concelho correspondente encontrado');
        }
    }
};

function submit() {
    form.post(route('suppliers.update', props.supplier.id));
}
</script> 