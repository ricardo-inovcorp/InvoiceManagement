<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';

const props = defineProps({
    auth: Object,
});

const toast = useToast();
const page = usePage();

const form = useForm({
    file: null,
});

const fileInput = ref(null);
const isUploading = ref(false);
const results = ref(null);
const errors = ref([]);

// Monitorar mensagens flash da sessão
watch(() => page.props.flash, (newValue) => {
    console.log('Flash atualizado no watcher:', newValue);
    
    // Exibir mensagem de sucesso do flash
    if (newValue?.success) {
        // Usar o tipo de toast apropriado com base nos resultados
        if (newValue?.results) {
            const results = newValue.results;
            if (results.imported > 0) {
                toast.success(newValue.success, { duration: 6000 });
            } else if (results.errors && results.errors.length > 0) {
                toast.warning(newValue.success, { duration: 6000 });
            } else if (results.duplicates > 0) {
                toast.info(newValue.success, { duration: 6000 });
            } else {
                toast.info(newValue.success, { duration: 5000 });
            }
        } else {
            toast.success(newValue.success, { duration: 5000 });
        }
    }
    
    // Atualizar os resultados para exibição na UI
    if (newValue?.results) {
        console.log('Resultados recebidos no watcher:', newValue.results);
        results.value = newValue.results;
    }
}, { deep: true, immediate: true });

// Monitorar erros do servidor
watch(() => page.props.errors, (newValue) => {
    if (newValue && Object.keys(newValue).length > 0) {
        errors.value = Object.values(newValue);
        errors.value.forEach(error => {
            toast.error(error);
        });
    }
}, { deep: true, immediate: true });

function handleFileChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    form.file = file;
}

function submit() {
    isUploading.value = true;
    results.value = null;
    errors.value = [];
    
    // Adicionar um timeout de segurança para resetar o estado de upload após 15 segundos
    const safetyTimeout = setTimeout(() => {
        if (isUploading.value) {
            isUploading.value = false;
            toast.error('A operação de importação expirou. Por favor, tente novamente.', {
                duration: 8000
            });
        }
    }, 15000);
    
    form.post(route('articles.import'), {
        forceFormData: true,
        preserveScroll: true,
        onStart: () => {
            isUploading.value = true;
        },
        onSuccess: (response) => {
            clearTimeout(safetyTimeout);
            console.log('Resposta de sucesso:', response);
            
            // Verificar se temos resultados ou mensagem na resposta
            const flashResults = response?.props?.flash?.results;
            const successMsg = response?.props?.flash?.success;
            
            if (flashResults) {
                results.value = flashResults;
                
                // Construir mensagem explícita
                let message = "";
                if (flashResults.imported > 0) {
                    message = `Importação concluída: ${flashResults.imported} artigos importados, ${flashResults.duplicates} ignorados`;
                    if (flashResults.errors && flashResults.errors.length > 0) {
                        message += `, ${flashResults.errors.length} erros encontrados`;
                    }
                    toast.success(message, { duration: 6000 });
                } else if (flashResults.errors && flashResults.errors.length > 0) {
                    message = `Importação com problemas: ${flashResults.imported} artigos importados, ${flashResults.duplicates} ignorados, ${flashResults.errors.length} erros encontrados`;
                    toast.warning(message, { duration: 6000 });
                } else if (flashResults.duplicates > 0) {
                    message = `Importação concluída: Todos os ${flashResults.duplicates} artigos já existiam no sistema`;
                    toast.info(message, { duration: 6000 });
                } else {
                    toast.info('Nenhum artigo importado. Verifique o arquivo e tente novamente.', { duration: 5000 });
                }
                
                // Redirecionar para a lista de artigos após um atraso para dar tempo de ver a mensagem
                setTimeout(() => {
                    window.location.href = route('articles.index');
                }, 3000);
            } else if (successMsg) {
                // Se temos apenas uma mensagem de sucesso
                toast.success(successMsg, { duration: 5000 });
                
                // Redirecionar para a lista de artigos
                setTimeout(() => {
                    window.location.href = route('articles.index');
                }, 3000);
            } else {
                // Caso não tenhamos informações específicas
                toast.info('Importação processada. Redirecionando para a lista de artigos...', { duration: 5000 });
                
                // Redirecionar para a lista de artigos
                setTimeout(() => {
                    window.location.href = route('articles.index');
                }, 2000);
            }
        },
        onError: (errors) => {
            clearTimeout(safetyTimeout);
            console.log('Erros na resposta:', errors);
            
            // Se não tivermos mensagens de erro específicas, mostrar uma genérica
            if (!Object.keys(errors).length) {
                toast.error('Ocorreu um erro durante a importação. Verifique o arquivo e tente novamente.', {
                    duration: 8000
                });
            }
        },
        onFinish: () => {
            // Garantir que o estado de upload seja resetado
            clearTimeout(safetyTimeout);
            isUploading.value = false;
        }
    });
}
</script>

