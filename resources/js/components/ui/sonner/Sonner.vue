<script setup lang="ts">
import 'vue-sonner/style.css'
import { Toaster as Sonner, type ToasterProps } from 'vue-sonner'

const props = defineProps<ToasterProps>()
</script>

<template>
  <Sonner
    class="toaster group"
    :class-names="{
      toast: 'group toast group-[.toaster]:bg-background group-[.toaster]:text-foreground group-[.toaster]:border-border group-[.toaster]:shadow-lg',
      description: 'group-[.toast]:text-muted-foreground',
      actionButton: 'group-[.toast]:bg-primary group-[.toast]:text-primary-foreground',
      cancelButton: 'group-[.toast]:bg-muted group-[.toast]:text-muted-foreground',
      error: 'group-[.toaster]:bg-destructive group-[.toaster]:text-destructive-foreground group-[.toaster]:border-destructive',
      success: 'group-[.toaster]:bg-emerald-500 group-[.toaster]:text-white group-[.toaster]:border-emerald-500',
      warning: 'group-[.toaster]:bg-amber-500 group-[.toaster]:text-white group-[.toaster]:border-amber-500',
      info: 'group-[.toaster]:bg-blue-500 group-[.toaster]:text-white group-[.toaster]:border-blue-500',
    }"
    v-bind="props"
  />
</template>
