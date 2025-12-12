<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useExchangeStore } from '@/stores/exchange'
import { Badge, Tooltip } from '@/components/ui'

interface SymbolSentiment {
  symbol: string
  sentiment: string
  confidence: number
  recommendation: string
  summary: string
  top_category: string
}

const store = useExchangeStore()
const sentiments = ref<Record<string, SymbolSentiment>>({})
const loading = ref(false)
const error = ref('')

async function fetchSentiment(): Promise<void> {
  loading.value = true
  error.value = ''

  try {
    const response = await axios.get<{ data: { attributes: Record<string, SymbolSentiment> } }>(
      '/api/ai/market-sentiment',
      {
        headers: {
          Authorization: `Bearer ${store.token}`,
        },
      }
    )
    sentiments.value = response.data.data.attributes
  } catch (err) {
    error.value = 'Failed to load sentiment'
  } finally {
    loading.value = false
  }
}

function getBadgeVariant(recommendation: string): 'default' | 'destructive' | 'secondary' | 'outline' {
  if (recommendation.includes('bullish')) return 'default'
  if (recommendation.includes('bearish')) return 'destructive'
  return 'secondary'
}

function getRecommendationEmoji(recommendation: string): string {
  if (recommendation.includes('bullish')) return '+'
  if (recommendation.includes('bearish')) return '-'
  return '~'
}

function formatRecommendation(recommendation: string): string {
  return recommendation.replace('_', ' ').toUpperCase()
}

function getTooltipContent(data: SymbolSentiment): string {
  return `${data.summary} (${Math.round(data.confidence * 100)}% confidence, ${data.top_category})`
}

onMounted(fetchSentiment)
</script>

<template>
  <div class="flex items-center gap-3">
    <span class="text-sm font-medium text-muted-foreground">AI Sentiment:</span>

    <div v-if="loading" class="text-sm text-muted-foreground">Loading...</div>

    <div v-else-if="error" class="text-sm text-destructive">{{ error }}</div>

    <template v-else>
      <Tooltip v-for="(data, symbol) in sentiments" :key="symbol" :content="getTooltipContent(data)">
        <Badge :variant="getBadgeVariant(data.recommendation)" class="cursor-help">
          {{ symbol }}: {{ getRecommendationEmoji(data.recommendation) }} {{ formatRecommendation(data.recommendation) }}
        </Badge>
      </Tooltip>
    </template>
  </div>
</template>
