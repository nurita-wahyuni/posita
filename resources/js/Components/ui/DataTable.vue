<script setup>
/**
 * DataTable - TanStack Table wrapper for server-side paginated data
 * Only handles rendering and client-side sorting of the CURRENT PAGE
 * Pagination is handled by Inertia request
 */
import { computed, ref } from 'vue'
import {
  useVueTable,
  getCoreRowModel,
  getSortedRowModel,
  FlexRender,
} from '@tanstack/vue-table'
import { cn } from '@/lib/utils'

const props = defineProps({
  /** Column definitions for TanStack Table */
  columns: {
    type: Array,
    required: true
  },
  /** Data array (current page only) */
  data: {
    type: Array,
    required: true
  },
  /** Optional class for the table element */
  class: String,
})

const sorting = ref([])

const table = useVueTable({
  get data() { return props.data },
  get columns() { return props.columns },
  state: {
    get sorting() { return sorting.value }
  },
  onSortingChange: (updaterOrValue) => {
    sorting.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(sorting.value)
      : updaterOrValue
  },
  getCoreRowModel: getCoreRowModel(),
  getSortedRowModel: getSortedRowModel(),
  manualPagination: true, // Pagination is handled server-side
})
</script>

<template>
  <div :class="cn('w-full overflow-auto', $props.class)">
    <table class="w-full caption-bottom text-sm">
      <thead class="[&_tr]:border-b">
        <tr
          v-for="headerGroup in table.getHeaderGroups()"
          :key="headerGroup.id"
          class="border-b transition-colors hover:bg-muted/50"
        >
          <th
            v-for="header in headerGroup.headers"
            :key="header.id"
            :class="cn(
              'h-12 px-4 text-left align-middle font-medium text-muted-foreground',
              '[&:has([role=checkbox])]:pr-0'
            )"
          >
            <FlexRender
              v-if="!header.isPlaceholder"
              :render="header.column.columnDef.header"
              :props="header.getContext()"
            />
          </th>
        </tr>
      </thead>
      <tbody class="[&_tr:last-child]:border-0">
        <template v-if="table.getRowModel().rows.length">
          <tr
            v-for="row in table.getRowModel().rows"
            :key="row.id"
            class="border-b transition-colors hover:bg-muted/50"
          >
            <td
              v-for="cell in row.getVisibleCells()"
              :key="cell.id"
              class="p-4 align-middle [&:has([role=checkbox])]:pr-0"
            >
              <FlexRender
                :render="cell.column.columnDef.cell"
                :props="cell.getContext()"
              />
            </td>
          </tr>
        </template>
        <template v-else>
          <tr>
            <td
              :colspan="columns.length"
              class="h-24 text-center text-muted-foreground"
            >
              No data available
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>
