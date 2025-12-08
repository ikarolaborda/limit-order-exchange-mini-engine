<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  variant?: 'default' | 'secondary' | 'destructive' | 'outline' | 'success'
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
})

const badgeClasses = computed((): string => {
  const base = 'inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold transition-colors'

  const variants: Record<string, string> = {
    default: 'border-transparent bg-primary text-primary-foreground shadow',
    secondary: 'border-transparent bg-secondary text-secondary-foreground',
    destructive: 'border-transparent bg-destructive text-destructive-foreground shadow',
    outline: 'text-foreground border',
    success: 'border-transparent bg-emerald-500 text-white shadow',
  }

  return cn(base, variants[props.variant], props.class)
})
</script>

<template>
  <span :class="badgeClasses">
    <slot />
  </span>
</template>

