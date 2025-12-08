<script setup lang="ts">
import { ref } from 'vue'
import { Card, CardHeader, CardTitle, CardContent, Button, Badge, Tooltip, toast } from '@/components/ui'
import { useExchangeStore } from '@/stores/exchange'
import { OrderStatus } from '@/types'
import axios from 'axios'

const store = useExchangeStore()
const cancellingId = ref<number | null>(null)

function refresh(): void {
  store.fetchMyOrders()
}

function getStatusLabel(status: OrderStatus): string {
  const labels: Record<OrderStatus, string> = {
    [OrderStatus.OPEN]: 'waiting',
    [OrderStatus.FILLED]: 'filled',
    [OrderStatus.CANCELLED]: 'cancelled',
  }
  return labels[status] ?? 'unknown'
}

function getStatusVariant(status: OrderStatus): 'default' | 'success' | 'secondary' {
  const variants: Record<OrderStatus, 'default' | 'success' | 'secondary'> = {
    [OrderStatus.OPEN]: 'default',
    [OrderStatus.FILLED]: 'success',
    [OrderStatus.CANCELLED]: 'secondary',
  }
  return variants[status] ?? 'secondary'
}

function getStatusTooltip(status: OrderStatus, side: string): string {
  if (status === OrderStatus.OPEN) {
    return side === 'buy'
      ? 'Waiting for a matching sell order at this price or lower'
      : 'Waiting for a matching buy order at this price or higher'
  }
  if (status === OrderStatus.FILLED) {
    return 'Order has been matched and executed'
  }
  return 'Order was cancelled and funds/assets were unlocked'
}

async function cancelOrder(orderId: number): Promise<void> {
  cancellingId.value = orderId
  try {
    await store.cancel(orderId)
    toast.success('Order cancelled successfully')
  } catch (err) {
    if (axios.isAxiosError(err) && err.response?.status === 422) {
      const errors = err.response.data.errors
      const firstError = Object.values(errors || {})[0]
      const message = Array.isArray(firstError) ? firstError[0] : String(firstError || 'Cannot cancel order')
      toast.error(message)
    } else {
      toast.error('Failed to cancel order')
    }
  } finally {
    cancellingId.value = null
  }
}
</script>

<template>
  <Card class="border-border bg-card">
    <CardHeader class="pb-3">
      <div class="flex items-center justify-between">
        <CardTitle>My Orders</CardTitle>
        <Button variant="ghost" size="sm" @click="refresh">Refresh</Button>
      </div>
    </CardHeader>
    <CardContent>
      <div class="max-h-72 space-y-2 overflow-y-auto">
        <div
          v-for="order in store.myOrders"
          :key="order.id"
          class="flex items-center justify-between rounded bg-muted/50 px-3 py-2 text-sm"
        >
          <div class="flex items-center gap-2">
            <Badge :variant="order.side === 'buy' ? 'success' : 'destructive'">
              {{ order.side }}
            </Badge>
            <span class="font-medium">{{ order.symbol }}</span>
            <span class="text-muted-foreground">${{ order.price }} × {{ order.amount }}</span>
            <Tooltip :content="getStatusTooltip(order.status, order.side)">
              <Badge :variant="getStatusVariant(order.status)" class="cursor-help">
                <span v-if="order.status === OrderStatus.OPEN" class="mr-1 inline-block h-2 w-2 animate-pulse rounded-full bg-yellow-500" />
                {{ getStatusLabel(order.status) }}
              </Badge>
            </Tooltip>
          </div>
          <Button
            v-if="order.status === OrderStatus.OPEN"
            variant="ghost"
            size="sm"
            class="text-destructive hover:text-destructive"
            :disabled="cancellingId === order.id"
            @click="cancelOrder(order.id)"
          >
            {{ cancellingId === order.id ? 'Cancelling...' : 'Cancel' }}
          </Button>
        </div>
        <div v-if="store.myOrders.length === 0" class="py-4 text-center text-sm text-muted-foreground">
          No orders yet
        </div>
      </div>
    </CardContent>
  </Card>
</template>

