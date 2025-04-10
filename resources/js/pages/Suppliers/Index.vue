<template>
    <AppLayout :user="auth.user">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Fornecedores</h2>
        </template>

        <Head title="Fornecedores" />

        <div class="w-full py-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium">Fornecedores</h3>
            </div>

            <!-- Barra de pesquisa -->
            <div class="mb-6">
                <form @submit.prevent="search">
                    <div class="flex space-x-2">
                        <div class="md:w-1/2 lg:w-1/3">
                            <Input 
                                type="text" 
                                placeholder="Pesquisar por nome da empresa ou NIF..." 
                                v-model="form.search"
                                class="w-full"
                            />
                        </div>
                        <Button type="submit">Pesquisar</Button>
                        <Button 
                            type="button" 
                            variant="outline" 
                            @click="resetSearch"
                            v-if="form.search"
                        >
                            Limpar
                        </Button>
                        <Link :href="route('suppliers.create')">
                            <Button>Novo Fornecedor</Button>
                        </Link>
                    </div>
                </form>
            </div>

            <Card class="w-full">
                <CardContent class="overflow-auto p-0">
                    <div class="w-full overflow-x-auto">
                        <Table class="w-full">
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="whitespace-nowrap">Empresa</TableHead>
                                    <TableHead class="whitespace-nowrap">NIF</TableHead>
                                    <TableHead class="whitespace-nowrap">Email</TableHead>
                                    <TableHead class="whitespace-nowrap">Telefone</TableHead>
                                    <TableHead class="whitespace-nowrap">Morada</TableHead>
                                    <TableHead class="whitespace-nowrap">Cidade</TableHead>
                                    <TableHead class="whitespace-nowrap">Concelho</TableHead>
                                    <TableHead class="whitespace-nowrap">Distrito</TableHead>
                                    <TableHead class="whitespace-nowrap">Código Postal</TableHead>
                                    <TableHead class="whitespace-nowrap">Status</TableHead>
                                    <TableHead class="whitespace-nowrap">Notas</TableHead>
                                    <TableHead class="whitespace-nowrap">Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="supplier in suppliers.data" :key="supplier.id">
                                    <TableCell class="whitespace-nowrap">{{ supplier.company_name }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ supplier.document }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ supplier.email }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ supplier.phone }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ supplier.address }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ supplier.city }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ supplier.county_id }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ supplier.district_id }}</TableCell>
                                    <TableCell class="whitespace-nowrap">{{ supplier.zip_code }}</TableCell>
                                    <TableCell class="whitespace-nowrap">
                                        <Badge :variant="supplier.active ? 'success' : 'destructive'">
                                            {{ supplier.active ? 'Ativo' : 'Inativo' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="max-w-xs truncate" :title="supplier.notes || 'No note to show'">
                                        <template v-if="supplier.notes">
                                            {{ supplier.notes }}
                                        </template>
                                        <template v-else>
                                            No note to show
                                        </template>
                                    </TableCell>
                                    <TableCell class="whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <Link :href="route('suppliers.edit', supplier.id)">
                                                <Button variant="outline" size="sm">
                                                    Editar
                                                </Button>
                                            </Link>
                                            <Link :href="route('suppliers.show', supplier.id)">
                                                <Button variant="outline" size="sm">
                                                    Detalhes
                                                </Button>
                                            </Link>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Paginação -->
                    <div class="mt-4 pl-4 pb-4 flex justify-start">
                        <Pagination :links="suppliers.links" />
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
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
            search: ''
        })
    }
});

const form = reactive({
    search: props.filters.search || '',
});

function search() {
    router.get(route('suppliers.index'), {
        search: form.search,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function resetSearch() {
    form.search = '';
    router.get(route('suppliers.index'), {}, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<style scoped>
/* Força a tabela a ocupar toda a largura disponível */
:deep(.table) {
    width: 100%;
    table-layout: auto;
}

:deep(.sidebar-inset) {
    max-width: 100% !important;
    padding: 0 !important;
}

:deep(.pagination) {
    justify-content: flex-start !important;
}
</style> 