<script setup lang="ts">
import { useField } from 'vee-validate'
import { provide, computed, toRef } from 'vue'

interface Props {
  name: string
}

const props = defineProps<Props>()
const { value, errorMessage, handleBlur, handleChange, meta } = useField(toRef(props, 'name'))

provide('form-field-state', {
  name: props.name,
  value,
  errorMessage,
  handleBlur,
  handleChange,
  meta,
})

const hasError = computed(() => !!errorMessage.value)

provide('form-field-error', hasError)
</script>

<template>
  <div class="space-y-2">
    <slot :value="value" :error-message="errorMessage" :handle-blur="handleBlur" :handle-change="handleChange" :meta="meta" />
  </div>
</template>
