<script setup lang="ts">
import { computed, inject } from 'vue'
import type { ComputedRef } from 'vue'

const errorMessage = inject<ComputedRef<string | undefined>>('form-field-error-message')

const message = computed(() => errorMessage?.value)
</script>

<template>
  <p v-if="message" class="text-[0.8rem] font-medium text-destructive">
    {{ message }}
  </p>
</template>
