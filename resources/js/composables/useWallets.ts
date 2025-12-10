import { ref, computed } from 'vue'
import axios from 'axios'
import type { UserWallet, WalletBalance } from '@/types'

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

interface JsonApiBalance {
  type: string
  id: string
  attributes: {
    address: string
    balance_wei: string
    balance_eth: string
  }
}

const wallets = ref<UserWallet[]>([])
const balances = ref<Record<string, WalletBalance>>({})
const loading = ref(false)
const initialized = ref(false)

export function useWallets() {
  const hasWallets = computed(() => wallets.value.length > 0)

  async function fetchWallets() {
    loading.value = true
    try {
      const { data } = await axios.get<{ data: JsonApiWallet[] }>('/api/web3/wallets')
      wallets.value = data.data.map((w) => ({
        id: parseInt(w.id, 10),
        address: w.attributes.address,
        label: w.attributes.label,
        is_primary: w.attributes.is_primary,
        created_at: w.attributes.created_at,
      }))

      for (const wallet of wallets.value) {
        fetchBalance(wallet.address)
      }

      initialized.value = true
    } catch (e) {
      console.error('Failed to fetch wallets:', e)
    } finally {
      loading.value = false
    }
  }

  async function fetchBalance(address: string) {
    try {
      const wallet = wallets.value.find((w) => w.address === address)
      if (!wallet) return

      const { data } = await axios.get<{ data: JsonApiBalance }>(`/api/web3/wallets/${wallet.id}/balance`)
      balances.value[address] = {
        address: data.data.attributes.address,
        balance_wei: data.data.attributes.balance_wei,
        balance_eth: data.data.attributes.balance_eth,
      }
    } catch (e) {
      console.error('Failed to fetch balance:', e)
    }
  }

  async function createWallet(password: string, label: string | null): Promise<void> {
    await axios.post('/api/web3/wallets', { password, label })
    await fetchWallets()
  }

  async function initializeIfNeeded() {
    if (!initialized.value) {
      await fetchWallets()
    }
  }

  function reset() {
    wallets.value = []
    balances.value = {}
    loading.value = false
    initialized.value = false
  }

  return {
    wallets,
    balances,
    loading,
    hasWallets,
    fetchWallets,
    fetchBalance,
    createWallet,
    initializeIfNeeded,
    reset,
  }
}
