<template>
    <span
        :class="[
            'inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold',
            variantClasses[variant],
        ]"
    >
        <slot />
    </span>
</template>

<script setup>
const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'secondary', 'success', 'warning', 'destructive'].includes(value),
    },
});

const variantClasses = {
    default: 'bg-primary text-primary-foreground',
    secondary: 'bg-secondary text-secondary-foreground',
    success: 'bg-green-100 text-green-800',
    warning: 'bg-yellow-100 text-yellow-800',
    destructive: 'bg-red-100 text-red-800',
};
</script> 