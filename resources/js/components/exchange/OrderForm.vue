<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Card, CardHeader, CardTitle, CardContent, Button, Input, Select, Label, Tooltip, toast } from '@/components/ui'
import { useExchangeStore } from '@/stores/exchange'
import axios from 'axios'

const store = useExchangeStore()
const loading = ref(false)

// Get current market price for selected symbol
const marketPrice = computed(() => {
  const rate = store.exchangeRates[store.symbol]
  return rate ? rate.toFixed(2) : null
})

// Calculate total value (price * amount)
const totalValue = computed(() => {
  const price = parseFloat(store.price)
  const amount = parseFloat(store.amount)
  if (isNaN(price) || isNaN(amount) || price <= 0 || amount <= 0) {
    return null
  }
  return (price * amount).toFixed(2)
})

// Calculate total with fee for buy orders (1.5% fee)
const totalWithFee = computed(() => {
  if (!totalValue.value) return null
  const total = parseFloat(totalValue.value)
  if (store.side === 'buy') {
    return (total * 1.015).toFixed(2)
  }
  return totalValue.value
})

// Use market price button handler
function useMarketPrice(): void {
  if (marketPrice.value) {
    store.price = marketPrice.value
  } else {
    toast.warning('Market price not available')
  }
}

// Auto-calculate price when amount changes (if price is empty and market price is available)
watch(() => store.amount, (newAmount) => {
  if (newAmount && !store.price && marketPrice.value) {
    // Suggest using market price
  }
})

async function submitOrder(): Promise<void> {
  // Validate inputs before submitting
  if (!store.price || !store.amount) {
    toast.warning('Please enter price and amount')
    return
  }

  loading.value = true

  try {
    await store.placeOrder()
    toast.success('Order placed successfully!')
    // Clear form after success
    store.price = ''
    store.amount = ''
  } catch (err) {
    if (axios.isAxiosError(err) && err.response?.status === 422) {
      const errors = err.response.data.errors
      // Get first error message from validation errors
      const firstError = Object.values(errors || {})[0]
      const message = Array.isArray(firstError) ? firstError[0] : String(firstError || 'Validation failed')
      toast.error(message)
    } else {
      toast.error('Failed to place order. Please try again.')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Card class="border-border bg-card">
    <CardHeader class="pb-3">
      <div class="flex items-center justify-between">
        <CardTitle>Place Order</CardTitle>
        <Select v-model="store.symbol" class="w-24">
          <option value="BTC">BTC</option>
          <option value="ETH">ETH</option>
        </Select>
      </div>
    </CardHeader>
    <CardContent class="space-y-4">
      <div class="flex gap-2">
        <Button
          :variant="store.side === 'buy' ? 'default' : 'outline'"
          class="flex-1"
          @click="store.side = 'buy'"
        >
          Buy
        </Button>
        <Button
          :variant="store.side === 'sell' ? 'destructive' : 'outline'"
          class="flex-1"
          @click="store.side = 'sell'"
        >
          Sell
        </Button>
      </div>

      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <Label>Price (USD)</Label>
          <Tooltip v-if="marketPrice" content="Use current market price">
            <button
              type="button"
              class="text-xs text-primary hover:underline"
              @click="useMarketPrice"
            >
              Market: ${{ marketPrice }}
            </button>
          </Tooltip>
        </div>
        <Input v-model="store.price" type="number" step="0.01" placeholder="0.00" />
      </div>

      <div class="space-y-2">
        <Label>Amount ({{ store.symbol }})</Label>
        <Input v-model="store.amount" type="number" step="0.00000001" placeholder="0.00" />
      </div>

      <!-- Order Summary -->
      <div v-if="totalValue" class="rounded-md bg-muted/50 p-3 space-y-1">
        <div class="flex justify-between text-sm">
          <span class="text-muted-foreground">Subtotal</span>
          <span>${{ totalValue }}</span>
        </div>
        <div v-if="store.side === 'buy'" class="flex justify-between text-sm">
          <span class="text-muted-foreground">Fee (1.5%)</span>
          <span>${{ (parseFloat(totalValue) * 0.015).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between text-sm font-medium border-t border-border pt-1 mt-1">
          <span>{{ store.side === 'buy' ? 'Total (locked)' : 'You receive' }}</span>
          <span :class="store.side === 'buy' ? 'text-primary' : 'text-green-600 dark:text-green-400'">${{ totalWithFee }}</span>
        </div>
      </div>

      <p class="text-xs text-muted-foreground">
        Fee 1.5% on notional (locked upfront on buys).
      </p>

      <Button class="w-full" :disabled="loading" @click="submitOrder">
        {{ loading ? 'Placing...' : `${store.side === 'buy' ? 'Buy' : 'Sell'} ${store.symbol}` }}
      </Button>
    </CardContent>
  </Card>
</template>

