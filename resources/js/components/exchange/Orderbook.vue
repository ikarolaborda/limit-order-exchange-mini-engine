<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardHeader, CardTitle, CardContent, Button, Select, Label } from '@/components/ui'
import { useExchangeStore } from '@/stores/exchange'
import type { Side, OrderStatus } from '@/types'

const store = useExchangeStore()

const buys = computed(() => store.orderbook.filter((o) => o.side === 'buy'))
const sells = computed(() => store.orderbook.filter((o) => o.side === 'sell'))

const sideFilter = computed({
  get: () => store.orderbookFilters.side ?? '',
  set: (value: string) => {
    store.setOrderbookFilters({ side: value ? (value as Side) : null })
  },
})

const statusFilter = computed({
  get: () => store.orderbookFilters.status?.toString() ?? '',
  set: (value: string) => {
    store.setOrderbookFilters({ status: value ? (parseInt(value, 10) as OrderStatus) : null })
  },
})

const hasFilters = computed(() => store.orderbookFilters.side !== null || store.orderbookFilters.status !== null)

function refresh(): void {
  store.fetchOrderbook()
}

function clearFilters(): void {
  store.clearOrderbookFilters()
}

function getStatusLabel(status: OrderStatus): string {
  switch (status) {
    case 1:
      return 'Open'
    case 2:
      return 'Filled'
    case 3:
      return 'Cancelled'
    default:
      return 'Unknown'
  }
}

function getStatusClass(status: OrderStatus): string {
  switch (status) {
    case 1:
      return 'text-blue-400'
    case 2:
      return 'text-emerald-400'
    case 3:
      return 'text-muted-foreground'
    default:
      return ''
  }
}
</script>

<template>
  <Card class="border-border bg-card md:col-span-2">
    <CardHeader class="pb-3">
      <div class="flex items-center justify-between">
        <CardTitle>Orderbook ({{ store.symbol }})</CardTitle>
        <div class="flex items-center gap-2">
          <Button v-if="hasFilters" variant="ghost" size="sm" @click="clearFilters">Clear</Button>
          <Button variant="ghost" size="sm" @click="refresh">Refresh</Button>
        </div>
      </div>
      <div class="mt-3 flex flex-wrap items-end gap-4">
        <div class="flex flex-col gap-1.5">
          <Label class="text-xs text-muted-foreground">Side</Label>
          <Select v-model="sideFilter" class="w-28">
            <option value="">All</option>
            <option value="buy">Buy</option>
            <option value="sell">Sell</option>
          </Select>
        </div>
        <div class="flex flex-col gap-1.5">
          <Label class="text-xs text-muted-foreground">Status</Label>
          <Select v-model="statusFilter" class="w-32">
            <option value="">Open only</option>
            <option value="1">Open</option>
            <option value="2">Filled</option>
            <option value="3">Cancelled</option>
          </Select>
        </div>
      </div>
    </CardHeader>
    <CardContent>
      <!-- Show split view when no side filter or showing all statuses -->
      <div v-if="!sideFilter" class="grid grid-cols-2 gap-4">
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
              <span v-if="statusFilter" :class="getStatusClass(order.status)" class="text-xs">
                {{ getStatusLabel(order.status) }}
              </span>
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
              <span v-if="statusFilter" :class="getStatusClass(order.status)" class="text-xs">
                {{ getStatusLabel(order.status) }}
              </span>
            </div>
            <div v-if="sells.length === 0" class="py-4 text-center text-sm text-muted-foreground">
              No sell orders
            </div>
          </div>
        </div>
      </div>

      <!-- Show single list when side filter is active -->
      <div v-else class="max-h-72 space-y-1 overflow-y-auto">
        <div
          v-for="order in store.orderbook"
          :key="order.id"
          :class="[
            'flex justify-between rounded px-3 py-2 text-sm',
            order.side === 'buy' ? 'bg-emerald-950/30' : 'bg-rose-950/30',
          ]"
        >
          <span class="text-muted-foreground">#{{ order.id }}</span>
          <span :class="order.side === 'buy' ? 'text-emerald-400' : 'text-rose-400'">
            {{ order.side.toUpperCase() }}
          </span>
          <span>{{ order.price }} @ {{ order.amount }}</span>
          <span v-if="statusFilter" :class="getStatusClass(order.status)" class="text-xs">
            {{ getStatusLabel(order.status) }}
          </span>
        </div>
        <div v-if="store.orderbook.length === 0" class="py-4 text-center text-sm text-muted-foreground">
          No orders found
        </div>
      </div>
    </CardContent>
  </Card>
</template>
