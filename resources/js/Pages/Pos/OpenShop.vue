<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { Card, CardContent, Button, Input, Label } from '@/Components/ui'
import { Store, Package, Lock, FileText, ArrowRight } from 'lucide-vue-next'
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
})

const form = useForm({
  opening_cash: '',
})

const submit = () => {
  form.post('/pos/open')
}

const sessionInfo = computed(() => ({
  shiftName: props.activeSession?.shift_name ?? null,
  openingBalance: props.activeSession?.opening_cash ?? 0,
  isActive: props.hasActiveSession,
  openedAt: props.activeSession?.opened_at ?? null,
}))

const formatMoney = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value || 0)
}
</script>

<template>
  <Head title="Buka Toko" />

  <EmployeeLayout :session-info="sessionInfo">
    <template #header>
      <h2 class="text-lg font-semibold text-foreground">Buka Toko</h2>
    </template>

    <div class="relative w-full max-w-5xl mx-auto overflow-hidden rounded-3xl shadow-2xl">
      <div class="flex flex-col lg:flex-row min-h-[500px]">
        <!-- Left Side - Hero/Illustration with Glassmorphism -->
        <div class="relative lg:w-1/2 w-full flex flex-col justify-center items-center text-white p-8 lg:p-12 bg-gradient-to-br from-primary via-primary/90 to-accent overflow-hidden">
          <!-- Floating Elements -->
          <div class="absolute top-10 left-10 w-20 h-20 bg-white/10 rounded-full blur-xl animate-float" />
          <div class="absolute bottom-20 right-10 w-32 h-32 bg-white/10 rounded-full blur-xl animate-float" style="animation-delay: 1s" />

          <!-- Glass Panel -->
          <div class="relative z-10 text-center space-y-6 glass rounded-2xl p-8 bg-white/10 border-white/20">
            <Store class="w-16 h-16 mx-auto text-white/90" />
            <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight text-white drop-shadow">
              Kantin Cerdas
            </h1>
            <p class="text-white/80 max-w-sm mx-auto leading-relaxed">
              Selamat datang di sistem kasir modern untuk kantin Anda.
              Mulailah hari dengan membuka sesi toko dan kelola transaksi dengan mudah.
            </p>
            <div class="bg-white/20 backdrop-blur-md border border-white/30 rounded-xl py-4 px-6 inline-block">
              <p class="uppercase text-xs tracking-wider text-white/70 font-semibold">Hari Ini</p>
              <p class="text-xl font-bold text-white drop-shadow-sm">
                {{ today }}
              </p>
            </div>
          </div>
        </div>

        <!-- Right Side - Form with Slide-in Animation -->
        <div class="lg:w-1/2 w-full bg-card p-8 lg:p-12 flex flex-col justify-center">
          <!-- Already has active session -->
          <div v-if="hasActiveSession" class="space-y-6 animate-fade-in">
            <div class="text-center">
              <div class="w-16 h-16 mx-auto bg-emerald-100 rounded-full flex items-center justify-center mb-4">
                <Store class="w-8 h-8 text-emerald-600" />
              </div>
              <h3 class="text-2xl font-semibold text-foreground">
                Sesi Toko Aktif
              </h3>
              <p class="text-muted-foreground mt-1">
                Anda sudah membuka toko hari ini
              </p>
            </div>

            <Card class="border-primary/20 bg-primary/5">
              <CardContent class="pt-6">
                <div class="space-y-3">
                  <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Kas Awal:</span>
                    <span class="font-bold text-foreground text-lg">
                      {{ formatMoney(activeSession?.opening_cash || 0) }}
                    </span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Dibuka:</span>
                    <span class="text-foreground">{{ activeSession?.opened_at }}</span>
                  </div>
                </div>
              </CardContent>
            </Card>

            <div class="flex flex-col sm:flex-row gap-3">
              <Button as-child class="flex-1 animate-slide-in-up" style="animation-delay: 0.1s">
                <Link href="/pos/consignment" class="flex items-center justify-center">
                  <Package class="w-5 h-5 mr-2" />
                  Input Barang Titipan
                </Link>
              </Button>
              <Button as-child variant="destructive" class="flex-1 animate-slide-in-up" style="animation-delay: 0.2s">
                <Link href="/pos/close" class="flex items-center justify-center">
                  <Lock class="w-5 h-5 mr-2" />
                  Tutup Toko
                </Link>
              </Button>
            </div>
          </div>

          <!-- Open new session form -->
          <div v-else class="space-y-6">
            <!-- Last Session Report Download -->
            <div
              v-if="lastClosedSession"
              class="flex items-center justify-between p-4 rounded-xl bg-muted border border-border animate-fade-in"
            >
              <div>
                <p class="text-sm font-medium text-foreground">Sesi Terakhir</p>
                <p class="text-xs text-muted-foreground">{{ lastClosedSession.closed_at }}</p>
              </div>
              <Button as-child variant="outline" size="sm">
                <a :href="`/pos/report/${lastClosedSession.id}`" target="_blank" class="flex items-center">
                  <FileText class="w-4 h-4 mr-1" />
                  Laporan
                </a>
              </Button>
            </div>

            <!-- Form Header -->
            <div class="text-center animate-slide-in-up stagger-1 animate-fill-both">
              <Store class="w-16 h-16 mx-auto text-primary mb-4" />
              <h3 class="text-2xl font-semibold text-foreground">Buka Sesi Toko</h3>
              <p class="text-muted-foreground mt-1">Masukkan kas awal hari ini</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5">
              <div class="space-y-2 animate-slide-in-up stagger-2 animate-fill-both">
                <Label for="opening_cash">Kas Awal (Modal)</Label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground font-medium">
                    Rp
                  </span>
                  <Input
                    id="opening_cash"
                    v-model="form.opening_cash"
                    type="number"
                    min="0"
                    step="1000"
                    :error="!!form.errors.opening_cash"
                    class="pl-10 text-lg"
                    placeholder="0"
                    required
                  />
                </div>
                <p v-if="form.errors.opening_cash" class="text-sm text-destructive">
                  {{ form.errors.opening_cash }}
                </p>
              </div>

              <Button
                type="submit"
                :loading="form.processing"
                :disabled="form.processing"
                size="lg"
                class="w-full animate-slide-in-up stagger-3 animate-fill-both"
              >
                <Store v-if="!form.processing" class="w-5 h-5 mr-2" />
                {{ form.processing ? 'Membuka...' : 'Buka Toko Sekarang' }}
                <ArrowRight v-if="!form.processing" class="w-5 h-5 ml-2" />
              </Button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </EmployeeLayout>
</template>