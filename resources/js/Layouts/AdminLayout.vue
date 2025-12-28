<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const navigation = [
    { 
        name: 'Dashboard', 
        href: '/admin', 
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>`
    },
    { 
        name: 'Partners', 
        href: '/admin/partners', 
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>`
    },
    { 
        name: 'Box Templates', 
        href: '/admin/box-templates', 
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>`
    },
    { 
        name: 'Users', 
        href: '/admin/users', 
        icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>`
    },
];

const isSidebarOpen = ref(true);
const isMobileSidebarOpen = ref(false);
const isProfileDropdownOpen = ref(false);
const isMobile = ref(false);

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
    if (!isMobile.value) {
        isMobileSidebarOpen.value = false;
    }
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

const isActive = (href) => {
    const path = window.location.pathname;
    if (href === '/admin') {
        return path === '/admin' || path === '/admin/';
    }
    return path.startsWith(href);
};

const toggleSidebar = () => {
    if (isMobile.value) {
        isMobileSidebarOpen.value = !isMobileSidebarOpen.value;
    } else {
        isSidebarOpen.value = !isSidebarOpen.value;
    }
};

const closeMobileSidebar = () => {
    isMobileSidebarOpen.value = false;
};
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <!-- Mobile Sidebar Backdrop -->
        <Transition
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="isMobileSidebarOpen && isMobile"
                class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
                @click="closeMobileSidebar"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex flex-col bg-admin-sidebar transition-all duration-300 ease-in-out',
                isMobile 
                    ? (isMobileSidebarOpen ? 'w-64 translate-x-0' : 'w-64 -translate-x-full')
                    : (isSidebarOpen ? 'w-64' : 'w-20')
            ]"
        >
            <!-- Logo Section -->
            <div class="flex h-16 items-center justify-between border-b border-slate-700 px-4">
                <Transition
                    enter-active-class="transition-opacity duration-200"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                    mode="out-in"
                >
                    <h1 
                        v-if="isSidebarOpen || isMobile"
                        class="text-xl font-bold text-white tracking-tight"
                    >
                        <span class="text-blue-400">Posita</span> Admin
                    </h1>
                    <span v-else class="text-xl font-bold text-blue-400">P</span>
                </Transition>
                
                <!-- Mobile Close Button -->
                <button 
                    v-if="isMobile && isMobileSidebarOpen"
                    @click="closeMobileSidebar"
                    class="text-slate-400 hover:text-white p-1 rounded-md hover:bg-slate-700 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200',
                        isActive(item.href)
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30'
                            : 'text-slate-300 hover:bg-admin-sidebar-hover hover:text-white'
                    ]"
                    @click="closeMobileSidebar"
                >
                    <span 
                        v-html="item.icon" 
                        :class="[
                            'flex-shrink-0 transition-colors',
                            isActive(item.href) ? 'text-white' : 'text-slate-400 group-hover:text-white'
                        ]"
                    />
                    <Transition
                        enter-active-class="transition-all duration-200"
                        enter-from-class="opacity-0 w-0"
                        enter-to-class="opacity-100 w-auto"
                        leave-active-class="transition-all duration-200"
                        leave-from-class="opacity-100 w-auto"
                        leave-to-class="opacity-0 w-0"
                    >
                        <span v-if="isSidebarOpen || isMobile" class="ml-3 whitespace-nowrap overflow-hidden">
                            {{ item.name }}
                        </span>
                    </Transition>
                </Link>
            </nav>

            <!-- User Info Footer -->
            <div class="border-t border-slate-700 p-4">
                <div :class="['flex items-center', isSidebarOpen || isMobile ? 'space-x-3' : 'justify-center']">
                    <div class="flex-shrink-0 w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm shadow-lg">
                        {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                    </div>
                    <Transition
                        enter-active-class="transition-all duration-200"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition-all duration-200"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div v-if="isSidebarOpen || isMobile" class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate">
                                {{ user?.name || 'Admin' }}
                            </p>
                            <p class="text-xs text-slate-400 truncate">
                                {{ user?.email || 'admin@example.com' }}
                            </p>
                        </div>
                    </Transition>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div 
            :class="[
                'transition-all duration-300 ease-in-out min-h-screen flex flex-col',
                isMobile ? 'ml-0' : (isSidebarOpen ? 'lg:ml-64' : 'lg:ml-20')
            ]"
        >
            <!-- Top Header -->
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm">
                <div class="flex items-center justify-between h-16 px-4 lg:px-6">
                    <!-- Left: Menu Toggle -->
                    <div class="flex items-center space-x-4">
                        <button
                            @click="toggleSidebar"
                            class="p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        
                        <div class="hidden sm:block">
                            <slot name="header" />
                        </div>
                    </div>

                    <!-- Right: Profile Dropdown -->
                    <div class="relative">
                        <button 
                            @click="isProfileDropdownOpen = !isProfileDropdownOpen"
                            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
                                {{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                            </div>
                            <span class="hidden md:block text-sm font-medium text-slate-700">
                                {{ user?.name || 'Admin' }}
                            </span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <Transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <div 
                                v-if="isProfileDropdownOpen"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg ring-1 ring-slate-900/5 py-1 z-50"
                            >
                                <div class="px-4 py-3 border-b border-slate-100">
                                    <p class="text-sm font-medium text-slate-900">{{ user?.name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ user?.email }}</p>
                                </div>
                                
                                <Link 
                                    href="/profile"
                                    class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors"
                                    @click="isProfileDropdownOpen = false"
                                >
                                    <svg class="w-4 h-4 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Profile Settings
                                </Link>
                                
                                <div class="border-t border-slate-100 mt-1 pt-1">
                                    <Link 
                                        href="/logout"
                                        method="post"
                                        as="button"
                                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                    >
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Sign Out
                                    </Link>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
                
                <!-- Mobile Header Slot -->
                <div class="sm:hidden px-4 pb-3" v-if="$slots.header">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-6">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="py-4 px-6 text-center text-xs text-slate-400 border-t border-slate-200">
                © {{ new Date().getFullYear() }} Posita. All rights reserved.
            </footer>
        </div>

        <!-- Click outside to close dropdown -->
        <div 
            v-if="isProfileDropdownOpen"
            class="fixed inset-0 z-20"
            @click="isProfileDropdownOpen = false"
        />
    </div>
</template>
