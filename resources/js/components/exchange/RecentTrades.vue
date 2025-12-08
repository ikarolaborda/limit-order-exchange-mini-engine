<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui'
import { useExchangeStore } from '@/stores/exchange'

const store = useExchangeStore()
</script>

<template>
  <Card class="border-border bg-card">
    <CardHeader class="pb-3">
      <CardTitle>Recent Trades</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="max-h-72 space-y-2 overflow-y-auto">
        <div
          v-for="trade in store.trades"
          :key="`${trade.buy_order_id}-${trade.sell_order_id}`"
          class="flex justify-between rounded bg-muted/50 px-3 py-2 text-sm"
        >
          <span class="font-medium">{{ trade.symbol }}</span>
          <span>
            <span class="text-muted-foreground">{{ trade.price }}</span>
            <span class="mx-1">×</span>
            <span>{{ trade.amount }}</span>
          </span>
        </div>
        <div v-if="store.trades.length === 0" class="py-4 text-center text-sm text-muted-foreground">
          No trades yet
        </div>
      </div>
    </CardContent>
  </Card>
</template>

