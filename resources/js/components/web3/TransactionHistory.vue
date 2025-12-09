<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Card, CardHeader, CardTitle, CardContent, Badge } from '@/components/ui'
import type { BlockchainTransaction } from '@/types'
import axios from 'axios'

interface JsonApiTransaction {
  type: string
  id: string
  attributes: {
    tx_hash: string
    from_address: string
    to_address: string
    amount: string
    status: 'pending' | 'success' | 'failed'
    block_number: number | null
    confirmations: number
    created_at: string
  }
}

const transactions = ref<BlockchainTransaction[]>([])
const loading = ref(false)

const hasTransactions = computed(() => transactions.value.length > 0)

async function fetchTransactions() {
  loading.value = true
  try {
    const { data } = await axios.get<{ data: JsonApiTransaction[] }>('/api/web3/transactions')
    transactions.value = data.data.map((tx) => ({
      id: parseInt(tx.id, 10),
      tx_hash: tx.attributes.tx_hash,
      from_address: tx.attributes.from_address,
      to_address: tx.attributes.to_address,
      amount: tx.attributes.amount,
      status: tx.attributes.status,
      block_number: tx.attributes.block_number,
      confirmations: tx.attributes.confirmations,
      created_at: tx.attributes.created_at,
    }))
  } catch (e) {
    console.error('Failed to fetch transactions:', e)
  } finally {
    loading.value = false
  }
}

function truncateHash(hash: string): string {
  return `${hash.slice(0, 10)}...${hash.slice(-8)}`
}

function truncateAddress(address: string): string {
  return `${address.slice(0, 6)}...${address.slice(-4)}`
}

function formatAmount(amount: string): string {
  return parseFloat(amount).toFixed(4)
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleString()
}

function getStatusVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' | 'success' {
  switch (status) {
    case 'success':
      return 'success'
    case 'pending':
      return 'secondary'
    case 'failed':
      return 'destructive'
    default:
      return 'outline'
  }
}

function copyHash(hash: string) {
  navigator.clipboard.writeText(hash)
}

onMounted(fetchTransactions)
</script>

<template>
  <Card class="border-border bg-card">
    <CardHeader class="pb-3">
      <CardTitle class="text-lg">Transaction History</CardTitle>
    </CardHeader>
    <CardContent>
      <div v-if="loading" class="py-4 text-center text-muted-foreground">
        Loading transactions...
      </div>

      <div v-else-if="!hasTransactions" class="py-4 text-center text-muted-foreground">
        No transactions yet.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="tx in transactions"
          :key="tx.id"
          class="rounded-lg border border-border p-3"
        >
          <div class="flex items-start justify-between">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <button
                  class="font-mono text-sm hover:text-primary"
                  title="Copy transaction hash"
                  @click="copyHash(tx.tx_hash)"
                >
                  {{ truncateHash(tx.tx_hash) }}
                </button>
                <Badge :variant="getStatusVariant(tx.status)" class="text-xs">
                  {{ tx.status }}
                </Badge>
              </div>
              <div class="text-xs text-muted-foreground">
                <span class="font-mono">{{ truncateAddress(tx.from_address) }}</span>
                <span class="mx-1">→</span>
                <span class="font-mono">{{ truncateAddress(tx.to_address) }}</span>
              </div>
              <div class="text-xs text-muted-foreground">
                {{ formatDate(tx.created_at) }}
              </div>
            </div>
            <div class="text-right">
              <div class="font-semibold">{{ formatAmount(tx.amount) }} ETH</div>
              <div v-if="tx.block_number" class="text-xs text-muted-foreground">
                Block #{{ tx.block_number }}
              </div>
              <div v-if="tx.confirmations > 0" class="text-xs text-muted-foreground">
                {{ tx.confirmations }} confirmations
              </div>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
