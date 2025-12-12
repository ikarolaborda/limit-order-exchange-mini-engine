<script setup lang="ts">
import { useExchangeStore } from '@/stores/exchange'
import { OrderForm, Orderbook, MyOrders, RecentTrades } from '@/components/exchange'
import { UserCard, AssetList } from '@/components/profile'
import { TradingInfo } from '@/components/info'
import { MarketSentiment } from '@/components/ai'

const store = useExchangeStore()
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Trading Dashboard</h1>
      <p class="text-sm text-muted-foreground">
        Place orders, view the orderbook, and track your trades
      </p>
    </div>

    <section v-if="store.profile" class="grid gap-4 md:grid-cols-3">
      <UserCard />
      <AssetList />
    </section>

    <section class="rounded-lg border bg-card p-4">
      <MarketSentiment />
    </section>

    <section class="grid gap-4 md:grid-cols-3">
      <OrderForm />
      <Orderbook />
    </section>

    <section class="grid gap-4 md:grid-cols-2">
      <MyOrders />
      <RecentTrades />
    </section>

    <section>
      <TradingInfo />
    </section>
  </div>
</template>
