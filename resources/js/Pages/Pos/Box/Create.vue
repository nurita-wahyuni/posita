<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import { formatMoney } from '@/utils/formatMoney';

const props = defineProps({
    selectedTemplate: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    customer_name: '',
    pickup_datetime: '',
    items: [],
});

// Add a new line item
const addItem = () => {
    form.items.push({
        product_name: '',
        quantity: 1,
        unit_price: '',
    });
};

// Remove a line item
const removeItem = (index) => {
    form.items.splice(index, 1);
};

// Calculate total
const calculatedTotal = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + (item.quantity * (item.unit_price || 0));
    }, 0);
});

// Get minimum datetime (now + 1 hour)
const minDateTime = computed(() => {
    const now = new Date();
    now.setHours(now.getHours() + 1);
    return now.toISOString().slice(0, 16);
});

const submit = () => {
    form.post('/pos/box', {
        onSuccess: () => {
            form.reset();
        },
    });
};

// Initialize with one empty item
if (form.items.length === 0) {
    addItem();
}
</script>

<template>
    <Head title="Buat Order Box" />

    <EmployeeLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-800">Buat Order Box</h2>
        </template>

        <div class="max-w-lg mx-auto">
            <div class="bg-white rounded-lg shadow p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Customer Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Pelanggan
                        </label>
                        <input
                            v-model="form.customer_name"
                            type="text"
                            class="w-full border rounded-lg px-3 py-2"
                            placeholder="Masukkan nama pelanggan"
                            required
                        />
                        <p v-if="form.errors.customer_name" class="text-red-500 text-sm mt-1">
                            {{ form.errors.customer_name }}
                        </p>
                    </div>

                    <!-- Pickup DateTime -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal & Waktu Pengambilan
                        </label>
                        <input
                            v-model="form.pickup_datetime"
                            type="datetime-local"
                            :min="minDateTime"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        />
                        <p v-if="form.errors.pickup_datetime" class="text-red-500 text-sm mt-1">
                            {{ form.errors.pickup_datetime }}
                        </p>
                    </div>

                    <!-- Line Items -->
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Item Pesanan
                            </label>
                            <button
                                type="button"
                                @click="addItem"
                                class="text-sm bg-green-100 text-green-700 px-3 py-1 rounded-lg hover:bg-green-200"
                            >
                                + Tambah Item
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(item, index) in form.items"
                                :key="index"
                                class="bg-gray-50 rounded-lg p-3"
                            >
                                <div class="flex items-start gap-2">
                                    <div class="flex-1 grid grid-cols-3 gap-2">
                                        <div class="col-span-3 sm:col-span-1">
                                            <label class="block text-xs text-gray-500 mb-1">Nama</label>
                                            <input
                                                v-model="item.product_name"
                                                type="text"
                                                class="w-full border rounded px-2 py-1 text-sm"
                                                placeholder="Nasi"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Qty</label>
                                            <input
                                                v-model.number="item.quantity"
                                                type="number"
                                                min="1"
                                                class="w-full border rounded px-2 py-1 text-sm text-center"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Harga</label>
                                            <div class="relative">
                                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                                <input
                                                    v-model.number="item.unit_price"
                                                    type="number"
                                                    min="0"
                                                    class="w-full border rounded pl-7 pr-2 py-1 text-sm"
                                                    required
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        v-if="form.items.length > 1"
                                        type="button"
                                        @click="removeItem(index)"
                                        class="text-red-500 hover:text-red-700 mt-5"
                                    >
                                        ✕
                                    </button>
                                </div>
                                <div class="mt-2 text-right text-xs text-gray-500">
                                    Subtotal: {{ formatMoney(item.quantity * (item.unit_price || 0)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Total</span>
                            <span class="text-xl font-bold text-blue-600">
                                {{ formatMoney(calculatedTotal) }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 pt-4">
                        <Link
                            href="/pos/box"
                            class="flex-1 text-center border border-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-50"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || form.items.length === 0"
                            class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Buat Order' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </EmployeeLayout>
</template>
