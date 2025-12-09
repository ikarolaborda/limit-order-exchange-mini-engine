export interface User {
  id: number
  name: string
  email: string
  balance: string
  created_at: string
  updated_at: string
}

export interface Asset {
  id: number
  user_id: number
  symbol: Symbol
  amount: string
  locked_amount: string
}

export interface Profile extends User {
  assets: Asset[]
  locked_balance: string
}

export interface Order {
  id: number
  user_id: number
  symbol: Symbol
  side: Side
  price: string
  amount: string
  locked_usd: string
  status: OrderStatus
  created_at: string
  updated_at: string
}

export enum OrderStatus {
  OPEN = 1,
  FILLED = 2,
  CANCELLED = 3,
}

export interface Trade {
  symbol: Symbol
  price: string
  amount: string
  fee: string
  buy_order_id: number
  sell_order_id: number
}

export interface ApiResponse<T> {
  data: T
}

export interface OrderFormData {
  symbol: Symbol
  side: Side
  price: string
  amount: string
}

export type Symbol = 'BTC' | 'ETH'
export type Side = 'buy' | 'sell'

export interface ExchangeRates {
  BTC: number | null
  ETH: number | null
  lastUpdated: Date | null
}

export interface OrderFilledNotificationData {
  trade_id: number
  symbol: Symbol
  price: string
  amount: string
  total: string
  side: Side
}

export interface AppNotification {
  id: string
  type: string
  data: OrderFilledNotificationData
  read_at: string | null
  created_at: string
}

export interface NotificationsResponse {
  data: AppNotification[]
  meta: {
    unread_count: number
  }
}

