<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardHeader, CardTitle, CardContent, Badge } from '@/components/ui'
import { useExchangeStore } from '@/stores/exchange'

const store = useExchangeStore()

const hasLockedBalance = computed(() => {
  const locked = parseFloat(store.profile?.locked_balance ?? '0')
  return locked > 0
})

function formatBalance(value: string): string {
  return parseFloat(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>

<template>
  <Card v-if="store.profile" class="border-border bg-card">
    <CardHeader class="pb-3">
      <CardTitle class="text-lg">Profile</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="space-y-2">
        <div class="font-semibold">{{ store.profile.name }}</div>
        <div class="text-sm text-muted-foreground">{{ store.profile.email }}</div>
        <div class="space-y-1.5 pt-2">
          <Badge variant="success" class="text-sm">
            Available: ${{ formatBalance(store.profile.balance) }}
          </Badge>
          <div v-if="hasLockedBalance" class="flex items-center gap-2">
            <Badge variant="secondary" class="text-sm">
              Pending: ${{ formatBalance(store.profile.locked_balance) }}
            </Badge>
            <span class="text-xs text-muted-foreground">(in open buy orders)</span>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

