<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { formatMoney } from '@/utils/formatMoney';
import {
    TrendingUp,
    TrendingDown,
    Minus,
    Wallet,
    Package,
    Users,
    BarChart3,
    Clock,
    ClipboardList,
    Eye,
    FileText,
    X,
    Image,
    Calendar,
    User,
    DollarSign,
    ShoppingBag,
    ArrowUpRight,
    ArrowDownRight
} from 'lucide-vue-next';

const props = defineProps({
    dailySalesTotal: {
        type: Number,
        default: 0,
    },
    salesTrend: {
        type: Object,
        default: () => ({
            today: 0,
            yesterday: 0,
            trend_percent: 0,
            trend_direction: 'flat',
        }),
    },
    pendingBoxOrders: {
        type: Array,
        default: () => [],
    },
    boxOrderStats: {
        type: Object,
        default: () => ({}),
    },
    todaySessions: {
        type: Array,
        default: () => [],
    },
    quickStats: {
        type: Object,
        default: () => ({}),
    },
    dailyRevenue: {
        type: Array,
        default: () => [],
    },
    weeklyRevenue: {
        type: Array,
        default: () => [],
    },
    monthlyRevenue: {
        type: Array,
        default: () => [],
    },
    globalProfit: {
        type: Object,
        default: () => ({}),
    },
    sessionHistory: {
        type: Array,
        default: () => [],
    },
    boxOrderHistory: {
        type: Array,
        default: () => [],
    },
});

const chartTab = ref('daily');
const hoveredPoint = ref(null);

// Get data based on selected tab
const chartData = computed(() => {
    switch (chartTab.value) {
        case 'daily':
            return props.dailyRevenue;
        case 'weekly':
            return props.weeklyRevenue;
        case 'monthly':
            return props.monthlyRevenue;
        default:
            return props.dailyRevenue;
    }
});

// Calculate max value for chart scaling
const chartMax = computed(() => Math.max(...chartData.value.map(d => d.revenue), 1));

// SVG Line Chart calculations
const chartWidth = 600;
const chartHeight = 200;
const padding = 40;

const linePoints = computed(() => {
    if (chartData.value.length === 0) return '';
    
    const points = chartData.value.map((d, i) => {
        const x = padding + (i * (chartWidth - padding * 2)) / (chartData.value.length - 1 || 1);
        const y = chartHeight - padding - ((d.revenue / chartMax.value) * (chartHeight - padding * 2));
        return `${x},${y}`;
    });
    
    return points.join(' ');
});

const areaPath = computed(() => {
    if (chartData.value.length === 0) return '';
    
    const points = chartData.value.map((d, i) => {
        const x = padding + (i * (chartWidth - padding * 2)) / (chartData.value.length - 1 || 1);
        const y = chartHeight - padding - ((d.revenue / chartMax.value) * (chartHeight - padding * 2));
        return { x, y };
    });
    
    let path = `M ${points[0].x},${chartHeight - padding}`;
    path += ` L ${points[0].x},${points[0].y}`;
    points.forEach((p, i) => {
        if (i > 0) path += ` L ${p.x},${p.y}`;
    });
    path += ` L ${points[points.length - 1].x},${chartHeight - padding}`;
    path += ' Z';
    
    return path;
});

// Format date label for chart
const formatDateLabel = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const day = date.getDate();
    const month = date.getMonth() + 1;
    return `${day}/${month}`;
};

const getStatusBadge = (status) => {
    const badges = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200',
        paid: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
        completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200',
        open: 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200',
        closed: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    };
    return badges[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
};

const getStatusText = (status) => {
    const texts = {
        pending: 'Menunggu',
        paid: 'Lunas',
        completed: 'Selesai',
        cancelled: 'Batal',
        open: 'Aktif',
        closed: 'Tutup',
    };
    return texts[status] || status;
};

// Detail Modal States
const showSessionModal = ref(false);
const selectedSession = ref(null);
const showOrderModal = ref(false);
const selectedOrder = ref(null);

// Open Session Detail Modal
const openSessionDetail = (session) => {
    selectedSession.value = session;
    showSessionModal.value = true;
};

// Close Session Modal
const closeSessionModal = () => {
    showSessionModal.value = false;
    selectedSession.value = null;
};

