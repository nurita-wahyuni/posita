<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

/**
 * @typedef {Object} SessionInfo
 * @property {string} [shiftName] - Current shift name
 * @property {number} [openingBalance] - Opening balance amount
 * @property {boolean} [isActive] - Whether a session is active
 * @property {string} [openedAt] - Session open time
 */

const props = defineProps({
    /** @type {SessionInfo} */
    sessionInfo: {
        type: Object,
        default: () => ({
            shiftName: null,
            openingBalance: 0,
            isActive: false,
            openedAt: null
        })
    }
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Try to get session info from page props if not passed directly
const session = computed(() => {
    return props.sessionInfo?.shiftName 
        ? props.sessionInfo 
        : page.props.currentSession || props.sessionInfo;
});

const navigation = [
    { 
        name: 'Buka Toko', 
        href: '/pos/open', 
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>`
    },
    { 
        name: 'Barang', 
        href: '/pos/consignment', 
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>`
    },
    { 
        name: 'Box Order', 
        href: '/pos/box', 
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>`
    },
    { 
        name: 'Tutup', 
        href: '/pos/close', 
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>`
    },
];

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value || 0);
};

const isActive = (href) => {
    return window.location.pathname === href;
};

const isQuickActionVisible = ref(false);
</script>

<template>
    <div class="min-h-screen bg-slate-50 pb-20 lg:pb-0">
        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-40 bg-white shadow-sm">
            <div class="flex items-center justify-between h-14 px-4 lg:px-6">
                <!-- Logo / Brand -->
                <div class="flex items-center space-x-3">
                    <h1 class="text-lg font-bold text-slate-800">
                        <span class="text-emerald-600">Posita</span> POS
                    </h1>
                </div>

                <!-- Session Status Badge -->
                <div class="flex items-center space-x-4">
                    <!-- Session Info -->
                    <div class="hidden sm:flex items-center space-x-3">
                        <div 
                            :class="[
                                'flex items-center space-x-2 px-3 py-1.5 rounded-full text-xs font-medium',
                                session?.isActive 
                                    ? 'bg-emerald-100 text-emerald-700' 
                                    : 'bg-slate-100 text-slate-600'
                            ]"
                        >
                            <span 
                                :class="[
                                    'w-2 h-2 rounded-full',
                                    session?.isActive ? 'bg-emerald-500 animate-pulse-subtle' : 'bg-slate-400'
                                ]"
                            />
                            <span>{{ session?.isActive ? 'Sesi Aktif' : 'Tidak Ada Sesi' }}</span>
                        </div>
                        
                        <div v-if="session?.openingBalance" class="text-sm text-slate-600">
                            Saldo: <span class="font-semibold text-slate-800">{{ formatCurrency(session.openingBalance) }}</span>
                        </div>
                    </div>

                    <!-- Quick Action Toggle -->
                    <button 
                        @click="isQuickActionVisible = !isQuickActionVisible"
                        class="lg:hidden p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                        </svg>
                    </button>

                    <!-- Desktop Quick Actions -->
                    <div class="hidden lg:flex items-center space-x-2">
                        <Link 
                            v-if="!session?.isActive"
                            href="/pos/open"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                            Buka Toko
                        </Link>
                        <Link 
                            v-else
                            href="/pos/close"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Tutup Toko
                        </Link>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-2">
                        <span class="hidden sm:block text-sm text-slate-600">{{ user?.name }}</span>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                            title="Logout"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Mobile Session Info Bar -->
            <div class="sm:hidden border-t border-slate-100 px-4 py-2 bg-slate-50">
                <div class="flex items-center justify-between">
                    <div 
                        :class="[
                            'flex items-center space-x-2 px-2.5 py-1 rounded-full text-xs font-medium',
                            session?.isActive 
                                ? 'bg-emerald-100 text-emerald-700' 
                                : 'bg-slate-200 text-slate-600'
                        ]"
                    >
                        <span 
                            :class="[
                                'w-1.5 h-1.5 rounded-full',
                                session?.isActive ? 'bg-emerald-500 animate-pulse-subtle' : 'bg-slate-400'
                            ]"
                        />
                        <span>{{ session?.isActive ? 'Aktif' : 'Tutup' }}</span>
                    </div>
                    <div v-if="session?.openingBalance" class="text-xs text-slate-600">
                        Saldo: <span class="font-semibold">{{ formatCurrency(session.openingBalance) }}</span>
                    </div>
                </div>
            </div>

            <!-- Mobile Quick Action Dropdown -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="transform opacity-0 -translate-y-2"
                enter-to-class="transform opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="transform opacity-100 translate-y-0"
                leave-to-class="transform opacity-0 -translate-y-2"
            >
                <div 
                    v-if="isQuickActionVisible"
                    class="lg:hidden absolute left-0 right-0 top-full bg-white border-t border-slate-200 shadow-lg p-4 space-y-2"
                >
                    <Link 
                        v-if="!session?.isActive"
                        href="/pos/open"
                        class="flex items-center justify-center w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors"
                        @click="isQuickActionVisible = false"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                        </svg>
                        Buka Toko
                    </Link>
                    <Link 
                        v-else
                        href="/pos/close"
                        class="flex items-center justify-center w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors"
                        @click="isQuickActionVisible = false"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Tutup Toko
                    </Link>
                </div>
            </Transition>
        </header>

        <!-- Page Title Header -->
        <div v-if="$slots.header" class="bg-white border-b border-slate-200 px-4 lg:px-6 py-4">
            <slot name="header" />
        </div>

        <!-- Main Content -->
        <main class="p-4 lg:p-6 lg:max-w-7xl lg:mx-auto">
            <slot />
        </main>

        <!-- Desktop Sidebar Navigation (Optional - shown on large screens) -->
        <aside class="hidden xl:fixed xl:inset-y-0 xl:right-0 xl:w-16 xl:flex xl:flex-col xl:items-center xl:py-6 xl:bg-white xl:border-l xl:border-slate-200">
            <nav class="flex-1 flex flex-col items-center space-y-2 mt-16">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'p-3 rounded-xl transition-all duration-200',
                        isActive(item.href)
                            ? 'bg-emerald-100 text-emerald-600'
                            : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'
                    ]"
                    :title="item.name"
                >
                    <span v-html="item.icon" class="w-6 h-6" />
                </Link>
            </nav>
        </aside>

        <!-- Bottom Navigation (Mobile & Tablet) -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow-nav z-50 lg:hidden safe-area-inset-bottom">
            <div class="flex justify-around items-stretch h-16">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'flex flex-col items-center justify-center flex-1 py-2 transition-all duration-200',
                        isActive(item.href)
                            ? 'text-emerald-600 bg-emerald-50'
                            : 'text-slate-400 hover:text-slate-600 active:bg-slate-50'
                    ]"
                >
                    <span 
                        v-html="item.icon" 
                        :class="[
                            'transition-transform duration-200',
                            isActive(item.href) ? 'scale-110' : ''
                        ]"
                    />
                    <span 
                        :class="[
                            'text-[10px] mt-1 font-medium transition-all duration-200',
                            isActive(item.href) ? 'text-emerald-600' : 'text-slate-500'
                        ]"
                    >
                        {{ item.name }}
                    </span>
                </Link>
            </div>
        </nav>

        <!-- Click outside to close quick action dropdown -->
        <div 
            v-if="isQuickActionVisible"
            class="fixed inset-0 z-30 lg:hidden"
            @click="isQuickActionVisible = false"
        />
    </div>
</template>

<style scoped>
.safe-area-inset-bottom {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
