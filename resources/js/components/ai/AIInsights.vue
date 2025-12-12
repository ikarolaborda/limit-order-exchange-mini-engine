<script setup lang="ts">
import { ref, computed } from 'vue'
import axios from 'axios'
import { useExchangeStore } from '@/stores/exchange'
import { Button, Card, CardContent, CardDescription, CardHeader, CardTitle, Label, Textarea, Select, Badge } from '@/components/ui'

interface SentimentResult {
  text: string
  label: string
  score: number
  sentiment: string
  confidence: number
}

interface MarketInsight {
  symbol: string
  news_text: string
  sentiment: SentimentResult
  recommendation: string
  categories: Array<{ label: string; score: number }>
  summary: string
}

const store = useExchangeStore()

const newsText = ref('')
const selectedSymbol = ref<'BTC' | 'ETH'>('BTC')
const isAnalyzing = ref(false)
const marketInsight = ref<MarketInsight | null>(null)
const error = ref('')

const canAnalyze = computed(() => newsText.value.trim().length >= 20)

const recommendationVariant = computed(() => {
  if (!marketInsight.value) return 'secondary'
  switch (marketInsight.value.recommendation) {
    case 'bullish':
    case 'slightly_bullish':
      return 'default'
    case 'bearish':
    case 'slightly_bearish':
      return 'destructive'
    default:
      return 'secondary'
  }
})

const recommendationLabel = computed(() => {
  if (!marketInsight.value) return ''
  return marketInsight.value.recommendation.replace('_', ' ').toUpperCase()
})

const sentimentEmoji = computed(() => {
  if (!marketInsight.value) return ''
  switch (marketInsight.value.sentiment.sentiment) {
    case 'positive':
      return '+'
    case 'negative':
      return '-'
    default:
      return '~'
  }
})

async function analyzeNews(): Promise<void> {
  if (!canAnalyze.value) return

  isAnalyzing.value = true
  error.value = ''
  marketInsight.value = null

  try {
    const response = await axios.post<{ data: { attributes: MarketInsight } }>(
      '/api/ai/market-insight',
      {
        symbol: selectedSymbol.value,
        news_text: newsText.value,
      },
      {
        headers: {
          Authorization: `Bearer ${store.token}`,
        },
      }
    )

    marketInsight.value = response.data.data.attributes
  } catch (err) {
    if (axios.isAxiosError(err)) {
      error.value = err.response?.data?.message || 'Failed to analyze news'
    } else {
      error.value = 'An unexpected error occurred'
    }
  } finally {
    isAnalyzing.value = false
  }
}

function clearAnalysis(): void {
  newsText.value = ''
  marketInsight.value = null
  error.value = ''
}

const sampleNews = [
  'Bitcoin surges past $100,000 as institutional investors pile in. Major banks are now offering crypto custody services, signaling mainstream adoption.',
  'SEC announces new cryptocurrency regulations that could impact trading. Industry experts worry about increased compliance costs for exchanges.',
  'Ethereum completes major network upgrade, reducing gas fees by 50%. Developers praise the improvement for enabling more complex smart contracts.',
]

function loadSampleNews(index: number): void {
  newsText.value = sampleNews[index]
}
</script>

<template>
  <Card class="h-full">
    <CardHeader>
      <CardTitle class="flex items-center gap-2">
        AI Market Insights
      </CardTitle>
      <CardDescription>
        Analyze crypto news using AI-powered sentiment analysis
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <div class="space-y-2">
        <Label>Select Symbol</Label>
        <Select v-model="selectedSymbol" class="w-full">
          <option value="BTC">Bitcoin (BTC)</option>
          <option value="ETH">Ethereum (ETH)</option>
        </Select>
      </div>

      <div class="space-y-2">
        <Label>News / Article Text</Label>
        <Textarea
          v-model="newsText"
          placeholder="Paste a news article or market update to analyze..."
          class="min-h-[120px] resize-none"
          :disabled="isAnalyzing"
        />
        <p class="text-xs text-muted-foreground">
          Minimum 20 characters required
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <Button
          v-for="(_, index) in sampleNews"
          :key="index"
          variant="outline"
          size="sm"
          @click="loadSampleNews(index)"
          :disabled="isAnalyzing"
        >
          Sample {{ index + 1 }}
        </Button>
      </div>

      <div class="flex gap-2">
        <Button
          @click="analyzeNews"
          :disabled="!canAnalyze || isAnalyzing"
          class="flex-1"
        >
          <span v-if="isAnalyzing">Analyzing...</span>
          <span v-else>Analyze Sentiment</span>
        </Button>
        <Button
          v-if="marketInsight"
          variant="outline"
          @click="clearAnalysis"
        >
          Clear
        </Button>
      </div>

      <div v-if="error" class="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
        {{ error }}
      </div>

      <div v-if="marketInsight" class="space-y-4 rounded-lg border p-4">
        <div class="flex items-center justify-between">
          <h4 class="font-semibold">Analysis Results</h4>
          <Badge :variant="recommendationVariant">
            {{ recommendationLabel }}
          </Badge>
        </div>

        <div class="grid gap-3 text-sm">
          <div class="flex justify-between">
            <span class="text-muted-foreground">Sentiment</span>
            <span class="font-medium capitalize">
              {{ sentimentEmoji }} {{ marketInsight.sentiment.sentiment }}
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-muted-foreground">Confidence</span>
            <span class="font-medium">
              {{ marketInsight.sentiment.confidence }}%
            </span>
          </div>

          <div class="flex justify-between">
            <span class="text-muted-foreground">Symbol</span>
            <span class="font-medium">{{ marketInsight.symbol }}</span>
          </div>
        </div>

        <div v-if="marketInsight.categories.length > 0" class="space-y-2">
          <p class="text-sm text-muted-foreground">Related Topics</p>
          <div class="flex flex-wrap gap-1">
            <Badge
              v-for="category in marketInsight.categories.slice(0, 3)"
              :key="category.label"
              variant="outline"
              class="text-xs"
            >
              {{ category.label }} ({{ Math.round(category.score * 100) }}%)
            </Badge>
          </div>
        </div>

        <p class="text-sm text-muted-foreground italic">
          {{ marketInsight.summary }}
        </p>
      </div>
    </CardContent>
  </Card>
</template>
