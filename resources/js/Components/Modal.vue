<script setup>
/**
 * Modal - Enhanced modal with confirmation variant and improved animations
 * @example
 * <Modal :show="isOpen" @close="isOpen = false" title="Confirm Delete">
 *   <p>Are you sure you want to delete this item?</p>
 *   <template #footer>
 *     <BaseButton variant="outline" @click="isOpen = false">Cancel</BaseButton>
 *     <BaseButton variant="danger" @click="handleDelete">Delete</BaseButton>
 *   </template>
 * </Modal>
 */
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    /** Show/hide modal */
    show: {
        type: Boolean,
        default: false,
    },
    /** Modal max width */
    maxWidth: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg', 'xl', '2xl', 'full'].includes(v),
    },
    /** Allow closing modal by clicking backdrop or pressing ESC */
    closeable: {
        type: Boolean,
        default: true,
    },
    /** Modal title (optional, can use header slot instead) */
    title: {
        type: String,
        default: '',
    },
    /** Modal variant */
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'danger', 'warning', 'success'].includes(v),
    },
    /** Show close button in header */
    showCloseButton: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);
const dialog = ref();
const showSlot = ref(props.show);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
            showSlot.value = true;
            dialog.value?.showModal();
        } else {
            document.body.style.overflow = '';
            setTimeout(() => {
                dialog.value?.close();
                showSlot.value = false;
            }, 200);
        }
    },
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault();
        if (props.show) {
            close();
        }
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = '';
});

const maxWidthClass = computed(() => ({
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    'full': 'sm:max-w-full sm:mx-4',
}[props.maxWidth]));

const variantStyles = computed(() => ({
    default: {
        iconBg: 'bg-blue-100',
        iconColor: 'text-blue-600',
    },
    danger: {
        iconBg: 'bg-red-100',
        iconColor: 'text-red-600',
    },
    warning: {
        iconBg: 'bg-amber-100',
        iconColor: 'text-amber-600',
    },
    success: {
        iconBg: 'bg-emerald-100',
        iconColor: 'text-emerald-600',
    },
}[props.variant]));
</script>

<template>
    <dialog
        class="z-50 m-0 min-h-full min-w-full overflow-y-auto bg-transparent backdrop:bg-transparent"
        ref="dialog"
    >
        <div
            class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
            scroll-region
        >
            <!-- Backdrop -->
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-show="show"
                    class="fixed inset-0 transform transition-all"
                    @click="close"
                >
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" />
                </div>
            </Transition>

            <!-- Modal Content -->
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div
                    v-show="show"
                    class="mb-6 transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all sm:mx-auto sm:w-full"
                    :class="maxWidthClass"
                >
                    <template v-if="showSlot">
                        <!-- Header -->
                        <div 
                            v-if="title || $slots.header || showCloseButton"
                            class="flex items-start justify-between gap-4 px-5 pt-5 pb-0"
                        >
                            <div class="flex items-start gap-4">
                                <!-- Variant Icon -->
                                <slot name="icon">
                                    <div 
                                        v-if="variant !== 'default'"
                                        :class="[
                                            'flex-shrink-0 p-2.5 rounded-full',
                                            variantStyles.iconBg,
                                            variantStyles.iconColor,
                                        ]"
                                    >
                                        <!-- Danger Icon -->
                                        <svg v-if="variant === 'danger'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        <!-- Warning Icon -->
                                        <svg v-else-if="variant === 'warning'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <!-- Success Icon -->
                                        <svg v-else-if="variant === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </slot>
                                
                                <div>
                                    <slot name="header">
                                        <h3 v-if="title" class="text-lg font-semibold text-slate-900">
                                            {{ title }}
                                        </h3>
                                    </slot>
                                </div>
                            </div>
                            
                            <!-- Close Button -->
                            <button 
                                v-if="showCloseButton && closeable"
                                @click="close"
                                class="flex-shrink-0 p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-5 py-4">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div 
                            v-if="$slots.footer"
                            class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 px-5 py-4 bg-slate-50 border-t border-slate-100"
                        >
                            <slot name="footer" />
                        </div>
                    </template>
                </div>
            </Transition>
        </div>
    </dialog>
</template>
