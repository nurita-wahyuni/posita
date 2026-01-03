<!--
  Created/Modified by: Muhammad Ammar Alfarabi
  NIM: 202312056
  Feature: Close Shop - Tutup sesi toko, rekap penjualan, verifikasi uang fisik
-->
<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, reactive } from 'vue';
import { formatMoney } from '@/utils/formatMoney';
import { AlertTriangle, BarChart3, Package, Wallet, Lock, Store, Loader2, CheckCircle, AlertCircle, FileText } from 'lucide-vue-next';
import ActionButton from '@/Components/ActionButton.vue';

const props = defineProps({
    hasSession: {
        type: Boolean,
        default: false,
    },
    currentSession: {
        type: Object,
        default: null,
    },
    consignments: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: null,
    },
});

// Reactive object to track leftover qty for each item
const leftoverQty = reactive({});

// Initialize leftover quantities from consignments
props.consignments.forEach(item => {
    // Default to qty_initial (nothing sold) - user will input actual leftover
    leftoverQty[item.id] = item.qty_remaining ?? item.qty_initial;
});

const form = useForm({
    actual_cash: '',
    notes: '',
});

// Calculate sold quantity for an item
const getQtySold = (item) => {
    const leftover = parseInt(leftoverQty[item.id]) || 0;
    return Math.max(0, item.qty_initial - leftover);
};

// Calculate income for an item
const getItemIncome = (item) => {
    return getQtySold(item) * item.selling_price;
};

// Calculate profit for an item
const getItemProfit = (item) => {
    return getQtySold(item) * (item.selling_price - item.base_price);
};

// Calculated totals based on current leftover inputs
const calculatedTotals = computed(() => {
    let totalIncome = 0;
    let totalProfit = 0;
    let totalSold = 0;

    props.consignments.forEach(item => {
        const qtySold = getQtySold(item);
        totalSold += qtySold;
        totalIncome += qtySold * item.selling_price;
        totalProfit += qtySold * (item.selling_price - item.base_price);
    });

    const expectedCash = (props.summary?.opening_cash || 0) + totalIncome;

    return {
        total_income: totalIncome,
        total_profit: totalProfit,
        total_sold: totalSold,
        opening_cash: props.summary?.opening_cash || 0,
        expected_cash: expectedCash,
    };
});

// Cash difference: expected - actual (positive = kurang/shortage, negative = lebih/overage)
const cashDifference = computed(() => {
    if (!form.actual_cash) return 0;
    return calculatedTotals.value.expected_cash - parseFloat(form.actual_cash);
});

const submit = () => {
    // Build leftovers array from reactive object
    const leftovers = props.consignments.map(item => ({
        id: item.id,
        qty_remaining: parseInt(leftoverQty[item.id]) || 0,
    }));

    form.transform(data => ({
        ...data,
        leftovers: leftovers,
    })).post('/pos/close');
};

// Validate leftover doesn't exceed initial
const validateLeftover = (item) => {
    const max = item.qty_initial;
    if (leftoverQty[item.id] > max) {
        leftoverQty[item.id] = max;
    }
    if (leftoverQty[item.id] < 0) {
        leftoverQty[item.id] = 0;
    }
};

// Session info for sidebar status
const sessionInfo = computed(() => ({
  shiftName: props.currentSession?.shift_name ?? 'Sesi Aktif',
  openingBalance: props.currentSession?.opening_cash ?? props.summary?.opening_cash ?? 0,
  isActive: props.hasSession,
  openedAt: props.currentSession?.opened_at ?? null,
}));
</script>

