<script setup>
import { DropdownMenuLabel } from 'radix-vue'
import { cn } from '@/lib/utils'

defineProps({
  class: String,
})
</script>

<template>
  <DropdownMenuLabel :class="cn('px-2 py-1.5 text-sm font-semibold', $props.class)">
    <slot />
  </DropdownMenuLabel>
</template>
