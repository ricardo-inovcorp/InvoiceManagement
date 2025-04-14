<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps({
    article: Object,
    auth: Object,
});
</script>

<template>
    <Head title="Detalhes do Artigo" />

    <AppLayout :user="auth.user">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Detalhes do Artigo</h1>
                    <div class="flex space-x-2">
                        <Link :href="route('articles.index')" class="px-3 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition">
                            Voltar
                        </Link>
                        <Link :href="route('articles.edit', article.id)" class="px-3 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition">
                            Editar
                        </Link>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-6">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full" :class="[
                                article.active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                            ]">
                                {{ article.active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold mb-4 border-b pb-2 border-gray-200 dark:border-gray-700">Informações Básicas</h3>
                                
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Código</p>
                                    <p class="font-medium">{{ article.code }}</p>
                                </div>
                                
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Nome</p>
                                    <p class="font-medium">{{ article.name }}</p>
                                </div>
                                
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Preço</p>
                                    <p class="font-medium">{{ Number(article.price).toFixed(2) }} €</p>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold mb-4 border-b pb-2 border-gray-200 dark:border-gray-700">Categorização</h3>
                                
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Categoria</p>
                                    <p class="font-medium">{{ article.category?.name || 'Não definida' }}</p>
                                </div>
                                
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Subcategoria</p>
                                    <p class="font-medium">{{ article.subcategory?.name || 'Não definida' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 border-t pt-6 border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold mb-4">Descrição</h3>
                            <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-md">
                                <p class="whitespace-pre-wrap">{{ article.description || 'Nenhuma descrição disponível.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 