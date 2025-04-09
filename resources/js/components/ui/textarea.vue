<template>
  <textarea
    v-bind="$attrs"
    class="border rounded p-2 w-full"
  ></textarea>
</template>

<script setup>
defineProps(['modelValue']);
</script> 