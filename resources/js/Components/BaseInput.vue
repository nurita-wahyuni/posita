<script setup>
/**
 * BaseInput - Modern input component with floating label and error handling
 * @example
 * <BaseInput v-model="email" label="Email" type="email" :error="form.errors.email" />
 */
import { ref, computed, useAttrs } from 'vue';

const props = defineProps({
    /** Input label (floating) */
    label: {
        type: String,
        default: '',
    },
    /** Input type */
    type: {
        type: String,
        default: 'text',
    },
    /** Error message */
    error: {
        type: String,
        default: '',
    },
    /** Placeholder text */
    placeholder: {
        type: String,
        default: ' ',
    },
    /** Disabled state */
    disabled: {
        type: Boolean,
        default: false,
    },
    /** Required field */
    required: {
        type: Boolean,
        default: false,
    },
});

const model = defineModel({
    type: [String, Number],
    default: '',
});

const attrs = useAttrs();
const input = ref(null);
const isFocused = ref(false);

const hasValue = computed(() => model.value !== '' && model.value !== null && model.value !== undefined);
const isFloating = computed(() => isFocused.value || hasValue.value);

const inputClasses = computed(() => [
    'peer w-full px-4 pt-5 pb-2 text-sm text-slate-900 bg-white border rounded-lg',
    'transition-all duration-200 outline-none',
    'placeholder-transparent',
    props.error 
        ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-500/20' 
        : 'border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 hover:border-slate-400',
    props.disabled ? 'bg-slate-50 cursor-not-allowed opacity-60' : '',
]);

const labelClasses = computed(() => [
    'absolute left-4 transition-all duration-200 pointer-events-none',
    isFloating.value
        ? 'top-1.5 text-[10px] font-medium'
        : 'top-1/2 -translate-y-1/2 text-sm',
    props.error
        ? 'text-red-500'
        : isFocused.value ? 'text-blue-600' : 'text-slate-500',
]);

const focus = () => {
    input.value?.focus();
};

defineExpose({ focus });
</script>

<template>
    <div class="relative">
        <!-- Input Field -->
        <input
            ref="input"
            v-model="model"
            :type="type"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :class="inputClasses"
            v-bind="attrs"
            @focus="isFocused = true"
            @blur="isFocused = false"
        />
        
        <!-- Floating Label -->
        <label 
            v-if="label"
            :class="labelClasses"
            @click="focus"
        >
            {{ label }}
            <span v-if="required" class="text-red-500 ml-0.5">*</span>
        </label>

        <!-- Error Message -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-1"
        >
            <p 
                v-if="error" 
                class="mt-1.5 text-xs text-red-500 flex items-center gap-1"
            >
                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ error }}
            </p>
        </Transition>
    </div>
</template>
