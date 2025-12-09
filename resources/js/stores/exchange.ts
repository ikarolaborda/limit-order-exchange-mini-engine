import { defineStore } from 'pinia'
import axios from 'axios'
import Echo from 'laravel-echo'
import type { Profile, Order, Trade, Symbol, Side, OrderStatus, ExchangeRates, AppNotification, NotificationsResponse } from '@/types'

type EchoInstance = InstanceType<typeof Echo>

// JSON:API response types
interface JsonApiOrder {
  type: string
  id: string
  attributes: {
    symbol: Symbol
    side: Side
    price: string
    amount: string
    locked_usd: string
    status: OrderStatus
    status_label: string
    created_at: string
    updated_at: string
  }
  relationships?: {
    user?: {
      data: {
        type: string
        id: string
      }
    }
  }
}

interface JsonApiOrderCollection {
  data: JsonApiOrder[]
  meta?: {
    total: number
  }
}

interface JsonApiTrade {
  type: string
  id: string
  attributes: {
    symbol: Symbol
    price: string
    amount: string
    fee: string
    created_at: string
  }
  relationships?: {
    buy_order?: {
      data: {
        type: string
        id: string
      }
    }
    sell_order?: {
      data: {
        type: string
        id: string
      }
    }
  }
}

interface JsonApiTradeCollection {
  data: JsonApiTrade[]
}

// Transform JSON:API order to flat Order object
function transformOrder(jsonApiOrder: JsonApiOrder): Order {
  return {
    id: parseInt(jsonApiOrder.id, 10),
    user_id: parseInt(jsonApiOrder.relationships?.user?.data?.id || '0', 10),
    symbol: jsonApiOrder.attributes.symbol,
    side: jsonApiOrder.attributes.side,
    price: jsonApiOrder.attributes.price,
    amount: jsonApiOrder.attributes.amount,
    locked_usd: jsonApiOrder.attributes.locked_usd,
    status: jsonApiOrder.attributes.status,
    created_at: jsonApiOrder.attributes.created_at,
    updated_at: jsonApiOrder.attributes.updated_at,
  }
}

// Transform JSON:API trade to flat Trade object
function transformTrade(jsonApiTrade: JsonApiTrade): Trade {
  return {
    symbol: jsonApiTrade.attributes.symbol,
    price: jsonApiTrade.attributes.price,
    amount: jsonApiTrade.attributes.amount,
    fee: jsonApiTrade.attributes.fee,
    buy_order_id: parseInt(jsonApiTrade.relationships?.buy_order?.data?.id || '0', 10),
    sell_order_id: parseInt(jsonApiTrade.relationships?.sell_order?.data?.id || '0', 10),
  }
}

interface OrderbookFilters {
  side: Side | null
  status: OrderStatus | null
}

interface ExchangeState {
  token: string
  profile: Profile | null
  orderbook: Order[]
  myOrders: Order[]
  trades: Trade[]
  loading: boolean
  symbol: Symbol
  side: Side
  price: string
  amount: string
  echo: EchoInstance | null
  exchangeRates: ExchangeRates
  orderbookFilters: OrderbookFilters
  notifications: AppNotification[]
  unreadNotificationCount: number
}

type EchoFactory = (token: string) => EchoInstance | null

