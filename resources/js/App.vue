<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { toast } from 'vue-sonner'
import { useExchangeStore } from '@/stores/exchange'
import { createEcho } from '@/echo'
import { Toaster } from '@/components/ui'
import { LoginForm } from '@/components/auth'
import { Navbar } from '@/components/layout'

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

    <!-- Main Application -->
    <template v-else>
      <div class="flex min-h-screen flex-col">
        <Navbar />
        <main class="flex-1">
          <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <router-view />
          </div>
        </main>
      </div>
      <Toaster position="top-right" :duration="4000" rich-colors />
    </template>
  </div>
</template>
