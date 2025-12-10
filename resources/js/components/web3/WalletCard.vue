<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Card, CardHeader, CardTitle, CardContent, CardFooter, Badge, Button, Input, Label } from '@/components/ui'
import { useWallets } from '@/composables/useWallets'

const { wallets, balances, loading, hasWallets, createWallet, initializeIfNeeded } = useWallets()

const creating = ref(false)
const newWalletPassword = ref('')
const newWalletLabel = ref('')
const showCreateForm = ref(false)
const error = ref('')

async function handleCreateWallet() {
  if (!newWalletPassword.value || newWalletPassword.value.length < 8) {
    error.value = 'Password must be at least 8 characters'
    return
  }

  creating.value = true
  error.value = ''

  try {
    await createWallet(newWalletPassword.value, newWalletLabel.value || null)
    newWalletPassword.value = ''
    newWalletLabel.value = ''
    showCreateForm.value = false
  } catch (e: unknown) {
    const axiosError = e as { response?: { data?: { error?: string } } }
    error.value = axiosError.response?.data?.error || 'Failed to create wallet'
  } finally {
    creating.value = false
  }
}

function formatEth(value: string): string {
  const num = parseFloat(value)
  if (num === 0) return '0.0000'
  return num.toFixed(4)
}

function truncateAddress(address: string): string {
  return `${address.slice(0, 6)}...${address.slice(-4)}`
}

function copyAddress(address: string) {
  navigator.clipboard.writeText(address)
}

onMounted(initializeIfNeeded)
</script>

<template>
  <Card class="border-border bg-card">
    <CardHeader class="pb-3">
      <div class="flex items-center justify-between">
        <CardTitle class="text-lg">Ethereum Wallets</CardTitle>
        <Button v-if="!showCreateForm" size="sm" @click="showCreateForm = true">
          + New Wallet
        </Button>
      </div>
    </CardHeader>
    <CardContent>
      <div v-if="showCreateForm" class="mb-4 space-y-3 rounded-lg border border-border p-4">
        <div>
          <Label for="wallet-label">Label (optional)</Label>
          <Input
            id="wallet-label"
            v-model="newWalletLabel"
            placeholder="My Trading Wallet"
            class="mt-1"
          />
        </div>
        <div>
          <Label for="wallet-password">Password</Label>
          <Input
            id="wallet-password"
            v-model="newWalletPassword"
            type="password"
            placeholder="Min 8 characters"
            class="mt-1"
          />
          <p class="mt-1 text-xs text-muted-foreground">
            This password encrypts your private key. Store it securely - it cannot be recovered.
          </p>
        </div>
        <div v-if="error" class="text-sm text-destructive">{{ error }}</div>
        <div class="flex gap-2">
          <Button :disabled="creating" @click="handleCreateWallet">
            {{ creating ? 'Creating...' : 'Create Wallet' }}
          </Button>
          <Button variant="outline" @click="showCreateForm = false">Cancel</Button>
        </div>
      </div>

      <div v-if="loading" class="py-4 text-center text-muted-foreground">
        Loading wallets...
      </div>

      <div v-else-if="!hasWallets && !showCreateForm" class="py-4 text-center text-muted-foreground">
        No wallets yet. Create your first Ethereum wallet to get started.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="wallet in wallets"
          :key="wallet.id"
          class="rounded-lg border border-border p-3"
        >
          <div class="flex items-start justify-between">
            <div>
              <div class="flex items-center gap-2">
                <span class="font-mono text-sm">{{ truncateAddress(wallet.address) }}</span>
                <button
                  class="text-muted-foreground hover:text-foreground"
                  title="Copy address"
                  @click="copyAddress(wallet.address)"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                    <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                  </svg>
                </button>
                <Badge v-if="wallet.is_primary" variant="secondary" class="text-xs">Primary</Badge>
              </div>
              <div v-if="wallet.label" class="mt-1 text-sm text-muted-foreground">
                {{ wallet.label }}
              </div>
            </div>
            <div class="text-right">
              <div v-if="balances[wallet.address]" class="font-semibold">
                {{ formatEth(balances[wallet.address].balance_eth) }} ETH
              </div>
              <div v-else class="text-sm text-muted-foreground">Loading...</div>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
    <CardFooter v-if="hasWallets" class="text-xs text-muted-foreground">
      Connected to local Ganache network (Chain ID: 1337)
    </CardFooter>
  </Card>
</template>
