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
    // Primary Action -> Uses --primary (Green in Light, Violet in Dark)
    primary: 'bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm shadow-emerald-500/20 dark:shadow-violet-500/20 focus:ring-primary',
    // Accent/Brand -> Uses --accent (Orange in Light, Green in Dark)
    brand: 'bg-accent hover:bg-accent/90 text-accent-foreground shadow-sm focus:ring-accent', 
    warning: 'bg-amber-500 hover:bg-amber-600 text-white shadow-sm focus:ring-amber-500',
    // Secondary -> Slate/Gray
    secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80 shadow-sm focus:ring-slate-500',
    // Danger -> Red
    danger: 'bg-destructive hover:bg-destructive/90 text-destructive-foreground shadow-sm focus:ring-destructive',
    // Outline -> Bordered
    outline: 'bg-transparent border border-input hover:bg-accent hover:text-accent-foreground focus:ring-primary',
    // Ghost -> Transparent
    ghost: 'bg-transparent hover:bg-muted focus:ring-slate-400',
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
