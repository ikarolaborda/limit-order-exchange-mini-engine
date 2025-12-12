<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useExchangeStore } from '@/stores/exchange'
import {
  NavigationMenu,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu'
import { Button, ThemeToggle } from '@/components/ui'
import { NotificationBell } from '@/components/notification'
import { ExchangeRates } from '@/components/market'

const route = useRoute()
const router = useRouter()
const store = useExchangeStore()

const navItems = [
  { name: 'trading', label: 'Trading', path: '/' },
  { name: 'ai', label: 'AI Sentiment', path: '/ai' },
  { name: 'web3', label: 'Web3', path: '/web3' },
  { name: 'settings', label: 'Settings', path: '/settings' },
]

const currentRoute = computed(() => route.name)

function navigateTo(path: string) {
  router.push(path)
}

function isActive(name: string): boolean {
  return currentRoute.value === name
}
</script>

<template>
  <header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="mx-auto flex h-14 max-w-7xl items-center px-4 sm:px-6 lg:px-8">
      <div class="mr-4 flex items-center space-x-2">
        <span class="text-xl font-bold text-primary">LOE</span>
      </div>

      <NavigationMenu class="hidden md:flex">
        <NavigationMenuList>
          <NavigationMenuItem v-for="item in navItems" :key="item.name">
            <NavigationMenuLink
              :class="[
                navigationMenuTriggerStyle(),
                isActive(item.name) && 'bg-accent text-accent-foreground',
              ]"
              class="cursor-pointer"
              @click="navigateTo(item.path)"
            >
              {{ item.label }}
            </NavigationMenuLink>
          </NavigationMenuItem>
        </NavigationMenuList>
      </NavigationMenu>

      <div class="flex flex-1 items-center justify-end space-x-2">
        <div class="hidden sm:block">
          <ExchangeRates />
        </div>
        <NotificationBell />
        <ThemeToggle />
        <Button variant="outline" size="sm" @click="store.logout()">
          Sign out
        </Button>
      </div>
    </div>

    <nav class="flex items-center justify-around border-t py-2 md:hidden">
      <button
        v-for="item in navItems"
        :key="item.name"
        :class="[
          'flex flex-col items-center px-3 py-1 text-xs transition-colors',
          isActive(item.name) ? 'text-primary' : 'text-muted-foreground hover:text-foreground',
        ]"
        @click="navigateTo(item.path)"
      >
        {{ item.label }}
      </button>
    </nav>
  </header>
</template>
