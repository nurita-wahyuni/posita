<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { formatMoney } from '@/utils/formatMoney';

const props = defineProps({
    upcomingOrders: {
        type: Array,
        default: () => [],
    },
    todayOrders: {
        type: Array,
        default: () => [],
    },
});

// Countdown timer state
const countdowns = ref({});
let intervalId = null;

// Calculate countdown for each order
const updateCountdowns = () => {
    const now = new Date();
    props.upcomingOrders.forEach(order => {
        const pickup = new Date(order.pickup_datetime);
        const diff = pickup - now;

        if (diff <= 0) {
            countdowns.value[order.id] = { expired: true, text: 'Sudah lewat' };
        } else {
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            countdowns.value[order.id] = {
                expired: false,
                days,
                hours,
                minutes,
                seconds,
                text: days > 0 
                    ? `${days}h ${hours}j ${minutes}m` 
                    : `${hours}j ${minutes}m ${seconds}d`
            };
        }
    });
};

onMounted(() => {
    updateCountdowns();
    intervalId = setInterval(updateCountdowns, 1000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});

const getStatusBadge = (status) => {
    const badges = {
        pending: 'bg-yellow-100 text-yellow-800',
        paid: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return badges[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
    const texts = {
        pending: 'Menunggu',
        paid: 'Lunas',
        completed: 'Selesai',
        cancelled: 'Batal',
    };
    return texts[status] || status;
};
</script>

<template>
    <Head title="Order Box" />

    <EmployeeLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-800">Order Box</h2>
        </template>

        <div class="space-y-6">
            <!-- Create New Order Button -->
            <Link
                href="/pos/box/create"
                class="block w-full bg-green-600 text-white py-3 rounded-lg font-medium text-center hover:bg-green-700"
            >
                + Buat Order Baru
            </Link>

            <!-- Countdown Widget -->
            <div v-if="upcomingOrders.length > 0" class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-4 text-white">
                <h3 class="font-semibold mb-3">⏰ Order Mendatang</h3>
                <div class="space-y-2">
                    <div
                        v-for="order in upcomingOrders.slice(0, 3)"
                        :key="order.id"
                        class="bg-white bg-opacity-20 rounded-lg p-3 flex justify-between items-center"
                    >
                        <div>
                            <p class="font-medium">{{ order.customer_name }}</p>
                            <p class="text-sm opacity-80">{{ order.items?.length || 1 }} item</p>
                        </div>
                        <div class="text-right">
                            <p 
                                class="font-bold text-lg"
                                :class="countdowns[order.id]?.expired ? 'text-red-300' : ''"
                            >
                                {{ countdowns[order.id]?.text || '...' }}
                            </p>
                            <p class="text-xs opacity-80">
                                {{ new Date(order.pickup_datetime).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Orders -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">📦 Pengambilan Hari Ini</h3>
                </div>
                <div class="p-4">
                    <div v-if="todayOrders.length === 0" class="text-center py-8 text-gray-500">
                        Tidak ada order untuk hari ini
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="order in todayOrders"
                            :key="order.id"
                            class="border rounded-lg p-4"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-medium">{{ order.customer_name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ new Date(order.pickup_datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                    </p>
                                </div>
                                <span :class="['px-2 py-1 text-xs rounded-full', getStatusBadge(order.status)]">
                                    {{ getStatusText(order.status) }}
                                </span>
                            </div>
                            
                            <!-- Order Items -->
                            <div v-if="order.items?.length > 0" class="mb-2 text-sm text-gray-600">
                                <div v-for="item in order.items" :key="item.id" class="flex justify-between">
                                    <span>{{ item.product_name }} x{{ item.quantity }}</span>
                                    <span>{{ formatMoney(item.subtotal) }}</span>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center pt-2 border-t">
                                <span class="font-bold text-green-600">{{ formatMoney(order.total_price) }}</span>
                                <a
                                    :href="`/pos/box/${order.id}/receipt`"
                                    target="_blank"
                                    class="text-sm text-blue-600 hover:text-blue-800"
                                >
                                    📄 Kwitansi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Upcoming Orders -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">📅 Order Mendatang</h3>
                </div>
                <div class="p-4">
                    <div v-if="upcomingOrders.length === 0" class="text-center py-8 text-gray-500">
                        Tidak ada order mendatang
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="order in upcomingOrders"
                            :key="order.id"
                            class="border rounded-lg p-4"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-medium">{{ order.customer_name }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ new Date(order.pickup_datetime).toLocaleString('id-ID', { 
                                            weekday: 'long', 
                                            day: 'numeric', 
                                            month: 'long',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span :class="['px-2 py-1 text-xs rounded-full', getStatusBadge(order.status)]">
                                        {{ getStatusText(order.status) }}
                                    </span>
                                    <p 
                                        class="text-sm font-medium mt-1"
                                        :class="countdowns[order.id]?.expired ? 'text-red-600' : 'text-blue-600'"
                                    >
                                        {{ countdowns[order.id]?.text || '...' }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Order Items Summary -->
                            <div v-if="order.items?.length > 0" class="mb-2 text-sm text-gray-600">
                                {{ order.items.map(i => i.product_name).join(', ') }}
                            </div>
                            
                            <div class="flex justify-between items-center pt-2 border-t">
                                <span class="font-bold text-green-600">{{ formatMoney(order.total_price) }}</span>
                                <a
                                    :href="`/pos/box/${order.id}/receipt`"
                                    target="_blank"
                                    class="text-sm text-blue-600 hover:text-blue-800"
                                >
                                    📄 Kwitansi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </EmployeeLayout>
</template>
