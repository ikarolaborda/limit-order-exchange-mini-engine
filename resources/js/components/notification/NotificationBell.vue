<script setup lang="ts">
import { computed } from 'vue'
import { useExchangeStore } from '@/stores/exchange'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { ScrollArea } from '@/components/ui/scroll-area'
import { Separator } from '@/components/ui/separator'
import type { AppNotification } from '@/types'

const store = useExchangeStore()

const hasUnread = computed(() => store.unreadNotificationCount > 0)

function formatNotification(notification: AppNotification): string {
  const { data } = notification
  const action = data.side === 'sell' ? 'sold' : 'bought'
  return `You ${action} ${data.amount} ${data.symbol} at $${parseFloat(data.price).toLocaleString()}`
}

function formatTime(dateString: string): string {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMins / 60)
  const diffDays = Math.floor(diffHours / 24)

  if (diffMins < 1) return 'just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  return `${diffDays}d ago`
}

async function handleNotificationClick(notification: AppNotification): Promise<void> {
  if (notification.read_at === null) {
    await store.markNotificationRead(notification.id)
  }
}

async function handleMarkAllRead(): Promise<void> {
  await store.markAllNotificationsRead()
}
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button variant="outline" size="icon" class="relative">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
          <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
        </svg>
        <Badge
          v-if="hasUnread"
          class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center p-0 text-xs"
          variant="destructive"
        >
          {{ store.unreadNotificationCount > 9 ? '9+' : store.unreadNotificationCount }}
        </Badge>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-80 p-0" align="end">
      <div class="flex items-center justify-between border-b px-4 py-3">
        <h4 class="text-sm font-semibold">
          Notifications
        </h4>
        <Button
          v-if="hasUnread"
          variant="ghost"
          size="sm"
          class="h-auto p-0 text-xs text-muted-foreground hover:text-foreground"
          @click="handleMarkAllRead"
        >
          Mark all read
        </Button>
      </div>
      <ScrollArea class="h-[300px]">
        <div v-if="store.notifications.length === 0" class="flex h-[200px] items-center justify-center">
          <p class="text-sm text-muted-foreground">
            No notifications
          </p>
        </div>
        <div v-else>
          <div
            v-for="notification in store.notifications"
            :key="notification.id"
            class="cursor-pointer px-4 py-3 transition-colors hover:bg-muted/50"
            :class="{ 'bg-muted/30': notification.read_at === null }"
            @click="handleNotificationClick(notification)"
          >
            <div class="flex items-start gap-3">
              <div
                class="mt-1 h-2 w-2 shrink-0 rounded-full"
                :class="notification.read_at === null ? 'bg-primary' : 'bg-transparent'"
              />
              <div class="flex-1 space-y-1">
                <p class="text-sm leading-tight">
                  {{ formatNotification(notification) }}
                </p>
                <p class="text-xs text-muted-foreground">
                  Total: ${{ parseFloat(notification.data.total).toLocaleString() }}
                </p>
                <p class="text-xs text-muted-foreground">
                  {{ formatTime(notification.created_at) }}
                </p>
              </div>
            </div>
            <Separator class="mt-3" />
          </div>
        </div>
      </ScrollArea>
    </PopoverContent>
  </Popover>
</template>
