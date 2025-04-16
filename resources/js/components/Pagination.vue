<template>
  <nav v-if="links.length > 3" class="flex justify-between pagination">
    <button
      v-for="link in links"
      :key="link.url"
      :disabled="!link.url"
      @click="goTo(link.url)"
      v-html="link.label"
      class="px-3 py-1 border rounded pagination-link"
      :class="{
        'pagination-link-active bg-gray-200 dark:bg-blue-800 dark:text-white': link.active, 
        'text-gray-500 dark:text-gray-400': !link.url,
        'hover:bg-gray-100 dark:hover:bg-gray-700': link.url && !link.active
      }"
    ></button>
  </nav>
</template>

<script setup>
const props = defineProps({
  links: {
    type: Array,
    required: true,
  },
});

const goTo = (url) => {
  if (url) {
    window.location.href = url;
  }
};
</script>

<style>
.pagination-link-active {
  font-weight: bold;
}

/* Para o modo escuro */
.dark .pagination-link-active {
  background-color: #374151 !important;
  color: white !important;
  border-color: #374151 !important;
}
</style> 