export const useExchangeStore = defineStore('exchange', {
  state: (): ExchangeState => ({
    token: localStorage.getItem('api_token') ?? '',
    profile: null,
    orderbook: [],
    myOrders: [],
    trades: [],
    loading: false,
    symbol: 'BTC',
    side: 'buy',
    price: '',
    amount: '',
    echo: null,
    exchangeRates: {
      BTC: null,
      ETH: null,
      lastUpdated: null,
    },
    orderbookFilters: {
      side: null,
      status: null,
    },
    notifications: [],
    unreadNotificationCount: 0,
  }),

  getters: {
    isAuthenticated(): boolean {
      return !!this.token
    },
  },

  actions: {
    setToken(token: string): void {
      this.token = token
      if (token) {
        localStorage.setItem('api_token', token)
        axios.defaults.headers.common.Authorization = `Bearer ${token}`
      } else {
        localStorage.removeItem('api_token')
        delete axios.defaults.headers.common.Authorization
      }
    },

    async logout(): Promise<void> {
      if (this.echo) {
        this.echo.disconnect()
        this.echo = null
      }

      if (this.token) {
        try {
          await axios.post('/api/auth/logout')
        } catch {
          // Token may already be invalid, proceed with local cleanup
        }
      }

      this.setToken('')
      this.profile = null
      this.orderbook = []
      this.myOrders = []
      this.trades = []
      this.notifications = []
      this.unreadNotificationCount = 0
    },

    initEcho(createEcho: EchoFactory): void {
      if (!this.token || this.echo) return
      const echo = createEcho(this.token)
      if (echo) {
        this.echo = echo
      }
    },

    async fetchProfile(): Promise<void> {
      if (!this.token) return
      this.loading = true
      try {
        interface JsonApiProfile {
          data: {
            id: string
            attributes: { name: string; email: string; balance: string; locked_balance: string }
            relationships?: {
              assets: Array<{
                id: string
                attributes: { symbol: string; amount: string; locked_amount: string }
              }>
            }
          }
        }
        const response = await axios.get<JsonApiProfile>('/api/profile')
        const attrs = response.data.data.attributes
        const assets = response.data.data.relationships?.assets?.map((a) => ({
          id: parseInt(a.id, 10),
          user_id: parseInt(response.data.data.id, 10),
          symbol: a.attributes.symbol as Symbol,
          amount: a.attributes.amount,
          locked_amount: a.attributes.locked_amount,
        })) ?? []
        this.profile = {
          id: parseInt(response.data.data.id, 10),
          name: attrs.name,
          email: attrs.email,
          balance: attrs.balance,
          locked_balance: attrs.locked_balance,
          created_at: '',
          updated_at: '',
          assets,
        }
      } finally {
        this.loading = false
      }
    },

    async fetchOrderbook(): Promise<void> {
      if (!this.token) return
      const params: Record<string, string | number> = { symbol: this.symbol }
      if (this.orderbookFilters.side) {
        params.side = this.orderbookFilters.side
      }
      if (this.orderbookFilters.status !== null) {
        params.status = this.orderbookFilters.status
      }
      const { data } = await axios.get<JsonApiOrderCollection>('/api/orders', { params })
      this.orderbook = data.data.map(transformOrder)
    },

    setOrderbookFilters(filters: Partial<OrderbookFilters>): void {
      this.orderbookFilters = { ...this.orderbookFilters, ...filters }
      this.fetchOrderbook()
    },

    clearOrderbookFilters(): void {
      this.orderbookFilters = { side: null, status: null }
      this.fetchOrderbook()
    },

    async fetchMyOrders(): Promise<void> {
      if (!this.token) return
      const { data } = await axios.get<JsonApiOrderCollection>('/api/my-orders')
      this.myOrders = data.data.map(transformOrder)
    },

    async fetchTrades(): Promise<void> {
      if (!this.token) return
      const { data } = await axios.get<JsonApiTradeCollection>('/api/trades', {
        params: { symbol: this.symbol },
      })
      this.trades = data.data.map(transformTrade)
    },

    async placeOrder(): Promise<Order> {
      const payload = {
        symbol: this.symbol,
        side: this.side,
        price: this.price,
        amount: this.amount,
      }
      const { data } = await axios.post<Order>('/api/orders', payload)
      await Promise.all([this.fetchProfile(), this.fetchOrderbook(), this.fetchMyOrders()])
      return data
    },

    async cancel(orderId: number): Promise<void> {
      await axios.post(`/api/orders/${orderId}/cancel`)
      await Promise.all([this.fetchProfile(), this.fetchOrderbook(), this.fetchMyOrders()])
    },

    handleTrade(trade: Trade): void {
      this.trades.unshift(trade)
      this.fetchProfile()
      this.fetchOrderbook()
      this.fetchMyOrders()
      this.fetchTrades()
    },

    async fetchNotifications(): Promise<AppNotification[]> {
      if (!this.token) return []
      const { data } = await axios.get<NotificationsResponse>('/api/notifications')
      this.notifications = data.data
      this.unreadNotificationCount = data.meta.unread_count
      return data.data.filter((n) => n.read_at === null)
    },

    async markNotificationRead(notificationId: string): Promise<void> {
      await axios.post(`/api/notifications/${notificationId}/read`)
      const notification = this.notifications.find((n) => n.id === notificationId)
      if (notification) {
        notification.read_at = new Date().toISOString()
        this.unreadNotificationCount = Math.max(0, this.unreadNotificationCount - 1)
      }
    },

    async markAllNotificationsRead(): Promise<void> {
      await axios.post('/api/notifications/read-all')
      this.notifications.forEach((n) => {
        if (n.read_at === null) {
          n.read_at = new Date().toISOString()
        }
      })
      this.unreadNotificationCount = 0
    },

    async fetchExchangeRates(): Promise<void> {
      const FALLBACK_RATES = {
        BTC: 95000,
        ETH: 3500,
      }

      try {
        interface RatesResponse {
          BTC: number
          ETH: number
          source: string
        }
        const { data } = await axios.get<RatesResponse>('/api/market/rates', {
          timeout: 5000,
        })
        this.exchangeRates = {
          BTC: data.BTC ?? FALLBACK_RATES.BTC,
          ETH: data.ETH ?? FALLBACK_RATES.ETH,
          lastUpdated: new Date(),
        }
      } catch (error) {
        console.warn('Failed to fetch exchange rates, using fallback:', error)
        this.exchangeRates = {
          BTC: FALLBACK_RATES.BTC,
          ETH: FALLBACK_RATES.ETH,
          lastUpdated: null,
        }
      }
    },
  },
})

