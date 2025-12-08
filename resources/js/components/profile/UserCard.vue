<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardContent, Badge } from '@/components/ui'
import { useExchangeStore } from '@/stores/exchange'

const store = useExchangeStore()
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
        <div class="pt-2">
          <Badge variant="success" class="text-sm">
            Balance: ${{ store.profile.balance }}
          </Badge>
        </div>
      </div>
    </CardContent>
  </Card>
</template>

