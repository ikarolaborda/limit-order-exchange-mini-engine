<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import * as z from 'zod'
import axios from 'axios'
import { Button, Input, Card, CardHeader, CardTitle, Label, ThemeToggle } from '@/components/ui'
import { toast } from 'vue-sonner'

const emit = defineEmits<{
  'login-success': [token: string]
}>()

const loginSchema = toTypedSchema(
  z.object({
    email: z.string().min(1, 'Email is required').email('Please enter a valid email'),
    password: z.string().min(1, 'Password is required'),
  })
)

const { handleSubmit, errors, defineField, resetForm } = useForm({
  validationSchema: loginSchema,
})

const [email, emailAttrs] = defineField('email')
const [password, passwordAttrs] = defineField('password')

const isLoading = ref(false)
const serverError = ref<string | null>(null)

const onSubmit = handleSubmit(async (values) => {
  isLoading.value = true
  serverError.value = null

  try {
    interface LoginResponse {
      data: {
        id: string
        attributes: {
          name: string
          email: string
          balance: string
        }
      }
      token: string
    }
    const { data } = await axios.post<LoginResponse>('/api/auth/login', values)
    toast.success('Login successful', {
      description: `Welcome back, ${data.data.attributes.name}!`,
    })
    emit('login-success', data.token)
    resetForm()
  } catch (error) {
    if (axios.isAxiosError(error) && error.response?.status === 422) {
      const responseData = error.response.data as { errors?: { email?: string[] } }
      serverError.value = responseData.errors?.email?.[0] ?? 'Invalid credentials'
    } else {
      serverError.value = 'An unexpected error occurred. Please try again.'
    }
    toast.error('Login failed', {
      description: serverError.value,
    })
  } finally {
    isLoading.value = false
  }
})

const demoUsers = [
  { email: 'trader1@example.com', name: 'Alice Trader' },
  { email: 'trader2@example.com', name: 'Bob Trader' },
  { email: 'trader3@example.com', name: 'Charlie Trader' },
  { email: 'trader4@example.com', name: 'Diana Trader' },
]

function fillDemoUser(demoEmail: string): void {
  email.value = demoEmail
  password.value = 'password'
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-background px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-4xl">
      <Card class="overflow-hidden">
        <div class="grid md:grid-cols-2">
          <!-- Left Column - Branding -->
          <div class="flex flex-col justify-between bg-primary p-8 text-primary-foreground">
            <div>
              <h1 class="text-3xl font-bold tracking-tight">
                Limit Order Exchange
              </h1>
              <p class="mt-2 text-primary-foreground/80">
                High-performance matching engine with real-time updates
              </p>
            </div>

            <div class="mt-8 space-y-4">
              <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-foreground/20">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold">Real-time Matching</h3>
                  <p class="text-sm text-primary-foreground/70">Orders matched instantly with WebSocket updates</p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-foreground/20">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold">Secure Trading</h3>
                  <p class="text-sm text-primary-foreground/70">Token-based authentication with Sanctum</p>
                </div>
              </div>

              <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-foreground/20">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                  </svg>
                </div>
                <div>
                  <h3 class="font-semibold">BTC & ETH Support</h3>
                  <p class="text-sm text-primary-foreground/70">Trade Bitcoin and Ethereum with USD</p>
                </div>
              </div>
            </div>

            <div class="mt-8">
              <p class="text-xs text-primary-foreground/60">
                Built with Laravel 12 + Vue 3 + PostgreSQL
              </p>
            </div>
          </div>

          <!-- Right Column - Login Form -->
          <div class="p-8">
            <div class="flex items-center justify-between">
              <CardHeader class="p-0">
                <CardTitle class="text-2xl">Sign in</CardTitle>
              </CardHeader>
              <ThemeToggle />
            </div>

            <p class="mt-2 text-sm text-muted-foreground">
              Enter your credentials to access your trading account
            </p>

            <form class="mt-6 space-y-4" @submit="onSubmit">
              <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input
                  id="email"
                  v-model="email"
                  v-bind="emailAttrs"
                  type="email"
                  placeholder="trader@example.com"
                  :class="{ 'border-destructive': errors.email }"
                />
                <p v-if="errors.email" class="text-sm text-destructive">
                  {{ errors.email }}
                </p>
              </div>

              <div class="space-y-2">
                <Label for="password">Password</Label>
                <Input
                  id="password"
                  v-model="password"
                  v-bind="passwordAttrs"
                  type="password"
                  placeholder="Enter your password"
                  :class="{ 'border-destructive': errors.password }"
                />
                <p v-if="errors.password" class="text-sm text-destructive">
                  {{ errors.password }}
                </p>
              </div>

              <p v-if="serverError" class="text-sm text-destructive">
                {{ serverError }}
              </p>

              <Button type="submit" class="w-full" :disabled="isLoading">
                <svg v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isLoading ? 'Signing in...' : 'Sign in' }}
              </Button>
            </form>

            <div class="mt-6">
              <div class="relative">
                <div class="absolute inset-0 flex items-center">
                  <span class="w-full border-t" />
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                  <span class="bg-background px-2 text-muted-foreground">Demo Accounts</span>
                </div>
              </div>

              <div class="mt-4 grid grid-cols-2 gap-2">
                <Button
                  v-for="user in demoUsers"
                  :key="user.email"
                  type="button"
                  variant="outline"
                  size="sm"
                  class="text-xs"
                  @click="fillDemoUser(user.email)"
                >
                  {{ user.name }}
                </Button>
              </div>

              <p class="mt-4 text-center text-xs text-muted-foreground">
                Click any demo user to auto-fill credentials (password: <code class="rounded bg-muted px-1">password</code>)
              </p>
            </div>
          </div>
        </div>
      </Card>
    </div>
  </div>
</template>
