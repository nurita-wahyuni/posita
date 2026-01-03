<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { formatMoney } from '@/utils/formatMoney';
import { AlertTriangle, Plus, X, Store } from 'lucide-vue-next';
import ActionButton from '@/Components/ActionButton.vue';

const props = defineProps({
    hasActiveSession: {
        type: Boolean,
        default: false,
    },
    activeSession: {
        type: Object,
        default: null,
    },
    partners: {
        type: Array,
        default: () => [],
    },
    productTemplates: {
        type: Array,
        default: () => [],
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

// Session info for sidebar status
const sessionInfo = computed(() => ({
  shiftName: props.activeSession?.shift_name ?? null,
  openingBalance: props.activeSession?.opening_cash ?? 0,
  isActive: props.hasActiveSession,
  openedAt: props.activeSession?.opened_at ?? null,
}));

const showAddModal = ref(false);
const selectedPartnerId = ref('');

const form = useForm({
    partner_id: '',
    product_name: '',
    qty_initial: '',
    base_price: '',
    selling_price: '',
});

// Filter templates by selected partner
const filteredTemplates = computed(() => {
    if (!selectedPartnerId.value) return [];
    return props.productTemplates.filter(t => t.partner_id == selectedPartnerId.value);
});

const selectPartner = (partnerId) => {
    selectedPartnerId.value = partnerId;
    form.partner_id = partnerId;
};

const selectTemplate = (template) => {
    form.product_name = template.name;
    form.base_price = template.base_price;
    // Use default_selling_price if available, otherwise leave empty for manual input
    form.selling_price = template.default_selling_price || '';
};

const submit = () => {
    form.post('/pos/consignment', {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
            selectedPartnerId.value = '';
        },
    });
};
</script>

<template>
    <Head title="Barang Titipan" />

    <EmployeeLayout :session-info="sessionInfo">
        <template #header>
            <h2 class="text-lg font-semibold text-gray-800">Barang Titipan</h2>
        </template>

        <div class="p-4 lg:p-6 space-y-6">
            <!-- No active session -->
            <div v-if="!hasActiveSession" class="bg-orange-50 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-800 rounded-lg p-6 text-center">
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

            <div v-else class="space-y-6">
                <!-- Summary -->
                <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-800 rounded-lg p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Total Item</p>
                            <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-200">{{ summary?.total_items || 0 }}</p>
                            <p class="text-sm text-emerald-600/80 dark:text-emerald-400/80 mt-1">Stok Awal Total: {{ summary?.total_qty_initial || 0 }} pcs</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Estimasi Nilai Stok</p>
                            <p class="text-2xl font-bold text-emerald-800 dark:text-emerald-200">{{ formatMoney(summary?.total_stock_value) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Add Button -->
                <div>
                    <ActionButton
                        @click="showAddModal = true"
                        :icon="Plus"
                        size="lg"
                        full-width
                    >
                        Tambah Barang Titipan
                    </ActionButton>
                </div>

                <!-- Consignment List -->
                <div class="space-y-4">
                    <div v-if="consignments.length === 0" class="text-center py-12 text-muted-foreground bg-muted/30 rounded-lg border border-dashed border-border">
                        <Package class="w-12 h-12 mx-auto mb-3 opacity-20" />
                        <p>Belum ada barang hari ini</p>
                    </div>
                    <div
                        v-for="item in consignments"
                        :key="item.id"
                        class="bg-card rounded-xl shadow-sm border border-border p-5 hover:shadow-md transition-shadow"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-medium text-lg text-foreground">{{ item.product_name }}</p>
                                <p class="text-sm text-muted-foreground">{{ item.partner?.name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-green-600 font-bold text-lg">{{ formatMoney(item.selling_price) }}</p>
                                <p class="text-xs text-muted-foreground">Modal: {{ formatMoney(item.base_price) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm text-muted-foreground border-t border-border pt-3 mt-3">
                            <span>Stok Awal: <span class="font-medium text-foreground">{{ item.qty_initial }} pcs</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Modal - Employee Theme -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-card border border-border rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
                <div class="px-5 py-4 border-b border-border flex justify-between items-center sticky top-0 bg-card rounded-t-2xl">
                    <h3 class="font-semibold text-foreground">Tambah Barang</h3>
                    <button @click="showAddModal = false" class="w-8 h-8 flex items-center justify-center rounded-full text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">✕</button>
                </div>

                <form @submit.prevent="submit" class="p-5 space-y-4">
                    <!-- Partner Selection -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Pilih Penyetok</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="partner in partners"
                                :key="partner.id"
                                type="button"
                                @click="selectPartner(partner.id)"
                                :class="[
                                    'p-3 rounded-xl border text-sm font-medium transition-all',
                                    selectedPartnerId == partner.id
                                        ? 'border-primary bg-primary/10 text-primary shadow-sm'
                                        : 'border-border hover:border-primary/50 text-foreground'
                                ]"
                            >
                                {{ partner.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Template Suggestions -->
                    <div v-if="filteredTemplates.length > 0">
                        <label class="block text-sm font-medium text-foreground mb-2">Template Produk</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="template in filteredTemplates"
                                :key="template.id"
                                type="button"
                                @click="selectTemplate(template)"
                                class="px-3 py-1.5 bg-muted rounded-full text-sm font-medium text-foreground hover:bg-primary/10 hover:text-primary transition-colors"
                            >
                                {{ template.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Product Name -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Nama Produk</label>
                        <input
                            v-model="form.product_name"
                            type="text"
                            class="w-full border border-input bg-background text-foreground rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                            required
                        />
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Jumlah (pcs)</label>
                        <input
                            v-model="form.qty_initial"
                            type="number"
                            min="1"
                            class="w-full border border-input bg-background text-foreground rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                            required
                        />
                    </div>

                    <!-- Base Price (Buying Price) -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Harga Modal (Beli)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground font-medium">Rp</span>
                            <input
                                v-model="form.base_price"
                                type="number"
                                min="0"
                                class="w-full border border-input bg-background text-foreground rounded-xl pl-12 pr-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                                required
                            />
                        </div>
                    </div>

                    <!-- Selling Price (Manual Input - Replacing Markup) -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Harga Jual</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground font-medium">Rp</span>
                            <input
                                v-model="form.selling_price"
                                type="number"
                                min="0"
                                class="w-full border border-input bg-background text-foreground rounded-xl pl-12 pr-4 py-2.5 focus:ring-2 focus:ring-primary focus:border-primary transition-all"
                                placeholder="Masukkan harga jual"
                                required
                            />
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">Tentukan harga jual secara manual</p>
                    </div>

                    <!-- Profit Preview -->
                    <div v-if="form.base_price && form.selling_price" class="bg-muted/50 p-4 rounded-xl border border-border">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Profit per item:</span>
                            <span 
                                :class="(form.selling_price - form.base_price) >= 0 ? 'text-primary font-semibold' : 'text-destructive font-semibold'"
                            >
                                {{ formatMoney(form.selling_price - form.base_price) }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 pt-2">
                        <div class="flex-1">
                            <ActionButton
                                type="button"
                                variant="negative"
                                full-width
                                @click="showAddModal = false"
                            >
                                Batal
                            </ActionButton>
                        </div>
                        <div class="flex-1">
                            <ActionButton
                                type="submit"
                                full-width
                                :loading="form.processing"
                                :disabled="form.processing || !form.partner_id"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </ActionButton>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </EmployeeLayout>
</template>
