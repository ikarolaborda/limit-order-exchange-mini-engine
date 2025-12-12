<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { toast } from 'vue-sonner'
import { useExchangeStore } from '@/stores/exchange'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
  Button,
  Input,
  Label,
  Badge,
  ScrollArea,
} from '@/components/ui'

interface Activity {
  id: number
  description: string
  ip_address: string | null
  user_agent: string | null
  created_at: string
}

const store = useExchangeStore()

const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const passwordLoading = ref(false)
const passwordError = ref('')

const activities = ref<Activity[]>([])
const activitiesLoading = ref(false)

async function changePassword(): Promise<void> {
  passwordError.value = ''

  if (newPassword.value !== confirmPassword.value) {
    passwordError.value = 'Passwords do not match'
    return
  }

  if (newPassword.value.length < 8) {
    passwordError.value = 'Password must be at least 8 characters'
    return
  }

  passwordLoading.value = true

  try {
    await axios.post(
      '/api/profile/password',
      {
        current_password: currentPassword.value,
        password: newPassword.value,
        password_confirmation: confirmPassword.value,
      },
      {
        headers: { Authorization: `Bearer ${store.token}` },
      }
    )

    toast.success('Password updated successfully')
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
  } catch (err: unknown) {
    const error = err as { response?: { data?: { message?: string } } }
    passwordError.value = error.response?.data?.message || 'Failed to update password'
  } finally {
    passwordLoading.value = false
  }
}

async function fetchActivities(): Promise<void> {
  activitiesLoading.value = true

  try {
    const response = await axios.get<{ data: Activity[] }>('/api/profile/activities', {
      headers: { Authorization: `Bearer ${store.token}` },
    })
    activities.value = response.data.data
  } catch {
    toast.error('Failed to load activity log')
  } finally {
    activitiesLoading.value = false
  }
}

function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(fetchActivities)
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Settings</h1>
      <p class="text-sm text-muted-foreground">
        Manage your account settings and view activity
      </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
      <Card>
        <CardHeader>
          <CardTitle>Profile Information</CardTitle>
          <CardDescription>Your account details</CardDescription>
        </CardHeader>
        <CardContent v-if="store.profile" class="space-y-4">
          <div class="space-y-2">
            <Label>Name</Label>
            <Input :model-value="store.profile.name" disabled />
          </div>
          <div class="space-y-2">
            <Label>Email</Label>
            <Input :model-value="store.profile.email" disabled />
          </div>
          <div class="flex items-center gap-2">
            <Label>Balance:</Label>
            <Badge variant="success">
              ${{ parseFloat(store.profile.balance).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
            </Badge>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Change Password</CardTitle>
          <CardDescription>Update your password to keep your account secure</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="space-y-2">
            <Label for="current-password">Current Password</Label>
            <Input
              id="current-password"
              v-model="currentPassword"
              type="password"
              placeholder="Enter current password"
            />
          </div>
          <div class="space-y-2">
            <Label for="new-password">New Password</Label>
            <Input
              id="new-password"
              v-model="newPassword"
              type="password"
              placeholder="Enter new password"
            />
          </div>
          <div class="space-y-2">
            <Label for="confirm-password">Confirm New Password</Label>
            <Input
              id="confirm-password"
              v-model="confirmPassword"
              type="password"
              placeholder="Confirm new password"
            />
          </div>
          <p v-if="passwordError" class="text-sm text-destructive">{{ passwordError }}</p>
        </CardContent>
        <CardFooter>
          <Button :disabled="passwordLoading" @click="changePassword">
            {{ passwordLoading ? 'Updating...' : 'Update Password' }}
          </Button>
        </CardFooter>
      </Card>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Activity Log</CardTitle>
        <CardDescription>Recent activity on your account</CardDescription>
      </CardHeader>
      <CardContent>
        <div v-if="activitiesLoading" class="text-center text-muted-foreground py-4">
          Loading activities...
        </div>
        <ScrollArea v-else-if="activities.length > 0" class="h-[400px]">
          <div class="space-y-4">
            <div
              v-for="activity in activities"
              :key="activity.id"
              class="flex items-start justify-between rounded-lg border p-4"
            >
              <div class="space-y-1">
                <p class="font-medium">{{ activity.description }}</p>
                <p v-if="activity.ip_address" class="text-xs text-muted-foreground">
                  IP: {{ activity.ip_address }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-sm text-muted-foreground">{{ formatDate(activity.created_at) }}</p>
              </div>
            </div>
          </div>
        </ScrollArea>
        <div v-else class="text-center text-muted-foreground py-8">
          No activity recorded yet
        </div>
      </CardContent>
    </Card>
  </div>
</template>
