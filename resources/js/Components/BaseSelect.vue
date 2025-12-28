<script setup>
/**
 * BaseSelect - Select dropdown with floating label and error handling
 * @example
 * <BaseSelect v-model="role" label="Role" :options="roles" :error="form.errors.role" />
 */
import { ref, computed, useAttrs } from 'vue';

const props = defineProps({
    /** Select label (floating) */
    label: {
        type: String,
        default: '',
    },
    /** Options array - can be array of strings or objects with value/label */
    options: {
        type: Array,
        default: () => [],
    },
    /** Key for option value (when using object options) */
    valueKey: {
        type: String,
        default: 'value',
    },
    /** Key for option label (when using object options) */
    labelKey: {
        type: String,
        default: 'label',
    },
    /** Placeholder text */
    placeholder: {
        type: String,
        default: 'Select an option',
    },
    /** Error message */
    error: {
        type: String,
        default: '',
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
    type: [String, Number, null],
    default: '',
});

const attrs = useAttrs();
const isFocused = ref(false);

const hasValue = computed(() => model.value !== '' && model.value !== null && model.value !== undefined);
const isFloating = computed(() => isFocused.value || hasValue.value);

const getOptionValue = (option) => {
    if (typeof option === 'object' && option !== null) {
        return option[props.valueKey];
    }
    return option;
};

const getOptionLabel = (option) => {
    if (typeof option === 'object' && option !== null) {
        return option[props.labelKey];
    }
    return option;
};

const selectClasses = computed(() => [
    'peer w-full px-4 pt-5 pb-2 text-sm text-slate-900 bg-white border rounded-lg',
    'transition-all duration-200 outline-none appearance-none cursor-pointer',
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
</script>

<template>
    <div class="relative">
        <!-- Select Field -->
        <select
            v-model="model"
            :disabled="disabled"
            :required="required"
            :class="selectClasses"
            v-bind="attrs"
            @focus="isFocused = true"
            @blur="isFocused = false"
        >
            <option value="" disabled>{{ placeholder }}</option>
            <option 
                v-for="(option, index) in options" 
                :key="index"
                :value="getOptionValue(option)"
            >
                {{ getOptionLabel(option) }}
            </option>
            
            <!-- Custom Options Slot -->
            <slot />
        </select>
        
        <!-- Dropdown Arrow Icon -->
        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none">
            <svg 
                class="w-4 h-4 text-slate-400"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
        
        <!-- Floating Label -->
        <label 
            v-if="label"
            :class="labelClasses"
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
