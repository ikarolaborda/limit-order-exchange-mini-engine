<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { toast } from 'vue-sonner'
import { useExchangeStore } from '@/stores/exchange'
import { createEcho } from '@/echo'
import { Button, ThemeToggle, Toaster } from '@/components/ui'
import { OrderForm, Orderbook, MyOrders, RecentTrades } from '@/components/exchange'
import { UserCard, AssetList } from '@/components/profile'
import { ExchangeRates } from '@/components/market'
import { LoginForm } from '@/components/auth'
import { TradingInfo } from '@/components/info'
import { NotificationBell } from '@/components/notification'
import { WalletCard, SendTransactionForm, TransactionHistory } from '@/components/web3'

const store = useExchangeStore()

async function handleLoginSuccess(token: string): Promise<void> {
  store.setToken(token)
  await bootstrapData()
  setupRealtime()
  await showPendingNotifications()
}

async function showPendingNotifications(): Promise<void> {
  const unreadNotifications = await store.fetchNotifications()
  if (unreadNotifications.length === 0) return

  if (unreadNotifications.length <= 3) {
    unreadNotifications.forEach((notification) => {
      const { data } = notification
      const action = data.side === 'sell' ? 'sold' : 'bought'
      toast.success(`Order Filled`, {
        description: `You ${action} ${data.amount} ${data.symbol} at $${parseFloat(data.price).toLocaleString()}`,
      })
    })
  } else {
    toast.success(`${unreadNotifications.length} orders filled`, {
      description: `You have ${unreadNotifications.length} orders that were filled while you were away. Click the bell to view details.`,
    })
  }
}

async function bootstrapData(): Promise<void> {
  await Promise.all([store.fetchProfile(), store.fetchOrderbook(), store.fetchMyOrders(), store.fetchTrades()])
}

function setupRealtime(): void {
  try {
    store.initEcho((token: string) => {
      const echo = createEcho(token)
      if (echo && store.profile?.id) {
        echo.private(`private-user.${store.profile.id}`).listen('.OrderMatched', (payload: unknown) => {
          store.handleTrade(payload as Parameters<typeof store.handleTrade>[0])
        })
      }
      return echo
    })
  } catch (error) {
    console.warn('WebSocket connection unavailable, real-time updates disabled', error)
  }
}

onMounted(async (): Promise<void> => {
  if (store.token) {
    await bootstrapData()
    setupRealtime()
    await showPendingNotifications()
  }
})

watch(
  () => store.symbol,
  (): void => {
    store.fetchOrderbook()
    store.fetchTrades()
  },
)
</script>

<template>
  <div class="min-h-screen bg-background text-foreground">
    <!-- Login Screen -->
    <template v-if="!store.isAuthenticated">
      <LoginForm @login-success="handleLoginSuccess" />
      <Toaster position="top-right" :duration="4000" rich-colors />
    </template>

    <!-- Main Trading Interface -->
    <template v-else>
      <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 class="text-3xl font-bold tracking-tight text-primary">
              Limit Order Exchange
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
              High-performance matching engine with real-time updates
            </p>
          </div>
          <div class="flex items-center gap-4">
            <ExchangeRates />
            <NotificationBell />
            <ThemeToggle />
            <Button variant="outline" @click="store.logout()">
              Sign out
            </Button>
          </div>
        </header>

        <section v-if="store.profile" class="grid gap-4 md:grid-cols-3">
          <UserCard />
          <AssetList />
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

        <section class="space-y-4">
          <h2 class="text-xl font-semibold">Web3 / Ethereum</h2>
          <div class="grid gap-4 md:grid-cols-3">
            <WalletCard />
            <SendTransactionForm />
            <TransactionHistory />
          </div>
        </section>
      </div>
      <Toaster position="top-right" :duration="4000" rich-colors />
    </template>
  </div>
</template>
