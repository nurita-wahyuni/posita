<script setup>
//Nurita Wahyuni | 202312061
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { formatMoney } from '@/utils/formatMoney';
import { computed } from 'vue'

const props = defineProps({
    hasActiveSession: {
        type: Boolean,
        default: false,
    },
    activeSession: {
        type: Object,
        default: null,
    },
    lastClosedSession: {
        type: Object,
        default: null,
    },
    today: {
        type: String,
        default: '',
    },
});

const form = useForm({
    opening_cash: '',
});

const submit = () => {
    form.post('/pos/open');
};

const sessionInfo = computed(() => ({
  shiftName: props.activeSession?.shift_name ?? null,
  openingBalance: props.activeSession?.opening_cash ?? 0,
  isActive: props.hasActiveSession,
  openedAt: props.activeSession?.opened_at ?? null,
}))
</script>

<template>
    <Head title="Buka Toko" />

    <EmployeeLayout  :session-info="sessionInfo">
        <template #header>
            <h2 class="text-lg font-semibold text-gray-800">Buka Toko</h2>
        </template>

        <div class="relative w-full max-w-6xl overflow-hidden rounded-3xl shadow-2xl flex flex-col lg:flex-row bg-gradient-to-br from-stone-50 via-white to-emerald-50">
            <!-- Left Illustration -->
             <div class="relative lg:w-1/2 w-full flex flex-col justify-center items-center text-white p-12 bg-gradient-to-br from-amber-700 via-orange-600 to-amber-400">
                <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
                <div class="relative z-10 text-center space-y-6">
                    <h1 class="text-4xl font-extrabold tracking-tight text-white drop-shadow">
                        Kantin Cerdas
                    </h1>
                    <p class="text-white/90 max-w-sm mx-auto leading-relaxed">
                        Selamat datang di sistem kasir modern untuk kantin Anda.  
                        Mulailah hari dengan membuka sesi toko dan kelola transaksi dengan mudah.
                    </p>
                    <div class="bg-white/20 backdrop-blur-md border border-white/30 rounded-xl py-4 px-6 inline-block mt-4">
                        <p
                        class="uppercase text-xs tracking-wider text-white/80 font-semibold">
                        Hari Ini</p>
                        <p class="text-xl font-bold text-white drop-shadow-sm">
                            {{ today }}
                        </p>
                    </div>
                </div>
            </div>

        <!-- Right Section (Form) -->
        <div class="lg:w-1/2 w-full backdrop-blur-md bg-white/80 p-10 lg:p-16 flex flex-col justify-center items-center text-center">
            
        <!-- Already has active session -->
            <div v-if="hasActiveSession" class="w-full max-w-md text-center space-y-6">
                <div class="text-center">
                    <div class="text-5xl mb-3">✅</div>
                    <h3 class="text-2xl font-semibold text-emerald-700">
                        Sesi Toko Aktif
                    </h3>
                    <p class="text-gray-600">
                        Anda sudah membuka toko hari ini
                    </p>
                    <div class="bg-white rounded-xl shadow-inner p-5 mb-4 text-left border border-amber-300">
                        <p class="text-sm text-gray-600">Kas Awal:</p>
                        <p class="text-2xl font-bold text-gray-800">
                            {{ formatMoney(activeSession?.opening_cash || 0) }}
                        </p>
                        <p class="text-sm text-gray-500 mt-2">
                            Dibuka: {{ activeSession?.opened_at }}
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Link
                            href="/pos/consignment"
                            class="flex-1 bg-gradient-to-r from-emerald-400 to-green-500 hover:from-emerald-500 hover:to-green-600 text-white py-3 rounded-lg font-medium transition"
                        >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="3"
                            stroke="currentColor"
                            class="w-5 h-5 inline-block mr-2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 7.5l9-4.5 9 4.5-9 4.5-9-4.5zm0 9l9 4.5 9-4.5m-9-4.5V21"
                            />
                        </svg>
                        Input Barang Titipan
                        </Link>
                        <Link
                            href="/pos/close"
                            class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-lg font-medium transition"

                        >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="3"
                            stroke="currentColor"
                            class="w-5 h-5 inline-block mr-2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16.5 10.5V7.5a4.5 4.5 0 00-9 0v3m10.5 0h-12a1.5 1.5 0 00-1.5 1.5v7.5A1.5 1.5 0 004.5 21h15a1.5 1.5 0 001.5-1.5v-7.5a1.5 1.5 0 00-1.5-1.5z"
                            />
                        </svg>
                        Tutup Toko
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Open new session form -->
            <div v-else class="w-full max-w-md">
                <!-- Last Session Report Download (if available) -->
                <div v-if="lastClosedSession" 
                class="bg-white/70 border border-amber-200 rounded-xl p-4 mb-6 flex items-center justify-between hover:shadow transition">
                        <div>
                            <p class="text-sm font-medium text-amber-800">Sesi Terakhir</p>
                            <p class="text-xs text-amber-500">{{ lastClosedSession.closed_at }}</p>
                        </div>
                        <a
                            :href="`/pos/report/${lastClosedSession.id}`"
                            class="bg-emerald-500 hover:bg-emerald-700 text-white text-sm px-3 py-2 rounded-lg font-medium shadow-sm transition"
                            target="_blank"
                        >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="3"
                            stroke="currentColor"
                            class="w-5 h-5 inline-block mr-1"
                        >
                            <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19.5 8.25l-5.25-5.25H6a1.5 1.5 0 00-1.5 1.5v15A1.5 1.5 0 006 21h12a1.5 1.5 0 001.5-1.5V8.25z"
                            />
                        </svg>
                        Laporan
                        </a>
                    </div>

                <!-- Open Session Form -->
                <div class="text-center mb-8">
                <div
                class="mx-auto w-32 h-32 w-20 h-20 text-green-700
                        flex items-center justify-center"
                >
                <svg 
                    xmlns="http://www.w3.org/2000/svg" 
                    fill="none" viewBox="0 0 24 24" 
                    stroke-width="1.5" 
                    stroke="currentColor" 
                    class="size-30">
                    <path stroke-linecap="round" 
                        stroke-linejoin="round" 
                        d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                </svg>
            </div>
                        <h3 class="text-2xl font-semibold text-gray-800">Buka Sesi Toko</h3>
                        <p class="text-gray-500 mt-2">Masukkan kas awal hari ini</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Kas Awal (Modal)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    Rp
                                </span>
                                <input
                                    v-model="form.opening_cash"
                                    type="number"
                                    min="0"
                                    step="1000"
                                    class="w-full border border-amber-200 rounded-lg pl-10 pr-4 py-3 text-lg text-gray-700 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none"
                                    placeholder="0"
                                    required
                                />
                            </div>
                            <p v-if="form.errors.opening_cash" class="text-red-500 text-sm mt-1">
                                {{ form.errors.opening_cash }}
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-gradient-to-r from-emerald-400 to-green-500 hover:from-emerald-500 hover:to-green-600 text-white py-3 rounded-lg font-semibold text-lg shadow-md transition disabled:opacity-60"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                class="w-10 h-8 inline-block mr-2"
                            >
                                <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4.5 19.5l7.5-7.5 3 3 4.5-9L15 9l-3-3-7.5 7.5z"
                                />
                            </svg>
                            {{ form.processing ? 'Membuka...' : 'Buka Toko Sekarang' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </EmployeeLayout>
</template>