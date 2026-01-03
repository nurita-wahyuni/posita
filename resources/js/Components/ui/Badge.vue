<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'secondary', 'destructive', 'outline', 'success', 'warning'].includes(v)
  },
  class: String,
})

const variantClasses = {
  default: 'bg-primary text-primary-foreground hover:bg-primary/80',
  secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
  destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/80',
  outline: 'border border-input bg-background text-foreground hover:bg-accent hover:text-accent-foreground',
  success: 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200',
  warning: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
}

const badgeClass = computed(() => cn(
  'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold transition-colors',
  'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
  variantClasses[props.variant],
  props.class
))
</script>

<template>
  <span :class="badgeClass">
    <slot />
  </span>
</template>
