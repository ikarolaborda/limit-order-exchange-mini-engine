<script setup lang="ts">
import { WalletCard, SendTransactionForm, TransactionHistory } from '@/components/web3'
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Web3 / Ethereum</h1>
      <p class="text-sm text-muted-foreground">
        Manage your Ethereum wallets, send transactions, and view history
      </p>
    </div>

    <section class="grid gap-6 lg:grid-cols-3">
      <WalletCard />
      <SendTransactionForm />
      <TransactionHistory />
    </section>
  </div>
</template>
