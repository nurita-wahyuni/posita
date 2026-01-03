<script setup>
import { cn } from '@/lib/utils'

defineProps({
  for: String,
  error: Boolean,
  class: String,
})
</script>

<template>
  <label
    :for="$props.for"
    :class="cn(
      'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
      error && 'text-destructive',
      $props.class
    )"
  >
    <slot />
  </label>
</template>
