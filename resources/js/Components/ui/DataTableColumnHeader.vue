<script setup>
/**
 * DataTableColumnHeader - Sortable column header with visual indicators
 */
import { computed } from 'vue'
import { cn } from '@/lib/utils'
import { ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next'

const props = defineProps({
  column: {
    type: Object,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  class: String,
})

const isSorted = computed(() => props.column.getIsSorted())

const handleSort = () => {
  if (props.column.getCanSort()) {
    props.column.toggleSorting()
  }
}
</script>

<template>
  <div :class="cn('flex items-center space-x-2', $props.class)">
    <button
      v-if="column.getCanSort()"
      @click="handleSort"
      class="inline-flex items-center justify-center whitespace-nowrap font-medium text-sm transition-colors hover:text-foreground -ml-3 h-8 px-3 rounded-md"
    >
      <span>{{ title }}</span>
      <ArrowUp v-if="isSorted === 'asc'" class="ml-2 h-4 w-4" />
      <ArrowDown v-else-if="isSorted === 'desc'" class="ml-2 h-4 w-4" />
      <ArrowUpDown v-else class="ml-2 h-4 w-4 text-muted-foreground" />
    </button>
    <span v-else>{{ title }}</span>
  </div>
</template>
