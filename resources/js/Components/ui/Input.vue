<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  modelValue: [String, Number],
  type: {
    type: String,
    default: 'text'
  },
  error: Boolean,
  class: String,
})

const emit = defineEmits(['update:modelValue'])

const inputClass = computed(() => cn(
  'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm',
  'ring-offset-background transition-all duration-200',
  'file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground',
  'placeholder:text-muted-foreground',
  'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
  'disabled:cursor-not-allowed disabled:opacity-50',
  // Error state
  props.error && 'border-destructive focus-visible:ring-destructive',
  props.class
))

const handleInput = (e) => {
  emit('update:modelValue', e.target.value)
}
</script>

<template>
  <input
    :type="type"
    :value="modelValue"
    :class="inputClass"
    @input="handleInput"
    v-bind="$attrs"
  />
</template>
