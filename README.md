# Limit Order Exchange Mini Engine

<p align="center">
  <strong>A production-ready cryptocurrency trading platform demonstrating modern software engineering practices</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php" alt="PHP 8.5">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=flat-square&logo=vuedotjs" alt="Vue 3.5">
  <img src="https://img.shields.io/badge/TypeScript-5.6-3178C6?style=flat-square&logo=typescript" alt="TypeScript 5.6">
  <img src="https://img.shields.io/badge/Go-1.23-00ADD8?style=flat-square&logo=go" alt="Go 1.23">
  <img src="https://img.shields.io/badge/PostgreSQL-16-4169E1?style=flat-square&logo=postgresql" alt="PostgreSQL 16">
  <img src="https://img.shields.io/badge/Ethereum-Ganache-3C3C3D?style=flat-square&logo=ethereum" alt="Ethereum">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="MIT License">
</p>

---

## Table of Contents

- [What is This Project?](#what-is-this-project)
- [Key Features](#key-features)
- [Quick Start Guide](#quick-start-guide)
- [Architecture Overview](#architecture-overview)
- [Infrastructure & Services](#infrastructure--services)
- [Web3 / Ethereum Integration](#web3--ethereum-integration)
- [Notification System](#notification-system)
- [Design Patterns & Principles](#design-patterns--principles)
- [Frontend Components](#frontend-components)
- [API Reference](#api-reference)
- [Development Guide](#development-guide)
- [Testing Strategy](#testing-strategy)
- [FAQ](#faq)
- [Contributing](#contributing)
- [License](#license)

---

## What is This Project?

The **Limit Order Exchange Mini Engine** is an educational and demonstration project that implements a fully functional cryptocurrency trading platform. It showcases how to build a real-time financial application using modern web technologies and best practices.

### Who is This For?

- **Developers** learning full-stack development with Laravel and Vue.js
- **Engineers** exploring real-time WebSocket implementations
- **Teams** looking for reference architecture for financial applications
- **Students** studying software design patterns and clean architecture

### What Can You Do With It?

- Place **buy** and **sell** limit orders for BTC and ETH
- See **real-time orderbook updates** via WebSockets
- **Match orders** automatically based on price-time priority
- Track your **portfolio balance** and **trading history**
- Experience a **professional trading UI** with dark/light mode

---

## Key Features

### Trading Engine
- **Price-Time Priority Matching**: Orders are matched fairly based on price first, then time of submission
- **Limit Orders Only**: Users specify the exact price they want to buy/sell at
- **Partial Fills**: Large orders can be filled incrementally as matching orders arrive
- **Fee System**: 1.5% trading fee applied to buyers, locked upfront to ensure settlement

### Real-Time Updates
- **WebSocket Broadcasting**: Instant updates when orders are placed, matched, or cancelled
- **Live Orderbook**: See bid/ask depth update in real-time
- **Trade Notifications**: Toast notifications for successful trades
- **Offline Notifications**: Database-persisted notifications for trades that occur while logged out

### Web3 / Blockchain
- **Ethereum Wallet Management**: Create and manage Ethereum wallets
- **ETH Transactions**: Send ETH via Go-based Web3 microservice
- **Local Blockchain**: Ganache for development with pre-funded accounts
- **Secure Key Storage**: Private keys never leave the Go service

### Security
- **Token-Based Auth**: Laravel Sanctum for secure API authentication
- **Input Validation**: All inputs validated server-side with Laravel Form Requests
- **CSRF Protection**: Built-in protection against cross-site request forgery
- **Type Safety**: Full TypeScript coverage on the frontend

### User Experience
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Dark/Light Mode**: System-aware theme with manual toggle
- **Live Market Prices**: Real-time BTC/ETH prices from CoinGecko API
- **Order Calculator**: Automatic total calculation with fee breakdown

---

## Quick Start Guide

### Prerequisites

Before you begin, ensure you have installed:

| Requirement | Version | Check Command |
|-------------|---------|---------------|
| Docker | 20.10+ | `docker --version` |
| Docker Compose | 2.0+ | `docker compose version` |
| Git | 2.0+ | `git --version` |
| Make | Any | `make --version` |

> **Note**: Make is optional but highly recommended. All commands can be run manually if needed.

### Step 1: Clone the Repository

```bash
git clone https://github.com/ikarolaborda/limit-order-exchange-mini-engine.git
cd limit-order-exchange-mini-engine
```

### Step 2: Run Installation

```bash
make install
```

This single command will automatically:

1. Copy `.env.example` to `.env` (if not exists)
2. Build Docker images for all services
3. Start all containers (app, database, redis, soketi, node)
4. Install PHP dependencies via Composer
5. Generate Laravel application key
6. Run database migrations
7. Seed demo users, assets, and sample trades
8. Install npm dependencies
9. Build frontend assets with Vite

**Expected duration**: 2-5 minutes on first run (depending on internet speed)

### Step 3: Access the Application

Open your browser and navigate to:

```
http://localhost:8000
```

You'll see the login screen. Use any of the demo accounts below.

### Demo Accounts

All accounts use the password: **`password`**

| Email | Name | USD Balance | BTC | ETH |
|-------|------|-------------|-----|-----|
| `trader1@example.com` | Alice Trader | $100,000 | 2.5 | 20 |
| `trader2@example.com` | Bob Trader | $75,000 | 1.5 | 15 |
| `trader3@example.com` | Charlie Trader | $50,000 | 3.0 | 10 |
| `trader4@example.com` | Diana Trader | $125,000 | 0.5 | 25 |

### Step 4: Try It Out!

1. **Login** with `trader1@example.com` / `password`
2. **Place a Buy Order**: Select BTC, enter price (try clicking "Market" to use current rate), enter amount
3. **Open another browser** (or incognito window) and login as `trader2@example.com`
4. **Place a matching Sell Order**: Same symbol, price at or below the buy price
5. **Watch the magic**: Orders match instantly, balances update, trade appears in history

---

## Architecture Overview

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                            Browser                                   │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │                    Vue 3 SPA (TypeScript)                    │   │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐    │   │
│  │  │  Pinia   │  │ shadcn-  │  │  Axios   │  │  Echo    │    │   │
│  │  │  Store   │  │   vue    │  │  HTTP    │  │WebSocket │    │   │
│  │  └──────────┘  └──────────┘  └──────────┘  └──────────┘    │   │
│  └─────────────────────────────────────────────────────────────┘   │
└───────────────────────────────┬─────────────────────────────────────┘
                                │ HTTP/WS
┌───────────────────────────────▼─────────────────────────────────────┐
│                         Docker Network                               │
│                                                                      │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐          │
│  │     App      │    │    Soketi    │    │    Redis     │          │
│  │ (FrankenPHP) │◄──►│  (WebSocket) │◄──►│   (Cache)    │          │
│  │  Port 8000   │    │  Port 6001   │    │  Port 6379   │          │
│  └──────┬───────┘    └──────────────┘    └──────────────┘          │
│         │                                                            │
│         ▼                                                            │
│  ┌──────────────┐    ┌──────────────┐                               │
│  │  PostgreSQL  │    │     Node     │                               │
│  │  (Database)  │    │ (Build Tool) │                               │
│  │  Port 5432   │    │              │                               │
│  └──────────────┘    └──────────────┘                               │
└──────────────────────────────────────────────────────────────────────┘
```

### Backend Directory Structure

```
app/
├── Actions/                    # Business logic (Single Action Classes)
│   ├── Auth/                   # Login, Register, Logout
│   ├── Market/                 # Exchange rates
│   ├── Matching/               # Order matching engine
│   ├── Order/                  # Create, Cancel orders
│   └── Profile/                # User profile
│
├── Contracts/                  # Interfaces (Dependency Inversion)
│   └── Repositories/           # Repository contracts
│
├── Http/
│   ├── Middleware/             # Request middleware (NoCacheHeaders)
│   ├── Requests/               # Form validation classes
│   └── Resources/              # JSON:API response transformers
│
├── Models/                     # Eloquent ORM models
│   ├── User.php
│   ├── Order.php
│   ├── Trade.php
│   └── Asset.php
│
├── Providers/                  # Service providers
│   └── RepositoryServiceProvider.php
│
├── Repositories/
│   ├── Eloquent/               # Database implementations
│   └── Cached/                 # Cache decorator layer
│
└── Support/
    └── Decimal.php             # High-precision arithmetic
```

### Frontend Directory Structure

```
resources/js/
├── app.ts                      # Application entry point
├── App.vue                     # Root component
├── bootstrap.ts                # Axios configuration
├── echo.ts                     # WebSocket setup
│
├── components/
│   ├── ui/                     # shadcn-vue base components
│   │   ├── button/
│   │   ├── card/
│   │   ├── input/
│   │   ├── tooltip/
│   │   └── ...
│   ├── auth/                   # Authentication
│   │   └── LoginForm.vue
│   ├── exchange/               # Trading widgets
│   │   ├── OrderForm.vue       # Place orders
│   │   ├── Orderbook.vue       # Bid/ask display
│   │   ├── MyOrders.vue        # User's orders
│   │   └── RecentTrades.vue    # Trade history
│   ├── market/
│   │   └── ExchangeRates.vue   # Live prices
│   └── profile/
│       ├── UserCard.vue        # User info
│       └── AssetList.vue       # Portfolio
│
├── stores/
│   └── exchange.ts             # Pinia state management
│
├── types/
│   └── index.ts                # TypeScript interfaces
│
└── lib/
    └── utils.ts                # Utility functions
```

---

## Infrastructure & Services

### Docker Services

| Service | Image | Port | Purpose |
|---------|-------|------|---------|
| **app** | Custom (FrankenPHP) | 8000 | Laravel API & serves SPA |
| **db** | postgres:16-alpine | 5432 | Primary database |
| **redis** | redis:7-alpine | 6379 | Cache, sessions, queue |
| **soketi** | soketi:1.6 | 6001 | WebSocket server (Pusher-compatible) |
| **node** | node:22-alpine | - | Frontend build tooling |
| **queue** | Custom (FrankenPHP) | - | Background job processing |
| **ganache** | trufflesuite/ganache | 8545 | Local Ethereum blockchain |
| **web3-service** | Custom (Go 1.23) | 8081 (internal) | Ethereum wallet & transaction service |

### Why These Technologies?

| Technology | Why We Chose It |
|------------|-----------------|
| **FrankenPHP** | Modern PHP server with built-in Octane support, 10x faster than PHP-FPM |
| **PostgreSQL** | ACID compliance, excellent for financial data, JSON support |
| **Redis** | Sub-millisecond caching, pub/sub for real-time features |
| **Soketi** | Open-source Pusher replacement, self-hosted WebSockets |
| **Vite** | Lightning-fast HMR, native ES modules, optimal production builds |

### Environment Configuration

Key environment variables (see `.env.example` for complete list):

```env
# Application
APP_NAME="Limit Order Exchange"
APP_URL=http://localhost:8000

# Database (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=db
DB_DATABASE=exchange
DB_USERNAME=exchange
DB_PASSWORD=exchange

# Cache & Sessions (Redis)
CACHE_STORE=redis
SESSION_DRIVER=redis
REDIS_HOST=redis

# WebSocket (Soketi)
BROADCAST_CONNECTION=pusher
PUSHER_HOST=soketi
PUSHER_PORT=6001
PUSHER_APP_KEY=app-key
PUSHER_APP_SECRET=app-secret

# Frontend (exposed to browser)
VITE_PUSHER_APP_KEY=app-key
VITE_PUSHER_HOST=localhost
VITE_PUSHER_PORT=6001
```

---

## Web3 / Ethereum Integration

The platform includes a complete Web3/Ethereum integration via a dedicated Go microservice. This architecture separates blockchain concerns from the main application while providing secure wallet management and transaction capabilities.

### Architecture

```
┌──────────────────────────────────────────────────────────────────────────┐
│                         Laravel Application                               │
│  ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐      │
│  │  Web3Service    │    │  CreateWallet   │    │  SendTransaction│      │
│  │  (HTTP Client)  │    │  Action         │    │  Action         │      │
│  └────────┬────────┘    └────────┬────────┘    └────────┬────────┘      │
│           │                      │                      │                │
│           └──────────────────────┴──────────────────────┘                │
│                                  │ HTTP (Internal Network)               │
└──────────────────────────────────┼───────────────────────────────────────┘
                                   │
┌──────────────────────────────────▼───────────────────────────────────────┐
│                         Go Web3 Service (Gin)                             │
│  ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐      │
│  │  WalletHandler  │    │  WalletService  │    │  KeyStore       │      │
│  │  (HTTP Layer)   │───►│  (Business)     │───►│  (go-ethereum)  │      │
│  └─────────────────┘    └─────────────────┘    └─────────────────┘      │
│           │                      │                      │                │
│           └──────────────────────┴──────────────────────┘                │
│                                  │ JSON-RPC                              │
└──────────────────────────────────┼───────────────────────────────────────┘
                                   │
┌──────────────────────────────────▼───────────────────────────────────────┐
│                    Ganache (Local Ethereum Blockchain)                    │
│  • Chain ID: 1337                                                        │
│  • Pre-funded accounts with 1000 ETH each                                │
│  • 1 second block time                                                   │
└──────────────────────────────────────────────────────────────────────────┘
```

### Go Service Features

| Feature | Description |
|---------|-------------|
| **Wallet Creation** | Generate new Ethereum wallets with encrypted keystores |
| **Balance Queries** | Check ETH balance for any address |
| **Send Transactions** | Send ETH between addresses with gas estimation |
| **Transaction Status** | Query transaction receipts and confirmation status |
| **API Key Auth** | Secure internal communication with API key middleware |

### Go Project Structure

```
web3-service/
├── cmd/
│   └── api/
│       └── main.go           # Entry point, dependency injection
├── internal/
│   ├── config/
│   │   └── config.go         # Environment configuration
│   ├── domain/
│   │   ├── wallet.go         # Wallet DTOs
│   │   └── transaction.go    # Transaction DTOs
│   ├── ethereum/
│   │   ├── client.go         # go-ethereum wrapper
│   │   └── keystore.go       # Key management
│   ├── handler/
│   │   ├── health.go         # Health check endpoint
│   │   ├── wallet.go         # Wallet HTTP handlers
│   │   └── transaction.go    # Transaction HTTP handlers
│   ├── middleware/
│   │   └── auth.go           # API key authentication
│   └── service/
│       ├── wallet_service.go # Wallet business logic
│       └── transaction_service.go
└── docker/
    └── Dockerfile            # Multi-stage Go build
```

### API Endpoints (Go Service)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/health` | Service health + Ethereum connectivity |
| `POST` | `/api/v1/wallets` | Create new wallet |
| `GET` | `/api/v1/wallets/:address/balance` | Get ETH balance |
| `POST` | `/api/v1/transactions` | Send ETH transaction |
| `GET` | `/api/v1/transactions/:hash` | Get transaction status |

### Laravel Integration

The Laravel application communicates with the Go service via HTTP:

```php
// Create a wallet
$action = app(CreateWalletAction::class);
$wallet = $action->handle($user, 'secure-password');

// Check balance
$action = app(GetWalletBalanceAction::class);
$balance = $action->handle($walletAddress);

// Send transaction
$action = app(SendTransactionAction::class);
$tx = $action->handle($user, $fromAddress, $toAddress, '0.1', 'password');
```

### Vue Components

| Component | Description |
|-----------|-------------|
| `WalletCard` | Create wallets, view addresses and balances |
| `SendTransactionForm` | Form to send ETH between addresses |
| `TransactionHistory` | List of user's blockchain transactions |

### Running the Web3 Stack

The Web3 services start automatically with Docker Compose:

```bash
make up  # Starts all services including Ganache and web3-service
```

To test the Go service directly:

```bash
# Health check
curl http://localhost:8081/health

# Create wallet (requires API key)
curl -X POST http://localhost:8081/api/v1/wallets \
  -H "X-API-Key: web3-secret-key" \
  -H "Content-Type: application/json" \
  -d '{"password": "my-secure-password"}'

# Check balance
curl http://localhost:8081/api/v1/wallets/0x.../balance \
  -H "X-API-Key: web3-secret-key"
```

---

## Notification System

The platform includes a comprehensive notification system that ensures users never miss important trading events, even when offline.

### How It Works

1. **Order Matched**: When orders are matched, the matching engine triggers a notification
2. **Real-time Delivery**: If the user is online, they receive an instant WebSocket notification
3. **Database Persistence**: Notifications are also stored in the database for offline users
4. **Login Retrieval**: When users log in, any unread notifications are displayed as toast messages

### Notification Flow

```
Order Matched
     │
     ▼
┌────────────────┐
│ MatchOrders    │
│ Action         │
└───────┬────────┘
        │
        ▼
┌────────────────┐     ┌────────────────┐
│ OrderMatched   │────►│ Notification   │
│ Event          │     │ (Database)     │
└───────┬────────┘     └────────────────┘
        │
        ▼
┌────────────────┐
│ Private        │
│ WebSocket      │
│ Channel        │
└───────┬────────┘
        │
        ▼
   User's Browser
   (Toast Message)
```

### Key Components

| Component | Purpose |
|-----------|---------|
| `OrderFilledNotification` | Laravel notification class with database channel |
| `OrderMatched` event | Broadcasts to private user channels |
| `NotificationBell` component | Shows unread count, lists notifications |
| `showPendingNotifications()` | Displays missed notifications on login |

### Notification Channels

- **Database**: Persistent storage for offline retrieval
- **WebSocket**: Real-time delivery via private channels (`private-user.{id}`)

### User Experience

**Online Users:**
- Instant toast notification when their order is filled
- Badge updates on notification bell
- Click to view details

**Offline Users:**
- Notifications stored in database
- On login: summary toast if >3 notifications, individual toasts if ≤3
- All notifications accessible via bell icon

### API Endpoints

```http
# Get unread notifications
GET /api/notifications
Authorization: Bearer {token}

# Mark notification as read
POST /api/notifications/{id}/read
Authorization: Bearer {token}

# Mark all as read
POST /api/notifications/read-all
Authorization: Bearer {token}
```

---

## Design Patterns & Principles

### 1. Action Pattern (Single Action Classes)

Instead of traditional controllers with multiple methods, we use single-purpose Action classes:

```php
// app/Actions/Order/CreateOrderAction.php
final class CreateOrderAction
{
    use AsAction;  // From lorisleiva/laravel-actions

    public function handle(User $user, array $data): Order
    {
        // Business logic here
    }

    public function asController(CreateOrderRequest $request): JsonResponse
    {
        // HTTP layer adapter
    }
}
```

**Benefits:**
- Single Responsibility Principle
- Easily testable in isolation
- Can be called from controllers, jobs, or console commands
- Self-documenting code structure

### 2. Repository Pattern with Cache Decorator

Data access is abstracted behind interfaces with automatic caching:

```php
// Contract
interface OrderRepositoryInterface {
    public function findById(int $id): ?Order;
}

// Eloquent Implementation
class EloquentOrderRepository implements OrderRepositoryInterface {
    public function findById(int $id): ?Order {
        return Order::find($id);
    }
}

// Cache Decorator
class CachedOrderRepository implements OrderRepositoryInterface {
    public function findById(int $id): ?Order {
        return Cache::tags(['orders'])->remember(
            "order:{$id}",
            now()->addMinutes(5),
            fn() => $this->repository->findById($id)
        );
    }
}
```

**Benefits:**
- Database queries are automatically cached
- Easy to switch implementations (e.g., for testing)
- Cache invalidation is centralized

### 3. HATEOAS & JSON:API

API responses follow JSON:API specification with hypermedia links:

```json
{
  "data": {
    "type": "orders",
    "id": "1",
    "attributes": {
      "symbol": "BTC",
      "side": "buy",
      "price": "42500.00",
      "amount": "0.5"
    },
    "links": {
      "self": "/api/orders/1",
      "cancel": "/api/orders/1/cancel"
    }
  }
}
```

**Benefits:**
- Self-documenting API
- Clients can navigate without hardcoded URLs
- Standardized format across endpoints

### 4. High-Precision Decimal Arithmetic

Financial calculations use a custom Decimal class to avoid floating-point errors:

```php
use App\Support\Decimal;

$price = Decimal::from('42500.00');
$amount = Decimal::from('0.5');
$fee = Decimal::from('0.015');

$total = $price->mul($amount);           // 21250.00
$withFee = $total->mul($fee->add('1'));  // 21568.75
```

**Why not floats?**
```php
// Floating point: 0.1 + 0.2 = 0.30000000000000004
// Decimal: 0.1 + 0.2 = 0.3 (exact)
```

### 5. Type Safety Throughout

- **Backend**: PHP 8.5 strict types, PHPStan level 8
- **Frontend**: TypeScript strict mode, no `any` types
- **API**: Form Request validation with typed rules

### 6. Test Pyramid

```
          ┌─────────┐
         /  Feature  \        5% - Full HTTP tests
        /   Tests     \
       ├───────────────┤
      /   Integration   \     15% - Database tests
     /      Tests        \
    ├─────────────────────┤
   /      Unit Tests       \  80% - Fast, isolated
  └─────────────────────────┘
```

---

## Frontend Components

### UI Component Library (shadcn-vue style)

| Component | Description |
|-----------|-------------|
| `Button` | Primary, secondary, destructive, outline, ghost variants |
| `Card` | Container with header, content, footer sections |
| `Input` | Text input with validation states |
| `Select` | Dropdown selection |
| `Label` | Form labels |
| `Badge` | Status indicators |
| `Tooltip` | Hover information |
| `Toaster` | Toast notifications (vue-sonner) |
| `ThemeToggle` | Dark/light mode switch |

### Trading Components

| Component | Purpose |
|-----------|---------|
| `LoginForm` | Two-column login with demo user quick-select |
| `OrderForm` | Place buy/sell orders with real-time calculation |
| `Orderbook` | Live bid/ask depth display |
| `MyOrders` | User's open/filled/cancelled orders |
| `RecentTrades` | Latest executed trades |
| `ExchangeRates` | Live BTC/ETH prices from CoinGecko |
| `UserCard` | Display user name and USD balance |
| `AssetList` | Portfolio holdings (BTC, ETH amounts) |
| `NotificationBell` | Notification center with unread count badge |

### Web3 Components

| Component | Purpose |
|-----------|---------|
| `WalletCard` | Create Ethereum wallets and view balances |
| `SendTransactionForm` | Send ETH to other addresses |
| `TransactionHistory` | View blockchain transaction history |

---

## API Reference

### Authentication Endpoints

#### Register
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "trader1@example.com",
  "password": "password"
}
```

Response:
```json
{
  "data": { "type": "users", "id": "1", ... },
  "token": "1|abc123..."
}
```

#### Logout
```http
POST /api/auth/logout
Authorization: Bearer {token}
```

### Trading Endpoints

#### Get Orderbook
```http
GET /api/orders?symbol=BTC
Authorization: Bearer {token}
```

#### Place Order
```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "symbol": "BTC",
  "side": "buy",
  "price": "42500.00",
  "amount": "0.5"
}
```

#### Cancel Order
```http
POST /api/orders/{id}/cancel
Authorization: Bearer {token}
```

#### Get My Orders
```http
GET /api/my-orders
Authorization: Bearer {token}
```

#### Get Recent Trades
```http
GET /api/trades?symbol=BTC
Authorization: Bearer {token}
```

### Market Data

#### Get Exchange Rates
```http
GET /api/market/rates
Authorization: Bearer {token}
```

Response:
```json
{
  "BTC": 97234.00,
  "ETH": 3456.78,
  "source": "coingecko",
  "cached_at": "2025-01-01T12:00:00Z"
}
```

### Web3 Endpoints

#### Create Wallet
```http
POST /api/web3/wallets
Authorization: Bearer {token}
Content-Type: application/json

{
  "password": "secure-wallet-password"
}
```

#### List User Wallets
```http
GET /api/web3/wallets
Authorization: Bearer {token}
```

#### Get Wallet Balance
```http
GET /api/web3/wallets/{address}/balance
Authorization: Bearer {token}
```

#### Send Transaction
```http
POST /api/web3/transactions
Authorization: Bearer {token}
Content-Type: application/json

{
  "from_address": "0x...",
  "to_address": "0x...",
  "amount": "0.1",
  "password": "wallet-password"
}
```

#### Get Transaction Status
```http
GET /api/web3/transactions/{hash}
Authorization: Bearer {token}
```

#### List User Transactions
```http
GET /api/web3/transactions
Authorization: Bearer {token}
```

### Notification Endpoints

#### Get Notifications
```http
GET /api/notifications
Authorization: Bearer {token}
```

#### Mark as Read
```http
POST /api/notifications/{id}/read
Authorization: Bearer {token}
```

#### Mark All as Read
```http
POST /api/notifications/read-all
Authorization: Bearer {token}
```

---

## Development Guide

### Make Commands Reference

```bash
# === Docker Operations ===
make build           # Build Docker images
make up              # Start all containers
make down            # Stop all containers
make restart         # Restart all containers
make logs            # Tail all container logs
make logs-app        # Tail app container logs only

# === Laravel Commands ===
make shell           # Open bash in app container
make artisan cmd='...' # Run any artisan command
make migrate         # Run database migrations
make seed            # Run database seeders
make fresh           # Reset DB and re-seed (destructive!)
make key-generate    # Generate application key

# === Cache Management ===
make cache-clear     # Clear all caches + reload Octane
make cache-views     # Clear compiled views only
make octane-reload   # Reload Octane workers (graceful)

# === Testing ===
make test            # Run all tests
make test-unit       # Run unit tests only
make test-int        # Run integration tests
make test-feature    # Run feature tests
make test-coverage   # Run with coverage report

# === Code Quality ===
make lint            # Run PHPStan static analysis
make format          # Run Laravel Pint formatter

# === Frontend ===
make npm cmd='...'   # Run any npm command
make npm-dev         # Start Vite dev server (HMR)
make npm-build       # Production build + cache clear

# === Database ===
make db-shell        # Open PostgreSQL psql shell
make redis-cli       # Open Redis CLI
```

### Development Workflow

1. **Start the environment:**
   ```bash
   make up
   ```

2. **Start frontend dev server (with HMR):**
   ```bash
   make npm-dev
   ```

3. **Make changes to PHP code:**
   - Changes are picked up automatically (Octane watches files)
   - If you modify config files, run `make octane-reload`

4. **Make changes to frontend code:**
   - Vite HMR updates the browser instantly

5. **Run tests before committing:**
   ```bash
   make test && make lint
   ```

6. **Build for production:**
   ```bash
   make npm-build
   ```

---

## Testing Strategy

### Unit Tests (80%)

Fast, isolated tests for business logic:

```php
// tests/Unit/Support/DecimalTest.php
public function test_addition(): void
{
    $a = Decimal::from('0.1');
    $b = Decimal::from('0.2');

    $this->assertEquals('0.30000000', $a->add($b)->toString());
}
```

### Integration Tests (15%)

Tests involving database:

```php
// tests/Integration/Repositories/OrderRepositoryTest.php
public function test_find_by_id_returns_order(): void
{
    $order = Order::factory()->create();

    $found = $this->repository->findById($order->id);

    $this->assertEquals($order->id, $found->id);
}
```

### Feature Tests (5%)

Full HTTP request/response cycle:

```php
// tests/Feature/Api/OrderTest.php
public function test_can_create_buy_order(): void
{
    $user = User::factory()->create(['balance' => 100000]);

    $response = $this->actingAs($user)->postJson('/api/orders', [
        'symbol' => 'BTC',
        'side' => 'buy',
        'price' => '42500',
        'amount' => '0.5',
    ]);

    $response->assertStatus(201);
}
```

### Running Tests

```bash
# All tests
make test

# Specific suite
make test-unit
make test-int
make test-feature

# With coverage report
make test-coverage
```

---

## FAQ

### General Questions

**Q: Is this production-ready?**

A: This is an educational project demonstrating best practices. For production use, you would need to add: rate limiting, audit logging, KYC/AML compliance, proper secrets management, monitoring/alerting, and security audits.

**Q: Why not use a real exchange API?**

A: This project demonstrates how exchange internals work. Understanding the matching engine, orderbook management, and settlement helps developers build better trading applications.

**Q: Can I add more cryptocurrencies?**

A: Yes! Add the symbol to the `Symbol` type in `resources/js/types/index.ts`, update the `OrderRequest` validation rules, and add it to the Select options in `OrderForm.vue`.

### Technical Questions

**Q: Why FrankenPHP instead of Nginx + PHP-FPM?**

A: FrankenPHP with Octane keeps the application in memory between requests, resulting in 10x faster response times. It also simplifies the Docker setup to a single container.

**Q: How does the order matching work?**

A: The matching engine uses price-time priority:
1. Best price first (lowest ask matches highest bid)
2. If prices are equal, earlier order has priority
3. Partial fills are supported (large orders can match multiple smaller ones)

**Q: Why Redis for sessions instead of database?**

A: Redis is faster and reduces database load. For a trading platform where milliseconds matter, Redis provides sub-millisecond session lookups.

**Q: How are WebSocket connections authenticated?**

A: Laravel Echo sends the Sanctum token with each WebSocket connection. Soketi validates this token via HTTP callback to the Laravel app's `/broadcasting/auth` endpoint.

### Troubleshooting

**Q: I get "Pusher not configured" in the console**

A: Make sure:
1. Soketi container is running: `docker compose ps`
2. `.env` has correct `VITE_PUSHER_*` variables
3. Frontend was rebuilt: `make npm-build`

**Q: The page shows old styles after rebuild**

A: Octane caches the Vite manifest. Run:
```bash
make npm-build  # This clears views and reloads Octane
```

**Q: Database connection refused**

A: Wait for PostgreSQL to be healthy:
```bash
docker compose ps  # Check db shows "healthy"
```

**Q: How do I reset everything?**

A: Nuclear option:
```bash
docker compose down -v  # Removes volumes too!
make install
```

---

## Contributing

We welcome contributions! Please follow these steps:

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/amazing-feature`
3. **Write tests** for your changes
4. **Ensure all tests pass**: `make test`
5. **Run static analysis**: `make lint`
6. **Format code**: `make format`
7. **Commit** with a descriptive message
8. **Push** to your fork
9. **Open a Pull Request**

### Code Standards

- PHP: PSR-12 (enforced by Laravel Pint)
- TypeScript: ESLint + Prettier
- Commits: Conventional Commits format preferred
- Tests: Required for new features

---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

<p align="center">
  Built with passion using Laravel, Vue.js, Go, and modern web technologies.
  <br>
  <a href="https://github.com/ikarolaborda/limit-order-exchange-mini-engine/issues">Report Bug</a>
  ·
  <a href="https://github.com/ikarolaborda/limit-order-exchange-mini-engine/issues">Request Feature</a>
</p>
