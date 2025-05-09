<template>
    <Head title="Novo Fornecedor" />

    <AuthenticatedLayout :user="auth.user">
        <div class="py-12">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Novo Fornecedor</h1>
                    <Link :href="route('suppliers.index')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                        Voltar
                    </Link>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Nome -->
                        <div>
                            <Label for="company_name" class="text-gray-900 dark:text-white">Nome</Label>
                            <Input
                                id="company_name"
                                v-model="form.company_name"
                                type="text"
                                required
                                class="w-full mt-1 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            />
                            <p v-if="form.errors.company_name" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                {{ form.errors.company_name }}
                            </p>
                        </div>

                        <!-- Documento -->
                        <div>
                            <Label for="document" class="text-gray-900 dark:text-white">NIF</Label>
                            <div class="flex space-x-2">
                                <Input
                                    id="document"
                                    v-model="form.document"
                                    type="text"
                                    required
                                    class="flex-1 mt-1 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                                />
                                <button 
                                    type="button" 
                                    @click="fetchCompanyData"
                                    :disabled="!validateNif(form.document) || loading"
                                    class="px-4 py-2 bg-gray-100 text-gray-800 border border-gray-300 rounded-md hover:bg-gray-200 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600 disabled:opacity-50"
                                >
                                    <span v-if="loading" class="animate-spin mr-1">&#8635;</span>
                                    Consultar
                                </button>
                            </div>
                            <p v-if="form.errors.document" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                {{ form.errors.document }}
                            </p>
                            <p v-if="nifError" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                {{ nifError }}
                            </p>
                            <p v-if="successMessage" class="text-sm text-green-600 dark:text-green-400 mt-1">
                                {{ successMessage }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Email -->
                            <div>
                                <Label for="email" class="text-gray-900 dark:text-white">Email</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full mt-1 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                                />
                                <p v-if="form.errors.email" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <!-- Telefone -->
                            <div>
                                <Label for="phone" class="text-gray-900 dark:text-white">Telefone</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                    class="w-full mt-1 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                                />
                                <p v-if="form.errors.phone" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                    {{ form.errors.phone }}
                                </p>
                            </div>
                        </div>

                        <!-- Morada -->
                        <div>
                            <Label for="address" class="text-gray-900 dark:text-white">Morada</Label>
                            <Textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                required
                                class="w-full mt-1 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            />
                            <p v-if="form.errors.address" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                {{ form.errors.address }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Localidade -->
                            <div>
                                <Label for="city" class="text-gray-900 dark:text-white">Localidade</Label>
                                <Input
                                    id="city"
                                    v-model="form.city"
                                    type="text"
                                    required
                                    class="w-full mt-1 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                                />
                                <p v-if="form.errors.city" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                    {{ form.errors.city }}
                                </p>
                            </div>

                            <!-- Código Postal -->
                            <div>
                                <Label for="zip_code" class="text-gray-900 dark:text-white">Código Postal</Label>
                                <Input
                                    id="zip_code"
                                    v-model="form.zip_code"
                                    type="text"
                                    placeholder="1234-567"
                                    required
                                    class="w-full mt-1 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                                />
                                <p v-if="form.errors.zip_code" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                    {{ form.errors.zip_code }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Concelho -->
                            <div>
                                <Label for="county_id" class="text-gray-900 dark:text-white">Concelho</Label>
                                <select
                                    id="county_id"
                                    v-model="form.county_id"
                                    class="w-full mt-1 rounded-md border border-gray-300 dark:border-gray-700 px-3 py-2 dark:bg-gray-900 dark:text-gray-100"
                                    required
                                >
                                    <option value="">Selecione um concelho</option>
                                    <option v-for="county in counties" :key="county.id" :value="county.id">
                                        {{ county.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.county_id" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                    {{ form.errors.county_id }}
                                </p>
                            </div>

                            <!-- Distrito -->
                            <div>
                                <Label for="district_id" class="text-gray-900 dark:text-white">Distrito</Label>
                                <select
                                    id="district_id"
                                    v-model="form.district_id"
                                    class="w-full mt-1 rounded-md border border-gray-300 dark:border-gray-700 px-3 py-2 dark:bg-gray-900 dark:text-gray-100"
                                    required
                                >
                                    <option value="">Selecione um distrito</option>
                                    <option v-for="district in districts" :key="district.id" :value="district.id">
                                        {{ district.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.district_id" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                    {{ form.errors.district_id }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Setor de Atividade -->
                            <div>
                                <Label for="sector_id" class="text-gray-900 dark:text-white">Setor de Atividade</Label>
                                <select
                                    id="sector_id"
                                    v-model="form.sector_id"
                                    class="w-full mt-1 rounded-md border border-gray-300 dark:border-gray-700 px-3 py-2 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option value="">Selecione um setor</option>
                                    <option v-for="sector in sectors" :key="sector.id" :value="sector.id">
                                        {{ sector.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.sector_id" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                    {{ form.errors.sector_id }}
                                </p>
                            </div>

                            <!-- Tipo de Organização -->
                            <div>
                                <Label for="organization_type_id" class="text-gray-900 dark:text-white">Tipo de Organização</Label>
                                <select
                                    id="organization_type_id"
                                    v-model="form.organization_type_id"
                                    class="w-full mt-1 rounded-md border border-gray-300 dark:border-gray-700 px-3 py-2 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    <option value="">Selecione um tipo</option>
                                    <option v-for="type in organizationTypes" :key="type.id" :value="type.id">
                                        {{ type.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.organization_type_id" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                    {{ form.errors.organization_type_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Observações -->
                        <div>
                            <Label for="notes" class="text-gray-900 dark:text-white">Observações</Label>
                            <Textarea
                                id="notes"
                                v-model="form.notes"
                                rows="3"
                                class="w-full mt-1 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                            />
                            <p v-if="form.errors.notes" class="text-sm text-red-600 dark:text-red-400 mt-1">
                                {{ form.errors.notes }}
                            </p>
                        </div>

                        <!-- Botões -->
                        <div class="flex justify-end space-x-2">
                            <Link :href="route('suppliers.index')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                                Cancelar
                            </Link>
                            <button 
                                type="submit" 
                                :disabled="form.processing" 
                                class="px-4 py-2 bg-blue-500 text-white border border-blue-600 rounded-md hover:bg-blue-600 transition disabled:opacity-50 dark:bg-blue-700 dark:border-blue-800 dark:hover:bg-blue-800"
                            >
                                Criar Fornecedor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Textarea from '@/components/ui/textarea/Textarea.vue';
import { ref, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
    auth: Object,
    districts: Array,
    counties: Array,
    sectors: Array,
    organizationTypes: Array,
});

const loading = ref(false);
const nifError = ref(null);
const successMessage = ref(null);

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
            
            successMessage.value = 'Os dados da empresa foram preenchidos automaticamente.';
            
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