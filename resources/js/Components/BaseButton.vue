<script setup>
/**
 * BaseButton - Reusable button component with multiple variants
 * @example
 * <BaseButton variant="primary" size="md" :loading="isSubmitting">Submit</BaseButton>
 */
import { computed } from 'vue';

const props = defineProps({
    /** Button variant style */
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'danger', 'outline', 'ghost'].includes(value),
    },
    /** Button size */
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    /** HTML button type */
    type: {
        type: String,
        default: 'button',
    },
    /** Disabled state */
    disabled: {
        type: Boolean,
        default: false,
    },
    /** Loading state with spinner */
    loading: {
        type: Boolean,
        default: false,
    },
    /** Full width button */
    block: {
        type: Boolean,
        default: false,
    },
});

const variantClasses = {
    primary: 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-600/25 focus:ring-blue-500',
    secondary: 'bg-slate-600 hover:bg-slate-700 text-white shadow-sm focus:ring-slate-500',
    danger: 'bg-red-600 hover:bg-red-700 text-white shadow-sm shadow-red-600/25 focus:ring-red-500',
    outline: 'bg-transparent border-2 border-slate-300 text-slate-700 hover:bg-slate-50 hover:border-slate-400 focus:ring-slate-500',
    ghost: 'bg-transparent text-slate-600 hover:bg-slate-100 hover:text-slate-800 focus:ring-slate-400',
};

const sizeClasses = {
    sm: 'px-3 py-1.5 text-xs rounded-md gap-1.5',
    md: 'px-4 py-2 text-sm rounded-lg gap-2',
    lg: 'px-6 py-3 text-base rounded-lg gap-2',
};

const isDisabled = computed(() => props.disabled || props.loading);

const buttonClasses = computed(() => [
    // Base styles
    'inline-flex items-center justify-center font-medium transition-all duration-200',
    'focus:outline-none focus:ring-2 focus:ring-offset-2',
    // Size
    sizeClasses[props.size],
    // Full width
    props.block ? 'w-full' : '',
    // States
    isDisabled.value 
        ? 'opacity-50 cursor-not-allowed' 
        : 'cursor-pointer active:scale-[0.98]',
    // Variant (only if not disabled)
    !isDisabled.value ? variantClasses[props.variant] : 'bg-slate-300 text-slate-500',
]);
</script>

<template>
    <button 
        :type="type" 
        :disabled="isDisabled" 
        :class="buttonClasses"
    >
        <!-- Loading Spinner -->
        <svg 
            v-if="loading" 
            class="animate-spin -ml-1 h-4 w-4" 
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24"
        >
            <circle 
                class="opacity-25" 
                cx="12" 
                cy="12" 
                r="10" 
                stroke="currentColor" 
                stroke-width="4"
            />
            <path 
                class="opacity-75" 
                fill="currentColor" 
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>

        <!-- Left Icon Slot -->
        <slot name="icon-left" />

        <!-- Button Content -->
        <slot />

        <!-- Right Icon Slot -->
        <slot name="icon-right" />
    </button>
</template>
