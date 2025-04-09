<template>
    <select
        :id="id"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <option v-if="placeholder" value="" disabled selected>{{ placeholder }}</option>
        <option
            v-for="option in options"
            :key="getOptionValue(option)"
            :value="getOptionValue(option)"
        >
            {{ getOptionLabel(option) }}
        </option>
    </select>
</template>

<script setup>
const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    modelValue: {
        type: [String, Number],
        default: '',
    },
    options: {
        type: Array,
        required: true,
    },
    optionLabel: {
        type: String,
        default: 'label',
    },
    optionValue: {
        type: String,
        default: 'value',
    },
    placeholder: {
        type: String,
        default: '',
    },
});

defineEmits(['update:modelValue']);

const getOptionValue = (option) => {
    return typeof option === 'object' ? option[props.optionValue] : option;
};

const getOptionLabel = (option) => {
    return typeof option === 'object' ? option[props.optionLabel] : option;
};
</script> 