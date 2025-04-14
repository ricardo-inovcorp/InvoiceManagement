<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';

const props = defineProps({
    articles: Object,
    categories: Array,
    filters: Object,
    auth: Object,
});

const searchQuery = ref(props.filters.search || '');
const categoryId = ref(props.filters.category_id || null);
const activeFilter = ref(props.filters.active !== undefined ? props.filters.active : null);

const toast = useToast();

// Exibir mensagem de sucesso, se houver
if (window.flash?.success) {
    toast.success(window.flash.success);
}

function applyFilters() {
    const filters = {};
    
    if (searchQuery.value) {
        filters.search = searchQuery.value;
    }
    
    if (categoryId.value) {
        filters.category_id = categoryId.value;
    }
    
    if (activeFilter.value !== null) {
        filters.active = activeFilter.value;
    }
    
    return filters;
}

function clearFilters() {
    searchQuery.value = '';
    categoryId.value = null;
    activeFilter.value = null;
    
    // Redirecionar para a página base sem filtros
    window.location = route('articles.index');
}
</script>

<template>
    <Head title="Artigos" />

    <AppLayout :user="auth.user">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Artigos</h1>
                    <div class="flex space-x-4">
                        <Link :href="route('articles.import.show')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition">
                            Importar Artigos
                        </Link>
                        <Link :href="route('articles.create')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition">
                            Novo Artigo
                        </Link>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Filtros -->
                        <div class="mb-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-md">
                            <h3 class="text-lg font-medium mb-3">Filtros</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label for="search" class="block text-sm font-medium mb-1">Procurar</label>
                                    <input
                                        type="text"
                                        id="search"
                                        v-model="searchQuery"
                                        placeholder="Código, nome ou descrição..."
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-900"
                                    />
                                </div>
                                
                                <div>
                                    <label for="category" class="block text-sm font-medium mb-1">Categoria</label>
                                    <select
                                        id="category"
                                        v-model="categoryId"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-900"
                                    >
                                        <option :value="null">Todas as categorias</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="active" class="block text-sm font-medium mb-1">Estado</label>
                                    <select
                                        id="active"
                                        v-model="activeFilter"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-900"
                                    >
                                        <option :value="null">Todos</option>
                                        <option :value="1">Ativos</option>
                                        <option :value="0">Inativos</option>
                                    </select>
                                </div>
                                
                                <div class="flex items-end space-x-2">
                                    <Link
                                        :href="route('articles.index', applyFilters())"
                                        class="flex-1 px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition text-center"
                                    >
                                        Aplicar Filtros
                                    </Link>
                                    <button
                                        @click.prevent="clearFilters"
                                        class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition text-center"
                                    >
                                        Limpar
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Lista de artigos -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Código
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Nome
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Preço
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Categoria
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
                                    <tr v-for="article in articles.data" :key="article.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            {{ article.code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ article.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ Number(article.price).toFixed(2) }} €
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div>{{ article.category?.name || '-' }}</div>
                                            <div v-if="article.subcategory" class="text-gray-500 dark:text-gray-400 text-xs">
                                                {{ article.subcategory.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span
                                                :class="[
                                                    'px-2 py-1 text-xs rounded-full',
                                                    article.active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                ]"
                                            >
                                                {{ article.active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Link
                                                    :href="route('articles.show', article.id)"
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    Ver
                                                </Link>
                                                <Link
                                                    :href="route('articles.edit', article.id)"
                                                    class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                                >
                                                    Editar
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="articles.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Nenhum artigo encontrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação -->
                        <div class="mt-6">
                            <Pagination :links="articles.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 