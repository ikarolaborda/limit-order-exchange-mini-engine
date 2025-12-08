<script setup lang="ts">
import { inject, computed } from 'vue'
import { cn } from '@/lib/utils'

const hasError = inject<{ value: boolean }>('form-field-error')

const labelClasses = computed(() =>
  cn(
    'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
    hasError?.value && 'text-destructive'
  )
)
</script>

<template>
  <label :class="labelClasses">
    <slot />
  </label>
</template>
