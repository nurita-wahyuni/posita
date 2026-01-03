<script setup>
/**
 * DataTablePagination - Renders Inertia pagination links with Shadcn styling
 * Accepts the `links` prop from Laravel's paginate() response
 */
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { cn } from '@/lib/utils'
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next'

const props = defineProps({
  /** Pagination links from Inertia (page.props.data.links) */
  links: {
    type: Array,
    required: true
  },
  /** Current page info */
  meta: {
    type: Object,
    default: () => ({})
  },
  /** Show page size selector */
  showPageSize: {
    type: Boolean,
    default: false
  },
  class: String,
})

// Extract first, previous, next, last links
const previousLink = computed(() => props.links.find(l => l.label.includes('Previous')))
const nextLink = computed(() => props.links.find(l => l.label.includes('Next')))
const pageLinks = computed(() => props.links.filter(l => 
  !l.label.includes('Previous') && !l.label.includes('Next')
))

const buttonClass = (active, disabled) => cn(
  'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium',
  'ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2',
  'focus-visible:ring-ring focus-visible:ring-offset-2',
  'h-10 w-10',
  active && 'bg-primary text-primary-foreground',
  !active && 'hover:bg-accent hover:text-accent-foreground',
  disabled && 'pointer-events-none opacity-50'
)
</script>

<template>
  <div :class="cn('flex items-center justify-between px-2 py-4', $props.class)">
    <!-- Page info -->
    <div class="flex-1 text-sm text-muted-foreground">
      <template v-if="meta.from && meta.to && meta.total">
        Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} results
      </template>
    </div>

    <!-- Pagination controls -->
    <div class="flex items-center space-x-2">
      <!-- Previous button -->
      <Link
        v-if="previousLink"
        :href="previousLink.url || '#'"
        :class="buttonClass(false, !previousLink.url)"
        preserve-scroll
      >
        <ChevronLeft class="h-4 w-4" />
        <span class="sr-only">Previous</span>
      </Link>
      <span v-else :class="buttonClass(false, true)">
        <ChevronLeft class="h-4 w-4" />
      </span>

      <!-- Page numbers -->
      <template v-for="link in pageLinks" :key="link.label">
        <Link
          v-if="link.url"
          :href="link.url"
          :class="buttonClass(link.active, false)"
          preserve-scroll
          v-html="link.label"
        />
        <span
          v-else
          :class="buttonClass(link.active, !link.url)"
          v-html="link.label"
        />
      </template>

      <!-- Next button -->
      <Link
        v-if="nextLink"
        :href="nextLink.url || '#'"
        :class="buttonClass(false, !nextLink.url)"
        preserve-scroll
      >
        <ChevronRight class="h-4 w-4" />
        <span class="sr-only">Next</span>
      </Link>
      <span v-else :class="buttonClass(false, true)">
        <ChevronRight class="h-4 w-4" />
      </span>
    </div>
  </div>
</template>
