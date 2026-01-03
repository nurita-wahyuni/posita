<script setup>
import { ref, computed, watch } from 'vue';
import { Dialog, DialogPanel, DialogTitle, DialogDescription, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { usePosStore } from '@/Stores/usePosStore';
import BaseButton from '@/Components/BaseButton.vue';
import { X, Printer, CheckCircle, ArrowRight } from 'lucide-vue-next';
import { ThermalPrinter } from '@/Services/ThermalPrinter';

const props = defineProps({
    isOpen: Boolean,
});

const emit = defineEmits(['close', 'complete']);

const store = usePosStore();
const amountPaid = ref('');
const step = ref('input'); // 'input' | 'success'
const printer = new ThermalPrinter();

// Computed
const change = computed(() => {
    const paid = Number(amountPaid.value) || 0;
    return paid - store.total;
});

const isValid = computed(() => {
    return Number(amountPaid.value) >= store.total;
});

const suggestions = computed(() => {
    const total = store.total;
    return [
        total,
        Math.ceil(total / 5000) * 5000,
        Math.ceil(total / 10000) * 10000,
        Math.ceil(total / 50000) * 50000,
        Math.ceil(total / 100000) * 100000,
    ].filter((v, i, a) => a.indexOf(v) === i && v >= total).slice(0, 4);
});

// Watch open state to reset
watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        step.value = 'input';
        amountPaid.value = '';
        // Focus logic could go here if using a directive or ref
    }
});

// Methods
const handlePay = () => {
    if (!isValid.value) return;
    
    // In a real app, here we would send data to backend API
    // await axios.post('/pos/transaction', ...)
    
    step.value = 'success';
};

const handlePrint = () => {
    const transaction = {
        id: `TRX-${Date.now().toString().slice(-6)}`,
        cart: [...store.cart],
        subtotal: store.subtotal,
        tax: store.tax,
        total: store.total,
        amountPaid: Number(amountPaid.value),
        change: change.value
    };
    
    printer.printBrowser(transaction);
};

const handleComplete = () => {
    emit('complete');
    emit('close');
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
};
</script>

<template>
    <TransitionRoot as="template" :show="isOpen">
        <Dialog as="div" class="relative z-50" @close="step === 'success' ? handleComplete() : emit('close')">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            
                            <!-- Header -->
                            <div class="flex justify-between items-center p-4 border-b border-gray-100">
                                <DialogTitle as="h3" class="text-lg font-bold leading-6 text-gray-900">
                                    {{ step === 'input' ? 'Pembayaran' : 'Transaksi Berhasil' }}
                                </DialogTitle>
                                <DialogDescription class="sr-only">
                                    {{ step === 'input' ? 'Complete the payment process' : 'Transaction completed successfully' }}
                                </DialogDescription>
                                <button v-if="step === 'input'" @click="$emit('close')" class="text-gray-400 hover:text-gray-500">
                                    <X class="w-6 h-6" />
                                </button>
                            </div>

                            <!-- Content: Input Step -->
                            <div v-if="step === 'input'" class="p-6">
                                <div class="mb-6 text-center">
                                    <p class="text-sm text-gray-500 mb-1">Total Tagihan</p>
                                    <p class="text-4xl font-extrabold text-gray-900">{{ formatPrice(store.total) }}</p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Uang Diterima (Tunai)</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <input 
                                                type="number" 
                                                v-model="amountPaid"
                                                class="block w-full rounded-md border-gray-300 pl-12 py-3 focus:border-emerald-500 focus:ring-emerald-500 sm:text-lg" 
                                                placeholder="0"
                                                autofocus
                                                @keyup.enter="handlePay"
                                            />
                                        </div>
                                    </div>

                                    <!-- Quick Suggestions -->
                                    <div class="flex gap-2 flex-wrap">
                                        <button 
                                            v-for="sugg in suggestions" 
                                            :key="sugg"
                                            @click="amountPaid = sugg"
                                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm font-medium transition-colors"
                                        >
                                            {{ formatPrice(sugg) }}
                                        </button>
                                    </div>

                                    <!-- Change Display Preview -->
                                    <div v-if="isValid" class="bg-emerald-50 p-3 rounded-lg flex justify-between items-center animate-in fade-in slide-in-from-top-1">
                                        <span class="text-emerald-700 font-medium">Kembali</span>
                                        <span class="text-emerald-700 font-bold text-lg">{{ formatPrice(change) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Content: Success Step -->
                            <div v-else class="p-6 flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                    <CheckCircle class="w-8 h-8 text-green-600" />
                                </div>
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Pembayaran Sukses!</h4>
                                <p class="text-gray-500 mb-6">Transaksi telah berhasil direkam.</p>

                                <div class="w-full bg-gray-50 rounded-xl p-4 mb-6 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Total Tagihan</span>
                                        <span class="font-bold text-gray-900">{{ formatPrice(store.total) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Tunai</span>
                                        <span class="font-bold text-gray-900">{{ formatPrice(amountPaid) }}</span>
                                    </div>
                                    <div class="border-t border-gray-200 pt-2 flex justify-between text-base">
                                        <span class="text-gray-600 font-medium">Kembali</span>
                                        <span class="font-bold text-emerald-600">{{ formatPrice(change) }}</span>
                                    </div>
                                </div>

                                <div class="w-full space-y-3">
                                    <BaseButton 
                                        variant="outline" 
                                        full-width 
                                        size="lg"
                                        @click="handlePrint"
                                    >
                                        <Printer class="w-5 h-5 mr-2" />
                                        Cetak Struk
                                    </BaseButton>

                                    <BaseButton 
                                        variant="primary" 
                                        full-width 
                                        size="lg"
                                        @click="handleComplete"
                                    >
                                        Transaksi Baru
                                        <ArrowRight class="w-5 h-5 ml-2" />
                                    </BaseButton>
                                </div>
                            </div>

                            <!-- Footer Actions (Only for Input Step) -->
                            <div v-if="step === 'input'" class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <BaseButton 
                                    variant="primary" 
                                    class="w-full sm:ml-3 sm:w-auto"
                                    :disabled="!isValid"
                                    @click="handlePay"
                                >
                                    Bayar Sekarang
                                </BaseButton>
                                <BaseButton 
                                    variant="ghost" 
                                    class="mt-3 w-full sm:mt-0 sm:w-auto"
                                    @click="$emit('close')"
                                >
                                    Batal
                                </BaseButton>
                            </div>

                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
