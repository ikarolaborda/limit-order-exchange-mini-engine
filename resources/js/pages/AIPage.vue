<script setup lang="ts">
import { AIInsights, MarketSentiment } from '@/components/ai'
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">AI-Powered Analysis</h1>
      <p class="text-sm text-muted-foreground">
        Leverage machine learning for market sentiment analysis and trading insights
      </p>
    </div>

    <section class="rounded-lg border bg-card p-6">
      <h2 class="mb-4 text-lg font-semibold">Market Sentiment Overview</h2>
      <MarketSentiment />
    </section>

    <section class="grid gap-6 lg:grid-cols-1">
      <AIInsights />
    </section>
  </div>
</template>