// Open Order Detail Modal
const openOrderDetail = (order) => {
    selectedOrder.value = order;
    showOrderModal.value = true;
};

// Close Order Modal
const closeOrderModal = () => {
    showOrderModal.value = false;
    selectedOrder.value = null;
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-foreground">Dashboard</h2>
        </template>

        <!-- Quick Stats with Icons and Orange Theme -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Penjualan Hari Ini -->
            <div class="bg-card rounded-xl shadow-sm border border-border p-6 transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-lg cursor-default">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <DollarSign class="w-6 h-6 text-white" />
                    </div>
                    <div 
                        v-if="salesTrend.trend_direction !== 'flat'"
                        :class="[
                            'flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium',
                            salesTrend.trend_direction === 'up' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'
                        ]"
                    >
                        <TrendingUp v-if="salesTrend.trend_direction === 'up'" class="w-3 h-3" />
                        <TrendingDown v-else class="w-3 h-3" />
                        {{ Math.abs(salesTrend.trend_percent) }}%
                    </div>
                    <div v-else class="flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-muted text-muted-foreground">
                        <Minus class="w-3 h-3" />
                        0%
                    </div>
                </div>
                <h3 class="text-sm font-medium text-muted-foreground">Penjualan Hari Ini</h3>
                <p class="text-2xl font-bold text-foreground mt-1">
                    {{ formatMoney(dailySalesTotal) }}
                </p>
                <p class="text-xs text-muted-foreground mt-1">vs kemarin</p>
            </div>

            <!-- Profit Hari Ini -->
            <div class="bg-card rounded-xl shadow-sm border border-border p-6 transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-lg cursor-default">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-lg shadow-orange-500/30">
                        <Wallet class="w-6 h-6 text-white" />
                    </div>
                </div>
                <h3 class="text-sm font-medium text-muted-foreground">Profit Hari Ini</h3>
                <p class="text-2xl font-bold text-foreground mt-1">
                    {{ formatMoney(globalProfit.today_profit || 0) }}
                </p>
                <p class="text-xs text-muted-foreground mt-1">
                    Konsinyasi: {{ formatMoney(globalProfit.today_session_profit || 0) }}
                </p>
            </div>

            <!-- Order Box Pending -->
            <div class="bg-card rounded-xl shadow-sm border border-border p-6 transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-lg cursor-default">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center shadow-lg shadow-amber-500/30">
                        <Package class="w-6 h-6 text-white" />
                    </div>
                    <span v-if="(boxOrderStats.pending_orders || 0) > 0" class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-200 border border-amber-200 dark:border-amber-800">
                        Menunggu
                    </span>
                </div>
                <h3 class="text-sm font-medium text-muted-foreground">Order Box Pending</h3>
                <p class="text-2xl font-bold text-foreground mt-1">
                    {{ boxOrderStats.pending_orders || 0 }}
                </p>
                <p class="text-xs text-muted-foreground mt-1">order menunggu proses</p>
            </div>

            <!-- Total Partners -->
            <div class="bg-card rounded-xl shadow-sm border border-border p-6 transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-lg cursor-default">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 flex items-center justify-center shadow-lg shadow-orange-400/30">
                        <Users class="w-6 h-6 text-white" />
                    </div>
                </div>
                <h3 class="text-sm font-medium text-muted-foreground">Total Partners</h3>
                <p class="text-2xl font-bold text-foreground mt-1">
                    {{ quickStats.total_partners || 0 }}
                </p>
                <p class="text-xs text-muted-foreground mt-1">mitra aktif</p>
            </div>
        </div>

        <!-- Revenue Line Chart -->
        <div class="bg-card rounded-xl shadow-sm border border-border mb-8">
            <div class="px-6 py-4 border-b border-border flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-md shadow-orange-500/20">
                        <BarChart3 class="w-5 h-5 text-white" />
                    </div>
                    <h3 class="text-lg font-semibold text-foreground">Trend Penjualan</h3>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="chartTab = 'daily'"
                        :class="[
                            'px-4 py-1 rounded-full text-sm transition-colors',
                            chartTab === 'daily'
                                ? 'bg-orange-500 text-white'
                                : 'bg-muted text-muted-foreground hover:bg-muted/80'
                        ]"
                    >
                        Harian
                    </button>
                    <button
                        @click="chartTab = 'weekly'"
                        :class="[
                            'px-4 py-1 rounded-full text-sm transition-colors',
                            chartTab === 'weekly'
                                ? 'bg-orange-500 text-white'
                                : 'bg-muted text-muted-foreground hover:bg-muted/80'
                        ]"
                    >
                        Mingguan
                    </button>
                    <button
                        @click="chartTab = 'monthly'"
                        :class="[
                            'px-4 py-1 rounded-full text-sm transition-colors',
                            chartTab === 'monthly'
                                ? 'bg-orange-500 text-white'
                                : 'bg-muted text-muted-foreground hover:bg-muted/80'
                        ]"
                    >
                        Bulanan
                    </button>
                </div>
            </div>
            <div class="p-6">
                <!-- SVG Line Chart -->
                <svg :viewBox="`0 0 ${chartWidth} ${chartHeight + 20}`" class="w-full h-72">
                    <!-- Grid lines -->
                    <line 
                        v-for="i in 4" 
                        :key="'grid-' + i"
                        :x1="padding" 
                        :y1="padding + ((i - 1) * (chartHeight - padding * 2) / 3)"
                        :x2="chartWidth - padding" 
                        :y2="padding + ((i - 1) * (chartHeight - padding * 2) / 3)"
                        class="stroke-border dark:stroke-border/40" 
                        stroke-width="1"
                    />
                    
                    <!-- Area fill -->
                    <path 
                        :d="areaPath"
                        fill="url(#gradient)"
                        opacity="0.3"
                    />
                    
                    <!-- Line -->
                    <polyline
                        :points="linePoints"
                        fill="none"
                        stroke="#3b82f6"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    
                    <!-- Data points -->
                    <g v-for="(d, i) in chartData" :key="i">
                        <!-- Invisible larger circle for easier hover -->
                        <circle
                            :cx="padding + (i * (chartWidth - padding * 2)) / (chartData.length - 1 || 1)"
                            :cy="chartHeight - padding - ((d.revenue / chartMax) * (chartHeight - padding * 2))"
                            r="15"
                            fill="transparent"
                            class="cursor-pointer"
                            @mouseenter="hoveredPoint = { ...d, index: i }"
                            @mouseleave="hoveredPoint = null"
                        />
                        <!-- Visible data point -->
                        <circle
                            :cx="padding + (i * (chartWidth - padding * 2)) / (chartData.length - 1 || 1)"
                            :cy="chartHeight - padding - ((d.revenue / chartMax) * (chartHeight - padding * 2))"
                            r="5"
                            fill="#3b82f6"
                            class="stroke-white dark:stroke-card pointer-events-none"
                            stroke-width="2"
                        />
                        <!-- Label -->
                        <text
                            :x="padding + (i * (chartWidth - padding * 2)) / (chartData.length - 1 || 1)"
                            :y="chartHeight - 10"
                            text-anchor="middle"
                            class="text-xs fill-muted-foreground pointer-events-none"
                            font-weight="500"
                        >
                            {{ d.label }}
                        </text>
                        <!-- Date below label (for daily view) -->
                        <text
                            v-if="chartTab === 'daily' && d.full_date"
                            :x="padding + (i * (chartWidth - padding * 2)) / (chartData.length - 1 || 1)"
                            :y="chartHeight + 5"
                            text-anchor="middle"
                            class="text-[10px] fill-muted-foreground pointer-events-none"
                        >
                            {{ formatDateLabel(d.full_date) }}
                        </text>
                        <!-- Date range for weekly/monthly view -->
                        <text
                            v-if="chartTab !== 'daily' && d.full_date"
                            :x="padding + (i * (chartWidth - padding * 2)) / (chartData.length - 1 || 1)"
                            :y="chartHeight + 5"
                            text-anchor="middle"
                            class="text-[10px] fill-muted-foreground pointer-events-none"
                        >
                            {{ formatDateLabel(d.full_date) }}
                        </text>
                    </g>
                    
                    <!-- Gradient definition -->
                    <defs>
                        <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:0.4" />
                            <stop offset="100%" style="stop-color:#3b82f6;stop-opacity:0" />
                        </linearGradient>
                    </defs>
                </svg>
                
                <!-- Tooltip -->
                <div
                    v-if="hoveredPoint"
                    class="mt-4 p-4 bg-popover border border-border rounded-lg transition-all"
                >
                    <div class="text-sm font-semibold text-foreground mb-2">
                        {{ hoveredPoint.label }}
                        <span v-if="hoveredPoint.full_date && chartTab === 'daily'" class="text-muted-foreground font-normal">
                            ({{ hoveredPoint.full_date }})
                        </span>
                        <span v-if="hoveredPoint.date_range" class="text-muted-foreground font-normal block text-xs">
                            {{ hoveredPoint.date_range }}
                        </span>
                    </div>
                    <div class="grid grid-cols-3 gap-3 text-sm">
                        <div>
                            <div class="text-muted-foreground text-xs">Total</div>
                            <div class="font-bold text-orange-600 dark:text-orange-400">{{ formatMoney(hoveredPoint.revenue) }}</div>
                        </div>
                        <div>
                            <div class="text-muted-foreground text-xs">Konsinyasi</div>
                            <div class="font-semibold text-green-600 dark:text-green-400">{{ formatMoney(hoveredPoint.session_revenue) }}</div>
                        </div>
                        <div>
                            <div class="text-muted-foreground text-xs">Box Order</div>
                            <div class="font-semibold text-purple-600 dark:text-purple-400">{{ formatMoney(hoveredPoint.box_revenue) }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Info text when no hover -->
                <div v-else class="mt-4 text-center text-sm text-muted-foreground">
                    Arahkan kursor ke titik data untuk melihat detail
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Session History -->
            <div class="bg-card rounded-xl shadow-sm border border-border">
                <div class="px-6 py-4 border-b border-border flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-md shadow-emerald-500/20">
                            <ClipboardList class="w-5 h-5 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Riwayat Sesi</h3>
                    </div>
                    <a
                        href="/admin/reports/daily"
                        target="_blank"
                        class="flex items-center gap-1 text-sm text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 transition-colors"
                    >
                        <FileText class="w-4 h-4" />
                        Laporan Hari Ini
                    </a>
                </div>
                <div class="p-4 max-h-96 overflow-y-auto custom-scrollbar">
                    <div v-if="sessionHistory.length === 0" class="text-muted-foreground text-center py-8">
                        Belum ada riwayat sesi
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="session in sessionHistory"
                            :key="session.id"
                            class="border border-border rounded-lg p-3 hover:bg-muted/50 transition"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="font-medium text-sm text-foreground">{{ session.user?.name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ new Date(session.opened_at).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }) }}
                                    </p>
                                </div>
                                <span :class="['px-2 py-1 text-xs rounded-full', getStatusBadge(session.status)]">
                                    {{ getStatusText(session.status) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-xs mb-2">
                                <div>
                                    <span class="text-muted-foreground">Item:</span>
                                    <span class="font-medium text-foreground"> {{ session.consignments?.length || 0 }}</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Terjual:</span>
                                    <span class="font-medium text-green-600 dark:text-green-400"> {{ session.consignments?.reduce((sum, c) => sum + (c.qty_sold || 0), 0) || 0 }}</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Revenue:</span>
                                    <span class="font-medium text-emerald-600 dark:text-emerald-400"> {{ formatMoney(session.consignments?.reduce((sum, c) => sum + (c.subtotal_income || 0), 0) || 0) }}</span>
                                </div>
                            </div>
                            <div class="flex justify-end gap-2 pt-2 border-t border-border">
                                <button
                                    @click="openSessionDetail(session)"
                                    class="flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground px-2 py-1 rounded hover:bg-muted"
                                >
                                    <Eye class="w-3 h-3" />
                                    Detail
                                </button>
                                <a
                                    v-if="session.status === 'closed'"
                                    :href="`/admin/reports/session/${session.id}`"
                                    target="_blank"
                                    class="flex items-center gap-1 text-xs text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 px-2 py-1 rounded hover:bg-orange-50 dark:hover:bg-orange-950/30 transition-colors"
                                >
                                    <FileText class="w-3 h-3" />
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box Order History -->
            <div class="bg-card rounded-xl shadow-sm border border-border">
                <div class="px-6 py-4 border-b border-border">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-md shadow-orange-500/20">
                            <Package class="w-5 h-5 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-foreground">Riwayat Box Order</h3>
                    </div>
                </div>
                <div class="p-4 max-h-96 overflow-y-auto custom-scrollbar">
                    <div v-if="boxOrderHistory.length === 0" class="text-muted-foreground text-center py-8">
                        Belum ada riwayat order
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="order in boxOrderHistory"
                            :key="order.id"
                            :class="[
                                'border rounded-lg p-3 transition',
                                order.status === 'pending' ? 'border-yellow-200 bg-yellow-50 dark:bg-yellow-500/10 dark:border-yellow-500/20' : 'border-border hover:bg-muted/50'
                            ]"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="font-medium text-sm text-foreground">{{ order.customer_name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        Pickup: {{ new Date(order.pickup_datetime).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
                                    </p>
                                </div>
                                <span :class="['px-2 py-1 text-xs rounded-full', getStatusBadge(order.status)]">
                                    {{ getStatusText(order.status) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-xs mb-2">
                                <div>
                                    <span class="text-muted-foreground">Item:</span>
                                    <span class="font-medium text-foreground"> {{ order.items?.length || 0 }}</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Qty:</span>
                                    <span class="font-medium text-foreground"> {{ order.quantity || 1 }} box</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground">Total:</span>
                                    <span class="font-medium text-green-600 dark:text-green-400"> {{ formatMoney(order.total_price) }}</span>
                                </div>
                            </div>
                            <!-- Show items summary -->
                            <div v-if="order.items?.length > 0" class="text-xs text-muted-foreground mb-2">
                                {{ order.items.map(i => i.product_name).join(', ') }}
                            </div>
                            <!-- Show cancellation reason if cancelled -->
                            <div v-if="order.status === 'cancelled' && order.cancellation_reason" class="text-xs text-red-600 dark:text-red-400 mb-2 p-2 bg-red-50 dark:bg-red-950/30 rounded border border-red-100 dark:border-red-900">
                                Alasan: {{ order.cancellation_reason }}
                            </div>
                            <div class="flex justify-end gap-2 pt-2 border-t border-border">
                                <button
                                    @click="openOrderDetail(order)"
                                    class="flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground px-2 py-1 rounded hover:bg-muted"
                                >
                                    <Eye class="w-3 h-3" />
                                    Detail
                                </button>
                                <a
                                    :href="`/pos/box/${order.id}/receipt`"
                                    target="_blank"
                                    class="flex items-center gap-1 text-xs text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 px-2 py-1 rounded hover:bg-orange-50 dark:hover:bg-orange-950/30 transition-colors"
                                >
                                    <FileText class="w-3 h-3" />
                                    Kwitansi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Box Order Stats -->
        <div class="bg-card rounded-xl shadow-sm border border-border p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center shadow-md shadow-amber-500/20">
                    <ShoppingBag class="w-5 h-5 text-white" />
                </div>
                <h3 class="text-lg font-semibold text-foreground">Statistik Box Order</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-orange-50 dark:bg-orange-950/20 rounded-xl border border-orange-100 dark:border-orange-800">
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ boxOrderStats.today_orders || 0 }}</p>
                    <p class="text-sm text-muted-foreground">Order Hari Ini</p>
                </div>
                <div class="text-center p-4 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 dark:border-emerald-800">
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ formatMoney(boxOrderStats.today_revenue || 0) }}</p>
                    <p class="text-sm text-muted-foreground">Revenue Hari Ini</p>
                </div>
                <div class="text-center p-4 bg-amber-50 dark:bg-amber-950/20 rounded-xl border border-amber-100 dark:border-amber-800">
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ boxOrderStats.month_orders || 0 }}</p>
                    <p class="text-sm text-muted-foreground">Order Bulan Ini</p>
                </div>
                <div class="text-center p-4 bg-orange-50 dark:bg-orange-950/20 rounded-xl border border-orange-100 dark:border-orange-800">
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ formatMoney(boxOrderStats.month_revenue || 0) }}</p>
                    <p class="text-sm text-muted-foreground">Revenue Bulan Ini</p>
                </div>
            </div>
        </div>

        <!-- Session Detail Modal -->
            <div 
                v-if="showSessionModal" 
                class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                @click.self="closeSessionModal"
            >
                <div class="bg-card rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-border shadow-2xl">
                    <div class="p-4 border-b border-border flex justify-between items-center sticky top-0 bg-card z-10">
                        <div>
                            <h3 class="font-semibold text-foreground">Detail Sesi Toko</h3>
                            <p class="text-sm text-muted-foreground">{{ selectedSession?.user?.name }}</p>
                        </div>
                        <button @click="closeSessionModal" class="p-1 text-muted-foreground hover:text-foreground rounded hover:bg-muted transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="p-4" v-if="selectedSession">
                        <!-- Session Info -->
                        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                            <div>
                                <span class="text-muted-foreground">Dibuka:</span>
                                <span class="font-medium ml-1 text-foreground">{{ new Date(selectedSession.opened_at).toLocaleString('id-ID') }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Status:</span>
                                <span :class="['ml-1 px-2 py-0.5 text-xs rounded-full', getStatusBadge(selectedSession.status)]">
                                    {{ getStatusText(selectedSession.status) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Kas Awal:</span>
                                <span class="font-medium ml-1 text-foreground">{{ formatMoney(selectedSession.opening_cash) }}</span>
                            </div>
                            <div v-if="selectedSession.status === 'closed'">
                                <span class="text-muted-foreground">Ditutup:</span>
                                <span class="font-medium ml-1 text-foreground">{{ new Date(selectedSession.closed_at).toLocaleString('id-ID') }}</span>
                            </div>
                        </div>

                        <!-- Consignment Items -->
                        <h4 class="font-medium text-foreground mb-2">Barang Konsinyasi</h4>
                        <div v-if="selectedSession.consignments?.length === 0" class="text-muted-foreground text-center py-4">
                            Tidak ada barang
                        </div>
                        <div v-else class="border border-border rounded-lg overflow-hidden mb-4">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-foreground font-medium">Produk</th>
                                        <th class="text-center px-3 py-2 text-foreground font-medium">Awal</th>
                                        <th class="text-center px-3 py-2 text-foreground font-medium">Terjual</th>
                                        <th class="text-center px-3 py-2 text-foreground font-medium">Sisa</th>
                                        <th class="text-right px-3 py-2 text-foreground font-medium">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="item in selectedSession.consignments" :key="item.id" class="hover:bg-muted/30">
                                        <td class="px-3 py-2 text-foreground">{{ item.product_name }}</td>
                                        <td class="text-center px-3 py-2 text-foreground">{{ item.qty_initial }}</td>
                                        <td class="text-center px-3 py-2 text-green-600 font-medium">{{ item.qty_sold }}</td>
                                        <td class="text-center px-3 py-2 text-foreground">{{ item.qty_remaining }}</td>
                                        <td class="text-right px-3 py-2 font-medium text-foreground">{{ formatMoney(item.subtotal_income) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-muted/50 font-medium border-t border-border">
                                    <tr>
                                        <td class="px-3 py-2 text-foreground">Total</td>
                                        <td class="text-center px-3 py-2 text-foreground">{{ selectedSession.consignments?.reduce((s, c) => s + c.qty_initial, 0) }}</td>
                                        <td class="text-center px-3 py-2 text-green-600">{{ selectedSession.consignments?.reduce((s, c) => s + c.qty_sold, 0) }}</td>
                                        <td class="text-center px-3 py-2 text-foreground">{{ selectedSession.consignments?.reduce((s, c) => s + c.qty_remaining, 0) }}</td>
                                        <td class="text-right px-3 py-2 text-emerald-600">{{ formatMoney(selectedSession.consignments?.reduce((s, c) => s + c.subtotal_income, 0)) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-4 border-t border-border">
                            <button @click="closeSessionModal" class="px-4 py-2 border border-border rounded-lg hover:bg-muted text-foreground transition-colors">Tutup</button>
                            <a
                                v-if="selectedSession.status === 'closed'"
                                :href="`/admin/reports/session/${selectedSession.id}`"
                                target="_blank"
                                class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 shadow-md transition-all hover:scale-105"
                            >
                                <FileText class="w-4 h-4" />
                                Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Order Detail Modal -->
        <Teleport to="body">
            <div 
                v-if="showOrderModal" 
                class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                @click.self="closeOrderModal"
            >
                <div class="bg-card rounded-xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-border shadow-2xl">
                    <div class="p-4 border-b border-border flex justify-between items-center sticky top-0 bg-card z-10">
                        <div>
                            <h3 class="font-semibold text-foreground">Detail Box Order</h3>
                            <p class="text-sm text-muted-foreground">{{ selectedOrder?.customer_name }}</p>
                        </div>
                        <button @click="closeOrderModal" class="p-1 text-muted-foreground hover:text-foreground rounded hover:bg-muted transition-colors">
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                    <div class="p-4" v-if="selectedOrder">
                        <!-- Order Info -->
                        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                            <div>
                                <span class="text-muted-foreground">Order ID:</span>
                                <span class="font-medium ml-1 text-foreground">#{{ selectedOrder.id }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Status:</span>
                                <span :class="['ml-1 px-2 py-0.5 text-xs rounded-full', getStatusBadge(selectedOrder.status)]">
                                    {{ getStatusText(selectedOrder.status) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Pickup:</span>
                                <span class="font-medium ml-1 text-foreground">{{ new Date(selectedOrder.pickup_datetime).toLocaleString('id-ID') }}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Qty Box:</span>
                                <span class="font-medium ml-1 text-foreground">{{ selectedOrder.quantity }}</span>
                            </div>
                        </div>

                        <!-- Cancellation Reason -->
                        <div v-if="selectedOrder.status === 'cancelled' && selectedOrder.cancellation_reason" class="mb-4 p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg">
                            <p class="text-sm text-red-800 dark:text-red-300">
                                <span class="font-medium">Alasan Pembatalan:</span> {{ selectedOrder.cancellation_reason }}
                            </p>
                        </div>

                        <!-- Order Items -->
                        <h4 class="font-medium text-foreground mb-2">Item Order</h4>
                        <div v-if="selectedOrder.items?.length === 0" class="text-muted-foreground text-center py-4">
                            Tidak ada item
                        </div>
                        <div v-else class="border border-border rounded-lg overflow-hidden mb-4">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th class="text-left px-3 py-2 text-foreground font-medium">Produk</th>
                                        <th class="text-center px-3 py-2 text-foreground font-medium">Qty</th>
                                        <th class="text-right px-3 py-2 text-foreground font-medium">Harga</th>
                                        <th class="text-right px-3 py-2 text-foreground font-medium">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="item in selectedOrder.items" :key="item.id" class="hover:bg-muted/30">
                                        <td class="px-3 py-2 text-foreground">{{ item.product_name }}</td>
                                        <td class="text-center px-3 py-2 text-foreground">{{ item.quantity }}</td>
                                        <td class="text-right px-3 py-2 text-foreground">{{ formatMoney(item.unit_price) }}</td>
                                        <td class="text-right px-3 py-2 font-medium text-foreground">{{ formatMoney(item.subtotal) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-muted/50 font-medium border-t border-border">
                                    <tr>
                                        <td colspan="3" class="px-3 py-2 text-right text-foreground">Total:</td>
                                        <td class="text-right px-3 py-2 text-green-600 text-lg">{{ formatMoney(selectedOrder.total_price) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Payment Proof -->
                        <div v-if="selectedOrder.payment_proof_path" class="mb-4">
                            <h4 class="font-medium text-foreground mb-2">Bukti Pembayaran</h4>
                            <a 
                                :href="`/storage/${selectedOrder.payment_proof_path}`" 
                                target="_blank"
                                class="flex items-center gap-2 text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 text-sm transition-colors"
                            >
                                <Image class="w-4 h-4" />
                                Lihat Bukti Pembayaran
                            </a>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-4 border-t border-border">
                            <button @click="closeOrderModal" class="px-4 py-2 border border-border rounded-lg hover:bg-muted text-foreground transition-colors">Tutup</button>
                            <a
                                :href="`/pos/box/${selectedOrder.id}/receipt`"
                                target="_blank"
                                class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 shadow-md transition-all hover:scale-105"
                            >
                                <FileText class="w-4 h-4" />
                                Download Kwitansi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
