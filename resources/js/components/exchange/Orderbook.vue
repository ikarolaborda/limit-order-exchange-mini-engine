<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardHeader, CardTitle, CardContent, Button } from '@/components/ui'
import { useExchangeStore } from '@/stores/exchange'

const store = useExchangeStore()

const buys = computed(() => store.orderbook.filter((o) => o.side === 'buy'))
const sells = computed(() => store.orderbook.filter((o) => o.side === 'sell'))

function refresh(): void {
  store.fetchOrderbook()
}
</script>

<template>
  <Card class="border-border bg-card md:col-span-2">
    <CardHeader class="pb-3">
      <div class="flex items-center justify-between">
        <CardTitle>Orderbook ({{ store.symbol }})</CardTitle>
        <Button variant="ghost" size="sm" @click="refresh">Refresh</Button>
      </div>
    </CardHeader>
    <CardContent>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <div class="mb-2 text-sm font-medium text-emerald-400">Buys</div>
          <div class="max-h-64 space-y-1 overflow-y-auto">
            <div
              v-for="order in buys"
              :key="order.id"
              class="flex justify-between rounded bg-emerald-950/30 px-3 py-2 text-sm"
            >
              <span class="text-muted-foreground">#{{ order.id }}</span>
              <span>{{ order.price }} @ {{ order.amount }}</span>
            </div>
            <div v-if="buys.length === 0" class="py-4 text-center text-sm text-muted-foreground">
              No buy orders
            </div>
          </div>
        </div>

        <div>
          <div class="mb-2 text-sm font-medium text-rose-400">Sells</div>
          <div class="max-h-64 space-y-1 overflow-y-auto">
            <div
              v-for="order in sells"
              :key="order.id"
              class="flex justify-between rounded bg-rose-950/30 px-3 py-2 text-sm"
            >
              <span class="text-muted-foreground">#{{ order.id }}</span>
              <span>{{ order.price }} @ {{ order.amount }}</span>
            </div>
            <div v-if="sells.length === 0" class="py-4 text-center text-sm text-muted-foreground">
              No sell orders
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

