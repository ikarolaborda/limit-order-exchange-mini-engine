<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent, Badge } from '@/components/ui'
import { useExchangeStore } from '@/stores/exchange'

const store = useExchangeStore()
</script>

<template>
  <Card v-if="store.profile?.assets?.length" class="border-border bg-card md:col-span-2">
    <CardHeader class="pb-3">
      <CardTitle class="text-lg">Assets</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="flex flex-wrap gap-3">
        <div
          v-for="asset in store.profile.assets"
          :key="asset.symbol"
          class="rounded-lg bg-muted/50 px-4 py-3"
        >
          <div class="flex items-center gap-2">
            <Badge>{{ asset.symbol }}</Badge>
          </div>
          <div class="mt-2 space-y-1 text-sm">
            <div>
              <span class="text-muted-foreground">Available:</span>
              <span class="ml-1 font-medium">{{ asset.amount }}</span>
            </div>
            <div>
              <span class="text-muted-foreground">Locked:</span>
              <span class="ml-1 font-medium text-amber-400">{{ asset.locked_amount }}</span>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

