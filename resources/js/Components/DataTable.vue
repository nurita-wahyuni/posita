<script setup>
/**
 * DataTable - Full-featured data table with pagination, search, and filter slots
 * @example
 * <DataTable 
 *   :columns="columns" 
 *   :data="users" 
 *   :pagination="pagination"
 *   :loading="loading"
 *   @page-change="handlePageChange"
 * />
 */
import { computed, ref } from 'vue';

const props = defineProps({
    /** 
     * Column definitions
     * @type {Array<{key: string, label: string, sortable?: boolean, class?: string}>}
     */
    columns: {
        type: Array,
        required: true,
    },
    /** Data rows */
    data: {
        type: Array,
        default: () => [],
    },
    /** 
     * Pagination object
     * @type {{current_page: number, last_page: number, per_page: number, total: number}}
     */
    pagination: {
        type: Object,
        default: null,
    },
    /** Loading state */
    loading: {
        type: Boolean,
        default: false,
    },
    /** Empty state message */
    emptyMessage: {
        type: String,
        default: 'No data available',
    },
    /** Enable row striping */
    striped: {
        type: Boolean,
        default: true,
    },
    /** Enable row hover effect */
    hoverable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['page-change', 'sort', 'row-click']);

const sortKey = ref('');
const sortOrder = ref('asc');

const handleSort = (column) => {
    if (!column.sortable) return;
    
    if (sortKey.value === column.key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = column.key;
        sortOrder.value = 'asc';
    }
    
    emit('sort', { key: sortKey.value, order: sortOrder.value });
};

const handlePageChange = (page) => {
    emit('page-change', page);
};

const getCellValue = (row, key) => {
    // Support nested keys like 'user.name'
    return key.split('.').reduce((obj, k) => obj?.[k], row);
};

// Pagination helpers
const pages = computed(() => {
    if (!props.pagination) return [];
    
    const { current_page, last_page } = props.pagination;
    const delta = 2;
    const range = [];
    
    for (let i = Math.max(2, current_page - delta); i <= Math.min(last_page - 1, current_page + delta); i++) {
        range.push(i);
    }
    
    if (current_page - delta > 2) {
        range.unshift('...');
    }
    if (current_page + delta < last_page - 1) {
        range.push('...');
    }
    
    if (last_page > 1) {
        range.unshift(1);
        if (last_page > 1) range.push(last_page);
    } else if (last_page === 1) {
        range.push(1);
    }
    
    return [...new Set(range)];
});

const showingFrom = computed(() => {
    if (!props.pagination) return 0;
    return ((props.pagination.current_page - 1) * props.pagination.per_page) + 1;
});

const showingTo = computed(() => {
    if (!props.pagination) return 0;
    return Math.min(props.pagination.current_page * props.pagination.per_page, props.pagination.total);
});
</script>

<template>
    <div class="bg-white rounded-xl shadow-card overflow-hidden">
        <!-- Header with search and filters -->
        <div v-if="$slots.header || $slots.search || $slots.filters" class="px-4 py-3 border-b border-slate-200 bg-slate-50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <slot name="header" />
                <div class="flex items-center gap-3">
                    <slot name="search" />
                    <slot name="filters" />
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th 
                            v-for="column in columns" 
                            :key="column.key"
                            :class="[
                                'px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider',
                                column.sortable ? 'cursor-pointer hover:text-slate-900 select-none' : '',
                                column.class || ''
                            ]"
                            @click="handleSort(column)"
                        >
                            <div class="flex items-center gap-1.5">
                                {{ column.label }}
                                <span v-if="column.sortable" class="text-slate-400">
                                    <svg 
                                        v-if="sortKey === column.key"
                                        class="w-3.5 h-3.5 transition-transform"
                                        :class="sortOrder === 'desc' ? 'rotate-180' : ''"
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    </svg>
                                    <svg 
                                        v-else
                                        class="w-3.5 h-3.5 opacity-40"
                                        fill="none" 
                                        stroke="currentColor" 
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </span>
                            </div>
                        </th>
                        <th v-if="$slots.actions" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- Loading State -->
                    <template v-if="loading">
                        <tr v-for="i in 5" :key="'skeleton-' + i" class="animate-pulse">
                            <td v-for="column in columns" :key="column.key" class="px-4 py-4">
                                <div class="h-4 bg-slate-200 rounded w-3/4"></div>
                            </td>
                            <td v-if="$slots.actions" class="px-4 py-4">
                                <div class="h-4 bg-slate-200 rounded w-16 ml-auto"></div>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <tr v-else-if="data.length === 0">
                        <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="px-4 py-12 text-center">
                            <slot name="empty">
                                <div class="flex flex-col items-center text-slate-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-sm font-medium">{{ emptyMessage }}</p>
                                </div>
                            </slot>
                        </td>
                    </tr>

                    <!-- Data Rows -->
                    <tr 
                        v-else
                        v-for="(row, index) in data" 
                        :key="row.id || index"
                        :class="[
                            striped && index % 2 === 1 ? 'bg-slate-50/50' : '',
                            hoverable ? 'hover:bg-blue-50/50 transition-colors cursor-pointer' : ''
                        ]"
                        @click="$emit('row-click', row)"
                    >
                        <td 
                            v-for="column in columns" 
                            :key="column.key"
                            class="px-4 py-3 text-sm text-slate-700"
                            :class="column.class || ''"
                        >
                            <slot :name="'cell-' + column.key" :row="row" :value="getCellValue(row, column.key)">
                                {{ getCellValue(row, column.key) }}
                            </slot>
                        </td>
                        <td v-if="$slots.actions" class="px-4 py-3 text-right">
                            <slot name="actions" :row="row" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div 
            v-if="pagination && pagination.last_page > 1"
            class="px-4 py-3 border-t border-slate-200 bg-slate-50"
        >
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-slate-600">
                    Showing <span class="font-medium">{{ showingFrom }}</span> to 
                    <span class="font-medium">{{ showingTo }}</span> of 
                    <span class="font-medium">{{ pagination.total }}</span> results
                </p>
                
                <nav class="flex items-center gap-1">
                    <!-- Previous Button -->
                    <button 
                        :disabled="pagination.current_page === 1"
                        class="p-2 rounded-lg text-slate-600 hover:bg-slate-200 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        @click="handlePageChange(pagination.current_page - 1)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    
                    <!-- Page Numbers -->
                    <template v-for="page in pages" :key="page">
                        <span v-if="page === '...'" class="px-2 text-slate-400">...</span>
                        <button 
                            v-else
                            :class="[
                                'min-w-[36px] h-9 rounded-lg text-sm font-medium transition-colors',
                                page === pagination.current_page 
                                    ? 'bg-blue-600 text-white' 
                                    : 'text-slate-600 hover:bg-slate-200'
                            ]"
                            @click="handlePageChange(page)"
                        >
                            {{ page }}
                        </button>
                    </template>
                    
                    <!-- Next Button -->
                    <button 
                        :disabled="pagination.current_page === pagination.last_page"
                        class="p-2 rounded-lg text-slate-600 hover:bg-slate-200 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                        @click="handlePageChange(pagination.current_page + 1)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>
