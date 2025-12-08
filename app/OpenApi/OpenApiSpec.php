<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Limit Order Exchange API',
    description: 'A production-ready cryptocurrency trading platform API for limit order management.

## Features
- **Order Management**: Place, cancel, and track limit orders for BTC and ETH
- **Real-time Matching**: Price-time priority matching engine
- **Portfolio Tracking**: Monitor balances and asset holdings
- **Market Data**: Live exchange rates from CoinGecko

## Authentication
All endpoints (except login/register) require Bearer token authentication using Laravel Sanctum.
After login, include the token in the Authorization header: `Bearer <token>`

## Fee Structure
- Trading fee: 1.5% on notional value (price × amount)
- Fee is charged to buyer and locked upfront when placing buy orders',
    contact: new OA\Contact(
        name: 'API Support',
        email: 'support@example.com'
    ),
    license: new OA\License(
        name: 'MIT',
        url: 'https://opensource.org/licenses/MIT'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local Development Server'
)]
#[OA\Tag(
    name: 'Authentication',
    description: 'User registration, login, and logout endpoints'
)]
#[OA\Tag(
    name: 'Orders',
    description: 'Order management - place, cancel, and view orders'
)]
#[OA\Tag(
    name: 'Profile',
    description: 'User profile and portfolio information'
)]
#[OA\Tag(
    name: 'Trades',
    description: 'Trade history and executed trades'
)]
#[OA\Tag(
    name: 'Market',
    description: 'Market data and exchange rates'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Enter your Bearer token obtained from the login endpoint',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
final class OpenApiSpec {}
