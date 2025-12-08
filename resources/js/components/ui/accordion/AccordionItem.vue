<script setup lang="ts">
import { AccordionItem } from 'radix-vue'
import { cn } from '@/lib/utils'

interface Props {
  value: string
  class?: string
}

const props = defineProps<Props>()
</script>

<template>
  <AccordionItem
    :value="value"
    :class="cn('border-b border-border', props.class)"
  >
    <slot />
  </AccordionItem>
</template>
