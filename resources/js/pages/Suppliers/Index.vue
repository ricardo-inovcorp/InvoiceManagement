<template>
    <Head title="Fornecedores" />

    <AppLayout :user="auth.user">
        <div class="py-12">
            <div class="w-full px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Fornecedores</h1>
                    <div class="flex space-x-4">
                        <Link :href="route('suppliers.create')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                            Novo Fornecedor
                        </Link>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Filtros -->
                        <div class="mb-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-md">
                            <h3 class="text-lg font-medium mb-3">Filtros</h3>
                            <form @submit.prevent="search">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div>
                                        <label for="search" class="block text-sm font-medium mb-1">Procurar</label>
                                        <Input 
                                            type="text" 
                                            id="search"
                                            placeholder="Pesquisar por nome da empresa ou NIF..." 
                                            v-model="form.search"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-900"
                                        />
                                    </div>
                                    
                                    <div>
                                        <label for="active" class="block text-sm font-medium mb-1">Estado</label>
                                        <select
                                            id="active"
                                            v-model="form.active"
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-900"
                                        >
                                            <option :value="null">Todos</option>
                                            <option :value="1">Activos</option>
                                            <option :value="0">Inactivos</option>
                                        </select>
                                    </div>
                                    
                                    <div class="flex items-end space-x-2">
                                        <Button 
                                            type="submit" 
                                            class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600"
                                        >
                                            Aplicar Filtros
                                        </Button>
                                        <Button 
                                            type="button" 
                                            variant="outline" 
                                            @click="resetSearch" 
                                            v-if="form.search || form.active !== null"
                                            class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600"
                                        >
                                            Limpar
                                        </Button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Lista de fornecedores -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Empresa
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            NIF
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Telefone
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Cidade
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Estado
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="supplier in suppliers.data" :key="supplier.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            {{ supplier.company_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ supplier.document }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ supplier.email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ supplier.phone }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ supplier.city }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span
                                                :class="[
                                                    'px-2 py-1 text-xs rounded-full',
                                                    supplier.active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                ]"
                                            >
                                                {{ supplier.active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Link
                                                    :href="route('suppliers.show', supplier.id)"
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    Ver
                                                </Link>
                                                <Link
                                                    :href="route('suppliers.edit', supplier.id)"
                                                    class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                                >
                                                    Editar
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="suppliers.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Nenhum fornecedor encontrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação -->
                        <div class="mt-6 flex justify-end">
                            <Pagination :links="suppliers.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    auth: Object,
    suppliers: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            active: null
        })
    }
});

const form = reactive({
    search: props.filters.search || '',
    active: props.filters.active !== undefined ? props.filters.active : null
});

function search() {
    const params = {};
    
    if (form.search) {
        params.search = form.search;
    }
    
    if (form.active !== null) {
        params.active = form.active;
    }
    
    router.get(route('suppliers.index'), params, {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetSearch() {
    form.search = '';
    form.active = null;
    router.get(route('suppliers.index'), {}, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<style scoped>
/* Estilos específicos para a página de fornecedores, se necessário */
</style> 