<template>
    <Head title="Importar Artigos" />

    <AppLayout :user="auth.user">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Importar Artigos</h1>
                    <div>
                        <Link :href="route('articles.index')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition">
                            Voltar para Artigos
                        </Link>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">Instruções para Importação</h3>
                            <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-md">
                                <p class="mb-2">1. Faça o download do template de importação abaixo.</p>
                                <p class="mb-2">2. Preencha o arquivo com os dados dos artigos seguindo o formato indicado.</p>
                                <p class="mb-2">3. Envie o arquivo preenchido através do formulário abaixo.</p>
                                <p class="mb-6">4. Aguarde o processamento e verifique os resultados.</p>
                                
                                <div class="flex items-center justify-start">
                                    <a :href="route('articles.import.template')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition">
                                        Download do Template
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <form @submit.prevent="submit" class="mt-6">
                            <div class="mb-4">
                                <label for="file" class="block text-sm font-medium">Arquivo Excel (xlsx, xls) ou CSV</label>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    id="file"
                                    @change="handleFileChange"
                                    accept=".xlsx,.xls,.csv"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-900"
                                    required
                                />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tamanho máximo: 5MB</p>
                            </div>
                            
                            <div class="flex justify-end mt-6">
                                <button
                                    type="submit"
                                    :disabled="isUploading || !form.file"
                                    class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition"
                                >
                                    <span v-if="isUploading">A processar...</span>
                                    <span v-else>Importar Artigos</span>
                                </button>
                            </div>
                        </form>
                        
                        <!-- Resultados da importação -->
                        <div v-if="results" class="mt-8 border-t pt-6 border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold mb-4">Resultados da Importação</h3>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-md">
                                    <p class="text-sm text-blue-800 dark:text-blue-200">Total de Registros</p>
                                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ results.total }}</p>
                                </div>
                                
                                <div class="bg-green-100 dark:bg-green-900 p-4 rounded-md">
                                    <p class="text-sm text-green-800 dark:text-green-200">Importados com Sucesso</p>
                                    <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ results.imported }}</p>
                                </div>
                                
                                <div class="bg-yellow-100 dark:bg-yellow-900 p-4 rounded-md">
                                    <p class="text-sm text-yellow-800 dark:text-yellow-200">Duplicados (Ignorados)</p>
                                    <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ results.duplicates }}</p>
                                </div>
                                
                                <div class="bg-red-100 dark:bg-red-900 p-4 rounded-md">
                                    <p class="text-sm text-red-800 dark:text-red-200">Erros</p>
                                    <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ results.errors.length }}</p>
                                </div>
                            </div>
                            
                            <!-- Lista de erros, se houver -->
                            <div v-if="results.errors.length > 0" class="mt-6">
                                <h4 class="font-medium mb-2">Erros encontrados:</h4>
                                <ul class="bg-red-50 dark:bg-red-950 p-4 rounded-md list-disc list-inside">
                                    <li v-for="(error, index) in results.errors" :key="index" class="text-red-800 dark:text-red-300 mb-1">
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Mensagens de erro do servidor -->
                        <div v-if="errors.length > 0" class="mt-6">
                            <div class="bg-red-50 dark:bg-red-950 p-4 rounded-md">
                                <h4 class="font-medium text-red-800 dark:text-red-300 mb-2">Ocorreram erros:</h4>
                                <ul class="list-disc list-inside">
                                    <li v-for="(error, index) in errors" :key="index" class="text-red-800 dark:text-red-300">
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 