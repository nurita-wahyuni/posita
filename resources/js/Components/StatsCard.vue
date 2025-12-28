<script setup>
/**
 * StatsCard - Dashboard statistics card with icon, value, and trend indicator
 * @example
 * <StatsCard 
 *   title="Total Sales" 
 *   :value="125000" 
 *   :change="12.5" 
 *   prefix="Rp"
 *   variant="primary"
 * />
 */
import { computed } from 'vue';

const props = defineProps({
    /** Card title */
    title: {
        type: String,
        required: true,
    },
    /** Main value to display */
    value: {
        type: [Number, String],
        default: 0,
    },
    /** Prefix for the value (e.g., "Rp", "$") */
    prefix: {
        type: String,
        default: '',
    },
    /** Suffix for the value (e.g., "%", "items") */
    suffix: {
        type: String,
        default: '',
    },
    /** Percentage change (positive = increase, negative = decrease) */
    change: {
        type: Number,
        default: null,
    },
    /** Change period description */
    changePeriod: {
        type: String,
        default: 'from last period',
    },
    /** Card color variant */
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'primary', 'success', 'warning', 'danger'].includes(v),
    },
    /** Format value as currency */
    currency: {
        type: Boolean,
        default: false,
    },
    /** Loading state */
    loading: {
        type: Boolean,
        default: false,
    },
});

const variantStyles = {
    default: {
        bg: 'bg-white',
        iconBg: 'bg-slate-100',
        iconColor: 'text-slate-600',
    },
    primary: {
        bg: 'bg-gradient-to-br from-blue-500 to-blue-600',
        iconBg: 'bg-white/20',
        iconColor: 'text-white',
        textColor: 'text-white',
    },
    success: {
        bg: 'bg-gradient-to-br from-emerald-500 to-emerald-600',
        iconBg: 'bg-white/20',
        iconColor: 'text-white',
        textColor: 'text-white',
    },
    warning: {
        bg: 'bg-gradient-to-br from-amber-500 to-orange-500',
        iconBg: 'bg-white/20',
        iconColor: 'text-white',
        textColor: 'text-white',
    },
    danger: {
        bg: 'bg-gradient-to-br from-red-500 to-red-600',
        iconBg: 'bg-white/20',
        iconColor: 'text-white',
        textColor: 'text-white',
    },
};

const styles = computed(() => variantStyles[props.variant]);

const isColored = computed(() => props.variant !== 'default');

const formattedValue = computed(() => {
    if (props.loading) return '–';
    
    let val = props.value;
    if (typeof val === 'number' && props.currency) {
        val = new Intl.NumberFormat('id-ID').format(val);
    }
    
    return `${props.prefix}${val}${props.suffix}`;
});

const changeInfo = computed(() => {
    if (props.change === null || props.change === undefined) return null;
    
    const isPositive = props.change >= 0;
    return {
        value: Math.abs(props.change).toFixed(1),
        isPositive,
        icon: isPositive ? '↑' : '↓',
        colorClass: isPositive 
            ? (isColored.value ? 'text-white/90' : 'text-emerald-600')
            : (isColored.value ? 'text-white/90' : 'text-red-600'),
        bgClass: isPositive
            ? (isColored.value ? 'bg-white/20' : 'bg-emerald-100')
            : (isColored.value ? 'bg-white/20' : 'bg-red-100'),
    };
});
</script>

<template>
    <div 
        :class="[
            'relative overflow-hidden rounded-xl p-5 shadow-card transition-all duration-300 hover:shadow-lg',
            styles.bg,
            loading ? 'animate-pulse' : ''
        ]"
    >
        <div class="flex items-start justify-between">
            <!-- Content -->
            <div class="flex-1 min-w-0">
                <p 
                    :class="[
                        'text-sm font-medium truncate',
                        isColored ? 'text-white/80' : 'text-slate-500'
                    ]"
                >
                    {{ title }}
                </p>
                
                <p 
                    :class="[
                        'mt-2 text-2xl font-bold tracking-tight',
                        isColored ? 'text-white' : 'text-slate-900',
                        loading ? 'bg-slate-200 rounded w-24 h-8' : ''
                    ]"
                >
                    <template v-if="!loading">{{ formattedValue }}</template>
                </p>

                <!-- Change Indicator -->
                <div 
                    v-if="changeInfo && !loading" 
                    class="mt-3 flex items-center gap-2"
                >
                    <span 
                        :class="[
                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                            changeInfo.bgClass,
                            changeInfo.colorClass,
                        ]"
                    >
                        {{ changeInfo.icon }} {{ changeInfo.value }}%
                    </span>
                    <span 
                        :class="[
                            'text-xs',
                            isColored ? 'text-white/60' : 'text-slate-400'
                        ]"
                    >
                        {{ changePeriod }}
                    </span>
                </div>
            </div>

            <!-- Icon Slot -->
            <div 
                :class="[
                    'flex-shrink-0 p-3 rounded-xl',
                    styles.iconBg,
                    styles.iconColor,
                ]"
            >
                <slot name="icon">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </slot>
            </div>
        </div>

        <!-- Decorative Element -->
        <div 
            v-if="isColored"
            class="absolute -right-4 -bottom-4 w-24 h-24 rounded-full bg-white/10"
        />
    </div>
</template>