<template>
    <Head title="Tutup Toko" />

    <EmployeeLayout :session-info="sessionInfo">
        <template #header>
            <div class="flex items-center gap-2">
                <Lock class="w-6 h-6 text-gray-600" />
                <h2 class="text-lg font-semibold text-gray-800">Tutup Toko</h2>
            </div>
        </template>

        <div class="p-4 lg:p-6">
            <!-- No active session -->
            <div v-if="!hasSession" class="max-w-lg mx-auto bg-orange-50 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-800 rounded-lg p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/50 flex items-center justify-center mx-auto mb-4">
                    <AlertTriangle class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                </div>
                <h3 class="text-lg font-semibold text-orange-800 dark:text-orange-200 mb-2">
                    Tidak Ada Sesi Aktif
                </h3>
                <p class="text-orange-600 dark:text-orange-300 mb-4">
                    Anda belum membuka toko hari ini
                </p>
                <Link
                    href="/pos/open"
                    class="inline-flex items-center gap-2 bg-emerald-600 text-white px-6 py-2 rounded-lg hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all font-semibold"
                >
                    <Store class="w-5 h-5" />
                    Buka Toko
                </Link>
            </div>

            <!-- Close session form -->
            <form v-else @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto">
                
                <!-- LEFT COLUMN: INPUTS -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Items with Leftover Input -->
                    <div class="bg-card rounded-xl shadow-sm border border-border overflow-hidden">
                        <div class="px-6 py-4 border-b border-border bg-muted/30">
                            <h3 class="font-semibold text-foreground flex items-center gap-2">
                                <Package class="w-5 h-5 text-primary" />
                                Input Sisa Stok
                            </h3>
                            <p class="text-sm text-muted-foreground mt-1">Masukkan jumlah sisa stok untuk menghitung penjualan</p>
                        </div>
                        
                        <div class="p-6">
                            <div v-if="consignments.length === 0" class="text-muted-foreground text-center py-8 bg-muted/20 rounded-lg border-2 border-dashed border-border/50">
                                <Package class="w-12 h-12 mx-auto mb-2 opacity-50" />
                                Belum ada barang
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="item in consignments"
                                    :key="item.id"
                                    class="bg-background border border-border rounded-xl p-4 hover:border-primary/50 transition-colors"
                                >
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <p class="font-bold text-foreground">{{ item.product_name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ item.partner?.name }}</p>
                                        </div>
                                        <p class="text-sm font-medium text-muted-foreground">@ {{ formatMoney(item.selling_price) }}</p>
                                    </div>
                                    
                                    <div class="grid grid-cols-3 gap-4 items-end">
                                        <div class="bg-muted/50 rounded-lg p-2 text-center border border-border/50">
                                            <p class="text-muted-foreground text-[10px] uppercase font-bold tracking-wider mb-1">Stok Awal</p>
                                            <p class="font-bold text-lg text-foreground">{{ item.qty_initial }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-muted-foreground text-[10px] uppercase font-bold tracking-wider mb-1">Sisa Stok</label>
                                            <input
                                                v-model.number="leftoverQty[item.id]"
                                                @blur="validateLeftover(item)"
                                                type="number"
                                                :min="0"
                                                :max="item.qty_initial"
                                                class="w-full border-2 border-input bg-background rounded-lg px-3 py-2 text-center font-bold text-lg focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all"
                                            />
                                        </div>
                                        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-2 text-center border border-emerald-100 dark:border-emerald-800">
                                            <p class="text-emerald-600/70 dark:text-emerald-400/70 text-[10px] uppercase font-bold tracking-wider mb-1">Terjual</p>
                                            <p class="font-bold text-lg text-emerald-600 dark:text-emerald-400">{{ getQtySold(item) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cash Verification Card -->
                    <div class="bg-card rounded-xl shadow-sm border border-border overflow-hidden">
                        <div class="px-6 py-4 border-b border-border bg-muted/30">
                            <h3 class="font-semibold text-foreground flex items-center gap-2">
                                <Wallet class="w-5 h-5 text-primary" />
                                Verifikasi Uang Tunai
                            </h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-foreground mb-2">
                                    Uang Tunai Fisik (Actual Cash)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground font-bold text-lg">
                                        Rp
                                    </span>
                                    <input
                                        v-model="form.actual_cash"
                                        type="number"
                                        min="0"
                                        step="1"
                                        class="w-full border-2 border-input bg-background rounded-xl pl-12 pr-4 py-3 text-xl font-bold tracking-wide focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all placeholder:text-muted-foreground/50"
                                        placeholder="0"
                                        required
                                    />
                                </div>
                                <p class="text-xs text-muted-foreground mt-2">Hitung total uang tunai yang ada di laci kasir saat ini.</p>
                                <p v-if="form.errors.actual_cash" class="text-sm text-red-500 mt-1 font-medium">
                                    {{ form.errors.actual_cash }}
                                </p>
                            </div>

                            <div>
                                <label class="flex items-center gap-2 text-sm font-semibold text-foreground mb-2">
                                    <FileText class="w-4 h-4" />
                                    Catatan / Alasan Selisih
                                </label>
                                <textarea
                                    v-model="form.notes"
                                    class="w-full border-2 border-input bg-background rounded-xl px-4 py-3 focus:ring-4 focus:ring-primary/20 focus:border-primary transition-all resize-none"
                                    rows="3"
                                    placeholder="Contoh: Ada pengeluaran beli air galon 20rb..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: SUMMARY (STICKY) -->
                <div class="md:col-span-1">
                    <div class="sticky top-4 space-y-6">
                        <!-- Summary Card -->
                        <div class="bg-card rounded-xl shadow-lg shadow-black/5 border border-border overflow-hidden">
                            <div class="px-5 py-4 border-b border-border bg-gradient-to-r from-primary/5 to-transparent">
                                <h3 class="font-bold text-foreground flex items-center gap-2">
                                    <BarChart3 class="w-5 h-5 text-primary" />
                                    Ringkasan
                                </h3>
                            </div>
                            
                            <!-- Key Metrics -->
                            <div class="grid grid-cols-2 border-b border-border">
                                <div class="p-4 border-r border-border hover:bg-muted/20 transition-colors">
                                    <p class="text-xs text-muted-foreground uppercase font-semibold mb-1">Total Terjual</p>
                                    <p class="text-xl font-bold text-foreground">{{ calculatedTotals.total_sold }}</p>
                                </div>
                                <div class="p-4 hover:bg-muted/20 transition-colors">
                                    <p class="text-xs text-muted-foreground uppercase font-semibold mb-1">Total Profit</p>
                                    <p class="text-xl font-bold text-emerald-600">{{ formatMoney(calculatedTotals.total_profit) }}</p>
                                </div>
                            </div>

                            <div class="p-5 space-y-4">
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground">Kas Awal</span>
                                        <span class="font-medium">{{ formatMoney(calculatedTotals.opening_cash) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-muted-foreground">Pendapatan</span>
                                        <span class="font-medium text-emerald-600">+ {{ formatMoney(calculatedTotals.total_income) }}</span>
                                    </div>
                                    
                                    <div class="h-px bg-border my-2"></div>
                                    
                                    <div class="flex justify-between items-center text-base">
                                        <span class="font-bold text-foreground">Total Sistem</span>
                                        <span class="font-bold text-foreground">{{ formatMoney(calculatedTotals.expected_cash) }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center text-sm text-muted-foreground">
                                        <span>Total Fisik</span>
                                        <span>{{ form.actual_cash ? formatMoney(form.actual_cash) : '-' }}</span>
                                    </div>
                                </div>
                                
                                <div class="border-t-2 border-dashed border-border my-4"></div>

                                <!-- Balance Status -->
                                <div 
                                    class="rounded-xl p-4 transition-all duration-300"
                                    :class="cashDifference === 0 && form.actual_cash 
                                        ? 'bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800' 
                                        : 'bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900'"
                                >
                                    <div class="flex justify-between items-center mb-1">
                                        <span 
                                            class="text-xs font-bold uppercase tracking-wider flex items-center gap-1.5"
                                            :class="cashDifference === 0 && form.actual_cash ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400'"
                                        >
                                            <component :is="cashDifference === 0 && form.actual_cash ? CheckCircle : AlertCircle" class="w-4 h-4" />
                                            {{ cashDifference === 0 && form.actual_cash ? 'BALANCE' : 'SELISIH' }}
                                        </span>
                                        <span class="text-xs font-medium text-muted-foreground" v-if="cashDifference !== 0">
                                            {{ cashDifference > 0 ? '(Kurang)' : '(Lebih)' }}
                                        </span>
                                    </div>
                                    <p 
                                        class="text-2xl font-black text-right tracking-tight"
                                        :class="cashDifference === 0 && form.actual_cash ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                                    >
                                        {{ formatMoney(Math.abs(cashDifference)) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="p-5 border-t border-border bg-muted/10">
                                <ActionButton
                                    type="submit"
                                    variant="negative"
                                    :icon="Lock"
                                    :loading="form.processing"
                                    :disabled="form.processing"
                                    full-width
                                    size="lg"
                                    class="shadow-lg shadow-red-500/20 hover:shadow-red-500/30 font-bold"
                                >
                                    {{ form.processing ? 'Memproses...' : 'Tutup Toko' }}
                                </ActionButton>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </EmployeeLayout>
</template>
