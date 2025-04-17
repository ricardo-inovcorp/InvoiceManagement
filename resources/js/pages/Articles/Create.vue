<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Label as InputLabel } from '@/components/ui/label';
import { Input as TextInput } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import axios from 'axios';

const props = defineProps({
    categories: Array,
    auth: Object,
    success: String,
    category: Object,
    subcategory: Object,
});

const form = useForm({
    code: '',
    name: '',
    description: '',
    price: '',
    category_id: null,
    subcategory_id: null,
    active: true,
});

// Forms para criar novas categorias e subcategorias
const newCategoryName = ref('');
const newCategoryDescription = ref('');
const newCategoryProcessing = ref(false);
const newCategoryErrors = ref({});

const newSubcategoryName = ref('');
const newSubcategoryDescription = ref('');
const newSubcategoryProcessing = ref(false);
const newSubcategoryErrors = ref({});

const subcategories = ref([]);
const showNewCategoryDialog = ref(false);
const showNewSubcategoryDialog = ref(false);
const categoriesList = ref(props.categories || []);
const formErrors = ref({});

// Inicializar com a nova categoria/subcategoria se foi adicionada anteriormente
if (props.category) {
    categoriesList.value.push(props.category);
    form.category_id = props.category.id;
}

if (props.subcategory && form.category_id === props.subcategory.category_id) {
    subcategories.value.push(props.subcategory);
    form.subcategory_id = props.subcategory.id;
}

watch(() => form.category_id, async (categoryId) => {
    if (!categoryId) {
        subcategories.value = [];
        form.subcategory_id = null;
        return;
    }
    
    try {
        const response = await fetch(route('categories.subcategories', categoryId));
        if (response.ok) {
            subcategories.value = await response.json();
        }
    } catch (error) {
        console.error('Erro ao carregar subcategorias:', error);
    }
});

// Atualiza o category_id do formulário de subcategoria sempre que mudar o category_id do formulário principal
watch(() => form.category_id, (categoryId) => {
    newSubcategoryName.value = '';
    newSubcategoryDescription.value = '';
});

function submit() {
    form.post(route('articles.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
}

function submitNewCategory() {
    newCategoryProcessing.value = true;
    
    axios.post(route('categories.store'), {
        name: newCategoryName.value,
        description: newCategoryDescription.value,
    }, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        // Adiciona a nova categoria à lista
        const newCategory = response.data.category;
        categoriesList.value.push(newCategory);
        
        // Seleciona a categoria recém-criada
        form.category_id = newCategory.id;
        
        // Fecha o diálogo e limpa o formulário
        showNewCategoryDialog.value = false;
        newCategoryName.value = '';
        newCategoryDescription.value = '';
        newCategoryErrors.value = {};
    })
    .catch(error => {
        if (error.response && error.response.status === 422) {
            newCategoryErrors.value = error.response.data.errors || {};
        } else {
            console.error('Erro ao criar categoria:', error);
        }
    })
    .finally(() => {
        newCategoryProcessing.value = false;
    });
}

function submitNewSubcategory() {
    if (!form.category_id) {
        return;
    }
    
    newSubcategoryProcessing.value = true;
    
    axios.post(route('subcategories.store'), {
        name: newSubcategoryName.value,
        description: newSubcategoryDescription.value,
        category_id: form.category_id,
    }, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        // Adiciona a nova subcategoria à lista
        const newSubcategory = response.data.subcategory;
        subcategories.value.push(newSubcategory);
        
        // Seleciona a subcategoria recém-criada
        form.subcategory_id = newSubcategory.id;
        
        // Fecha o diálogo e limpa o formulário
        showNewSubcategoryDialog.value = false;
        newSubcategoryName.value = '';
        newSubcategoryDescription.value = '';
        newSubcategoryErrors.value = {};
    })
    .catch(error => {
        if (error.response && error.response.status === 422) {
            newSubcategoryErrors.value = error.response.data.errors || {};
        } else {
            console.error('Erro ao criar subcategoria:', error);
        }
    })
    .finally(() => {
        newSubcategoryProcessing.value = false;
    });
}
</script>

