<script setup>
/**
 * Sonner Toast Integration
 * Provides a Vue wrapper for vue-sonner toasts
 */
import { Toaster, toast } from 'vue-sonner'
</script>

<template>
  <Toaster
    position="top-right"
    :toastOptions="{
      duration: 4000,
      classNames: {
        toast: 'bg-background text-foreground border-border shadow-lg',
        title: 'font-semibold',
        description: 'text-muted-foreground text-sm',
        success: 'border-emerald-200 bg-emerald-50 text-emerald-900',
        error: 'border-destructive bg-destructive/10 text-destructive',
        warning: 'border-yellow-200 bg-yellow-50 text-yellow-900',
        info: 'border-blue-200 bg-blue-50 text-blue-900',
      }
    }"
    richColors
    closeButton
  />
</template>
