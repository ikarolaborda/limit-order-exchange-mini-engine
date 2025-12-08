<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { useExchangeStore } from '@/stores/exchange'

const store = useExchangeStore()
let intervalId: ReturnType<typeof setInterval> | null = null

function formatPrice(price: number | null): string {
  if (price === null) return '—'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(price)
}

function formatTime(date: Date | null): string {
  if (!date) return ''
  return date.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  store.fetchExchangeRates()
  // Refresh every 60 seconds
  intervalId = setInterval(() => {
    store.fetchExchangeRates()
  }, 60000)
})

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId)
  }
})
</script>

<template>
  <div class="flex items-center gap-4 text-sm">
    <div class="flex items-center gap-1.5">
      <span class="font-medium text-amber-500">BTC</span>
      <span class="text-foreground">{{ formatPrice(store.exchangeRates.BTC) }}</span>
    </div>
    <div class="flex items-center gap-1.5">
      <span class="font-medium text-blue-500">ETH</span>
      <span class="text-foreground">{{ formatPrice(store.exchangeRates.ETH) }}</span>
    </div>
    <span v-if="store.exchangeRates.lastUpdated" class="text-xs text-muted-foreground">
      {{ formatTime(store.exchangeRates.lastUpdated) }}
    </span>
    <span v-else class="text-xs text-amber-500" title="Using cached or fallback rates">
      (offline)
    </span>
  </div>
</template>
