<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Card, CardHeader, CardTitle, CardContent, Button, Input, Label } from '@/components/ui'
import type { UserWallet } from '@/types'
import axios from 'axios'

interface JsonApiWallet {
  type: string
  id: string
  attributes: {
    address: string
    label: string | null
    is_primary: boolean
    created_at: string
  }
}

const wallets = ref<UserWallet[]>([])
const selectedWallet = ref('')
const toAddress = ref('')
const amount = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')
const success = ref('')

const hasWallets = computed(() => wallets.value.length > 0)
const isValid = computed(() => {
  return (
    selectedWallet.value &&
    toAddress.value.length === 42 &&
    toAddress.value.startsWith('0x') &&
    parseFloat(amount.value) > 0 &&
    password.value.length >= 8
  )
})

async function fetchWallets() {
  try {
    const { data } = await axios.get<{ data: JsonApiWallet[] }>('/api/web3/wallets')
    wallets.value = data.data.map((w) => ({
      id: parseInt(w.id, 10),
      address: w.attributes.address,
      label: w.attributes.label,
      is_primary: w.attributes.is_primary,
      created_at: w.attributes.created_at,
    }))

    if (wallets.value.length > 0) {
      const primary = wallets.value.find((w) => w.is_primary)
      selectedWallet.value = primary?.address || wallets.value[0].address
    }
  } catch (e) {
    console.error('Failed to fetch wallets:', e)
  }
}

async function sendTransaction() {
  if (!isValid.value) return

  loading.value = true
  error.value = ''
  success.value = ''

  try {
    const { data } = await axios.post('/api/web3/transactions', {
      from: selectedWallet.value,
      to: toAddress.value,
      amount: amount.value,
      password: password.value,
    })

    success.value = `Transaction sent! Hash: ${data.data.attributes.tx_hash.slice(0, 10)}...`
    toAddress.value = ''
    amount.value = ''
    password.value = ''
  } catch (e: unknown) {
    const axiosError = e as { response?: { data?: { error?: string } } }
    error.value = axiosError.response?.data?.error || 'Failed to send transaction'
  } finally {
    loading.value = false
  }
}

function truncateAddress(address: string): string {
  return `${address.slice(0, 6)}...${address.slice(-4)}`
}

onMounted(fetchWallets)
</script>

<template>
  <Card class="border-border bg-card">
    <CardHeader class="pb-3">
      <CardTitle class="text-lg">Send ETH</CardTitle>
    </CardHeader>
    <CardContent>
      <div v-if="!hasWallets" class="py-4 text-center text-muted-foreground">
        Create a wallet first to send transactions.
      </div>

      <form v-else class="space-y-4" @submit.prevent="sendTransaction">
        <div>
          <Label for="from-wallet">From Wallet</Label>
          <select
            id="from-wallet"
            v-model="selectedWallet"
            class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
          >
            <option v-for="wallet in wallets" :key="wallet.id" :value="wallet.address">
              {{ wallet.label || truncateAddress(wallet.address) }}
              {{ wallet.is_primary ? '(Primary)' : '' }}
            </option>
          </select>
        </div>

        <div>
          <Label for="to-address">To Address</Label>
          <Input
            id="to-address"
            v-model="toAddress"
            placeholder="0x..."
            class="mt-1 font-mono"
          />
        </div>

        <div>
          <Label for="amount">Amount (ETH)</Label>
          <Input
            id="amount"
            v-model="amount"
            type="number"
            step="0.0001"
            min="0"
            placeholder="0.1"
            class="mt-1"
          />
        </div>

        <div>
          <Label for="tx-password">Wallet Password</Label>
          <Input
            id="tx-password"
            v-model="password"
            type="password"
            placeholder="Enter wallet password"
            class="mt-1"
          />
        </div>

        <div v-if="error" class="rounded-md bg-destructive/10 p-3 text-sm text-destructive">
          {{ error }}
        </div>

        <div v-if="success" class="rounded-md bg-green-500/10 p-3 text-sm text-green-600">
          {{ success }}
        </div>

        <Button type="submit" :disabled="!isValid || loading" class="w-full">
          {{ loading ? 'Sending...' : 'Send Transaction' }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>
