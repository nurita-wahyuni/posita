<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatsCard from '@/Components/StatsCard.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Sample stats data - in production, these would come from backend props
const stats = [
    { 
        title: 'Total Penjualan', 
        value: 15750000, 
        prefix: 'Rp ', 
        currency: true,
        change: 12.5,
        variant: 'primary'
    },
    { 
        title: 'Order Hari Ini', 
        value: 24, 
        suffix: ' order',
        change: 8.2,
        variant: 'success'
    },
    { 
        title: 'Partner Aktif', 
        value: 12,
        suffix: ' partner',
        change: 0,
        variant: 'default'
    },
    { 
        title: 'Pending Orders', 
        value: 3,
        suffix: ' order',
        change: -25,
        variant: 'warning'
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-slate-800">
                Dashboard
            </h2>
        </template>

        <!-- Welcome Section -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">
                Selamat datang, {{ user?.name || 'Admin' }}! 👋
            </h1>
            <p class="mt-1 text-slate-500">
                Berikut adalah ringkasan aktivitas hari ini.
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <StatsCard
                v-for="(stat, index) in stats"
                :key="index"
                :title="stat.title"
                :value="stat.value"
                :prefix="stat.prefix"
                :suffix="stat.suffix"
                :currency="stat.currency"
                :change="stat.change"
                :variant="stat.variant"
                change-period="vs kemarin"
            >
                <template #icon>
                    <!-- Custom icons per card -->
                    <svg v-if="index === 0" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg v-else-if="index === 1" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <svg v-else-if="index === 2" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
            </StatsCard>
        </div>

        <!-- Main Content Area -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Activity -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-card p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">
                    Aktivitas Terbaru
                </h3>
                <div class="space-y-4">
                    <div v-for="i in 4" :key="i" class="flex items-start gap-4 pb-4 border-b border-slate-100 last:border-0 last:pb-0">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900">Order #{{ 1000 + i }} selesai diproses</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ i * 15 }} menit yang lalu</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-xl shadow-card p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">
                    Menu Cepat
                </h3>
                <div class="space-y-2">
                    <a 
                        href="/admin/partners"
                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors group"
                    >
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">Kelola Partner</p>
                            <p class="text-xs text-slate-500">Lihat semua partner</p>
                        </div>
                    </a>
                    <a 
                        href="/admin/box-templates"
                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors group"
                    >
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">Box Templates</p>
                            <p class="text-xs text-slate-500">Atur template box</p>
                        </div>
                    </a>
                    <a 
                        href="/admin/users"
                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors group"
                    >
                        <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">Kelola Users</p>
                            <p class="text-xs text-slate-500">Manajemen akun</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
