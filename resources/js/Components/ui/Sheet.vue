<script setup>
import {
  DialogRoot,
  DialogPortal,
  DialogOverlay,
  DialogContent,
  DialogTitle,
  DialogDescription,
  DialogClose
} from 'radix-vue'
import { computed } from 'vue'
import { cn } from '@/lib/utils'
import { X } from 'lucide-vue-next'

const props = defineProps({
  open: Boolean,
  side: {
    type: String,
    default: 'right',
    validator: (v) => ['top', 'bottom', 'left', 'right'].includes(v)
  },
  title: String,
  description: String,
  class: String,
})

const emit = defineEmits(['update:open'])

const handleOpenChange = (val) => {
  emit('update:open', val)
}

const sheetVariants = {
  top: 'inset-x-0 top-0 border-b data-[state=open]:animate-slide-in-down data-[state=closed]:slide-out-to-top',
  bottom: 'inset-x-0 bottom-0 border-t data-[state=open]:animate-slide-in-up data-[state=closed]:slide-out-to-bottom',
  left: 'inset-y-0 left-0 h-full w-3/4 border-r sm:max-w-sm data-[state=open]:animate-slide-in-left data-[state=closed]:slide-out-to-left',
  right: 'inset-y-0 right-0 h-full w-3/4 border-l sm:max-w-sm data-[state=open]:animate-slide-in-right data-[state=closed]:slide-out-to-right',
}

const contentClass = computed(() => cn(
  'fixed z-50 gap-4 bg-background p-6 shadow-lg transition ease-in-out',
  sheetVariants[props.side],
  props.class
))
</script>

<template>
  <DialogRoot :open="open" @update:open="handleOpenChange">
    <DialogPortal>
      <DialogOverlay
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm data-[state=open]:animate-fade-in"
      />
      <DialogContent :class="contentClass">
        <div v-if="title || description" class="flex flex-col space-y-2">
          <DialogTitle v-if="title" class="text-lg font-semibold text-foreground">
            {{ title }}
          </DialogTitle>
          <DialogDescription v-if="description" class="text-sm text-muted-foreground">
            {{ description }}
          </DialogDescription>
        </div>

        <slot />

        <DialogClose
          class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
        >
          <X class="h-4 w-4" />
          <span class="sr-only">Close</span>
        </DialogClose>
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>
