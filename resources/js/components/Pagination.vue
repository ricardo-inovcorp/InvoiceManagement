<template>
  <nav v-if="links.length > 3" class="flex justify-between">
    <button
      v-for="link in links"
      :key="link.url"
      :disabled="!link.url"
      @click="goTo(link.url)"
      v-html="link.label"
      class="px-3 py-1 border rounded"
      :class="{'bg-gray-200': link.active, 'text-gray-500': !link.url}"
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