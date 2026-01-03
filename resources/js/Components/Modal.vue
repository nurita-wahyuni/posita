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
import { AlertTriangle, AlertCircle, CheckCircle, X } from 'lucide-vue-next';

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
        iconBg: 'bg-blue-500/20 dark:bg-blue-500/30',
        iconColor: 'text-blue-600 dark:text-blue-400',
    },
    danger: {
        iconBg: 'bg-red-500/20 dark:bg-red-500/30',
        iconColor: 'text-red-600 dark:text-red-400',
    },
    warning: {
        iconBg: 'bg-amber-500/20 dark:bg-amber-500/30',
        iconColor: 'text-amber-600 dark:text-amber-400',
    },
    success: {
        iconBg: 'bg-emerald-500/20 dark:bg-emerald-500/30',
        iconColor: 'text-emerald-600 dark:text-emerald-400',
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
                    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" />
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
                    class="mb-6 transform overflow-hidden rounded-2xl bg-card text-card-foreground shadow-xl transition-all sm:mx-auto sm:w-full"
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
                                        <AlertTriangle v-if="variant === 'danger'" class="w-5 h-5" />
                                        <!-- Warning Icon -->
                                        <AlertCircle v-else-if="variant === 'warning'" class="w-5 h-5" />
                                        <!-- Success Icon -->
                                        <CheckCircle v-else-if="variant === 'success'" class="w-5 h-5" />
                                    </div>
                                </slot>
                                
                                <div>
                                    <slot name="header">
                                        <h3 v-if="title" class="text-lg font-semibold text-foreground">
                                            {{ title }}
                                        </h3>
                                    </slot>
                                </div>
                            </div>
                            
                            <!-- Close Button -->
                            <button 
                                v-if="showCloseButton && closeable"
                                @click="close"
                                class="flex-shrink-0 p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                            >
                                <X class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-5 py-4">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div 
                            v-if="$slots.footer"
                            class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 px-5 py-4 bg-muted border-t border-border"
                        >
                            <slot name="footer" />
                        </div>
                    </template>
                </div>
            </Transition>
        </div>
    </dialog>
</template>