<template>
    <Head title="Novo Artigo" />

    <AppLayout :user="auth.user">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Mensagem de sucesso -->
                <div v-if="success" class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ success }}
                </div>
                
                <div class="mb-6 flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Novo Artigo</h1>
                    <div>
                        <Link :href="route('articles.index')" class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 transition">
                            Cancelar
                        </Link>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Informações Básicas -->
                                <div>
                                    <h3 class="text-lg font-semibold mb-4 border-b pb-2 border-gray-200 dark:border-gray-700">Informações Básicas</h3>

                                    <div class="mb-4">
                                        <InputLabel for="code" value="Código" class="mb-1" />
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Código único para identificação do artigo</p>
                                        <TextInput
                                            id="code"
                                            v-model="form.code"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError :message="form.errors.code" class="mt-2" />
                                    </div>

                                    <div class="mb-4">
                                        <InputLabel for="name" value="Nome" class="mb-1" />
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Nome completo do artigo</p>
                                        <TextInput
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError :message="form.errors.name" class="mt-2" />
                                    </div>

                                    <div class="mb-4">
                                        <InputLabel for="price" value="Preço (€)" class="mb-1" />
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Valor do artigo em euros</p>
                                        <TextInput
                                            id="price"
                                            v-model="form.price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.price" class="mt-2" />
                                    </div>

                                    <!-- Estado do Artigo (Ativo/Inativo) -->
                                    <div class="mb-4">
                                        <h4 class="font-medium mb-2">Estado do Artigo</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Define se o artigo está disponível para uso no sistema</p>
                                        <div class="flex items-center gap-3 border p-4 rounded bg-gray-50 dark:bg-gray-700">
                                            <div class="flex items-center">
                                                <input
                                                    type="checkbox"
                                                    id="active"
                                                    v-model="form.active"
                                                    class="h-5 w-5 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                />
                                                <label for="active" class="ml-2 block text-sm font-medium">
                                                    {{ form.active ? 'Ativo' : 'Inativo' }}
                                                </label>
                                            </div>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Selecione a opção para marcar o artigo como ativo ou desmarque para inativo.
                                        </p>
                                    </div>
                                </div>

                                <!-- Categorização -->
                                <div>
                                    <h3 class="text-lg font-semibold mb-4 border-b pb-2 border-gray-200 dark:border-gray-700">Categorização</h3>

                                    <div class="mb-4">
                                        <div class="flex justify-between items-center mb-1">
                                            <InputLabel for="category_id" value="Categoria" />
                                            <button 
                                                type="button" 
                                                class="text-sm text-blue-600 hover:text-blue-800"
                                                @click="showNewCategoryDialog = true"
                                            >
                                                + Nova Categoria
                                            </button>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Grupo principal a que o artigo pertence</p>
                                        <select
                                            id="category_id"
                                            v-model="form.category_id"
                                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full"
                                        >
                                            <option :value="null">Selecione uma categoria</option>
                                            <option v-for="category in categoriesList" :key="category.id" :value="category.id">
                                                {{ category.name }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.category_id" class="mt-2" />
                                    </div>

                                    <div class="mb-4">
                                        <div class="flex justify-between items-center mb-1">
                                            <InputLabel for="subcategory_id" value="Subcategoria" />
                                            <button 
                                                type="button" 
                                                class="text-sm text-blue-600 hover:text-blue-800"
                                                @click="showNewSubcategoryDialog = true"
                                                :disabled="!form.category_id"
                                            >
                                                + Nova Subcategoria
                                            </button>
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Subgrupo específico dentro da categoria selecionada</p>
                                        <select
                                            id="subcategory_id"
                                            v-model="form.subcategory_id"
                                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full"
                                            :disabled="!form.category_id || subcategories.length === 0"
                                        >
                                            <option :value="null">{{ form.category_id ? (subcategories.length === 0 ? 'Nenhuma subcategoria disponível' : 'Selecione uma subcategoria') : 'Selecione uma categoria primeiro' }}</option>
                                            <option v-for="subcategory in subcategories" :key="subcategory.id" :value="subcategory.id">
                                                {{ subcategory.name }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.subcategory_id" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Descrição -->
                            <div class="mt-6">
                                <InputLabel for="description" value="Descrição" class="mb-1" />
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Informações detalhadas sobre o artigo (opcional)</p>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    class="mt-1 block w-full h-32"
                                />
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <div class="flex justify-end mt-6">
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-white text-gray-800 border border-gray-300 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing">A guardar...</span>
                                    <span v-else>Guardar Artigo</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
    
    <!-- Modal para criar nova categoria -->
    <Dialog :open="showNewCategoryDialog" @update:open="showNewCategoryDialog = $event">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Nova Categoria</DialogTitle>
            </DialogHeader>
            
            <form @submit.prevent="submitNewCategory" class="mt-4">
                <div class="mb-4">
                    <InputLabel for="new-category-name" value="Nome" class="mb-1" />
                    <TextInput
                        id="new-category-name"
                        v-model="newCategoryName"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <div v-if="newCategoryErrors.name" class="text-sm text-red-600 mt-2">
                        {{ newCategoryErrors.name[0] }}
                    </div>
                </div>
                
                <div class="mb-4">
                    <InputLabel for="new-category-description" value="Descrição (opcional)" class="mb-1" />
                    <Textarea
                        id="new-category-description"
                        v-model="newCategoryDescription"
                        class="mt-1 block w-full"
                    />
                    <div v-if="newCategoryErrors.description" class="text-sm text-red-600 mt-2">
                        {{ newCategoryErrors.description[0] }}
                    </div>
                </div>
                
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="showNewCategoryDialog = false"
                    >
                        Cancelar
                    </Button>
                    <Button
                        type="submit"
                        :disabled="newCategoryProcessing"
                    >
                        {{ newCategoryProcessing ? 'A guardar...' : 'Guardar' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
    
    <!-- Modal para criar nova subcategoria -->
    <Dialog :open="showNewSubcategoryDialog" @update:open="showNewSubcategoryDialog = $event">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Nova Subcategoria</DialogTitle>
            </DialogHeader>
            
            <form @submit.prevent="submitNewSubcategory" class="mt-4">
                <div class="mb-4">
                    <InputLabel for="new-subcategory-name" value="Nome" class="mb-1" />
                    <TextInput
                        id="new-subcategory-name"
                        v-model="newSubcategoryName"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <div v-if="newSubcategoryErrors.name" class="text-sm text-red-600 mt-2">
                        {{ newSubcategoryErrors.name[0] }}
                    </div>
                </div>
                
                <div class="mb-4">
                    <InputLabel for="new-subcategory-description" value="Descrição (opcional)" class="mb-1" />
                    <Textarea
                        id="new-subcategory-description"
                        v-model="newSubcategoryDescription"
                        class="mt-1 block w-full"
                    />
                    <div v-if="newSubcategoryErrors.description" class="text-sm text-red-600 mt-2">
                        {{ newSubcategoryErrors.description[0] }}
                    </div>
                </div>
                
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="showNewSubcategoryDialog = false"
                    >
                        Cancelar
                    </Button>
                    <Button
                        type="submit"
                        :disabled="newSubcategoryProcessing"
                    >
                        {{ newSubcategoryProcessing ? 'A guardar...' : 'Guardar' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template> 