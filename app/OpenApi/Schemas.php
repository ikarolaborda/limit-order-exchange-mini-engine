<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

// ============================================================================
// Request Schemas
// ============================================================================

#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'trader1@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
    ]
)]

#[OA\Schema(
    schema: 'RegisterRequest',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'password123'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
    ]
)]

#[OA\Schema(
    schema: 'CreateOrderRequest',
    required: ['symbol', 'side', 'price', 'amount'],
    properties: [
        new OA\Property(property: 'symbol', type: 'string', enum: ['BTC', 'ETH'], example: 'BTC'),
        new OA\Property(property: 'side', type: 'string', enum: ['buy', 'sell'], example: 'buy'),
        new OA\Property(property: 'price', type: 'string', description: 'Price in USD', example: '50000.00'),
        new OA\Property(property: 'amount', type: 'string', description: 'Amount of cryptocurrency', example: '0.5'),
    ]
)]

// ============================================================================
// Response Schemas - User
// ============================================================================

#[OA\Schema(
    schema: 'UserResource',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'users'),
        new OA\Property(property: 'id', type: 'string', example: '1'),
        new OA\Property(
            property: 'attributes',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Alice Trader'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'trader1@example.com'),
                new OA\Property(property: 'balance', type: 'string', description: 'USD balance', example: '100000.00'),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'links',
            properties: [
                new OA\Property(property: 'self', type: 'string', example: '/api/profile'),
            ],
            type: 'object'
        ),
    ]
)]

#[OA\Schema(
    schema: 'ProfileResource',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'users'),
        new OA\Property(property: 'id', type: 'string', example: '1'),
        new OA\Property(
            property: 'attributes',
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Alice Trader'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'trader1@example.com'),
                new OA\Property(property: 'balance', type: 'string', description: 'USD balance', example: '100000.00'),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'relationships',
            properties: [
                new OA\Property(
                    property: 'assets',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/AssetResource')
                ),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'links',
            properties: [
                new OA\Property(property: 'self', type: 'string', example: '/api/profile'),
                new OA\Property(property: 'orders', type: 'string', example: '/api/my-orders'),
            ],
            type: 'object'
        ),
    ]
)]

// ============================================================================
// Response Schemas - Asset
// ============================================================================

#[OA\Schema(
    schema: 'AssetResource',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'assets'),
        new OA\Property(property: 'id', type: 'string', example: '1'),
        new OA\Property(
            property: 'attributes',
            properties: [
                new OA\Property(property: 'symbol', type: 'string', enum: ['BTC', 'ETH'], example: 'BTC'),
                new OA\Property(property: 'amount', type: 'string', description: 'Available amount', example: '2.50000000'),
                new OA\Property(property: 'locked_amount', type: 'string', description: 'Amount locked in orders', example: '0.50000000'),
            ],
            type: 'object'
        ),
    ]
)]

// ============================================================================
// Response Schemas - Order
// ============================================================================

#[OA\Schema(
    schema: 'OrderResource',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'orders'),
        new OA\Property(property: 'id', type: 'string', example: '1'),
        new OA\Property(
            property: 'attributes',
            properties: [
                new OA\Property(property: 'symbol', type: 'string', enum: ['BTC', 'ETH'], example: 'BTC'),
                new OA\Property(property: 'side', type: 'string', enum: ['buy', 'sell'], example: 'buy'),
                new OA\Property(property: 'price', type: 'string', example: '50000.00'),
                new OA\Property(property: 'amount', type: 'string', example: '0.50000000'),
                new OA\Property(property: 'locked_usd', type: 'string', description: 'USD locked for buy orders', example: '25375.00'),
                new OA\Property(property: 'status', type: 'integer', description: '1=open, 2=filled, 3=cancelled', example: 1),
                new OA\Property(property: 'status_label', type: 'string', enum: ['open', 'filled', 'cancelled'], example: 'open'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-01-01T12:00:00.000000Z'),
                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-01-01T12:00:00.000000Z'),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'relationships',
            properties: [
                new OA\Property(
                    property: 'user',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'type', type: 'string', example: 'users'),
                                new OA\Property(property: 'id', type: 'string', example: '1'),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                ),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'links',
            properties: [
                new OA\Property(property: 'self', type: 'string', example: '/api/orders/1'),
                new OA\Property(property: 'cancel', type: 'string', example: '/api/orders/1/cancel'),
            ],
            type: 'object'
        ),
    ]
)]

#[OA\Schema(
    schema: 'OrderCollection',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/OrderResource')
        ),
        new OA\Property(
            property: 'meta',
            properties: [
                new OA\Property(property: 'total', type: 'integer', example: 10),
            ],
            type: 'object'
        ),
    ]
)]

// ============================================================================
// Response Schemas - Trade
// ============================================================================

#[OA\Schema(
    schema: 'TradeResource',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'trades'),
        new OA\Property(property: 'id', type: 'string', example: '1'),
        new OA\Property(
            property: 'attributes',
            properties: [
                new OA\Property(property: 'symbol', type: 'string', enum: ['BTC', 'ETH'], example: 'BTC'),
                new OA\Property(property: 'price', type: 'string', example: '50000.00'),
                new OA\Property(property: 'amount', type: 'string', example: '0.50000000'),
                new OA\Property(property: 'fee', type: 'string', description: 'Trading fee paid by buyer', example: '375.00'),
                new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-01-01T12:00:00.000000Z'),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'relationships',
            properties: [
                new OA\Property(
                    property: 'buy_order',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'type', type: 'string', example: 'orders'),
                                new OA\Property(property: 'id', type: 'string', example: '1'),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                ),
                new OA\Property(
                    property: 'sell_order',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'type', type: 'string', example: 'orders'),
                                new OA\Property(property: 'id', type: 'string', example: '2'),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                ),
            ],
            type: 'object'
        ),
    ]
)]

#[OA\Schema(
    schema: 'TradeCollection',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/TradeResource')
        ),
    ]
)]

// ============================================================================
// Response Schemas - Market
// ============================================================================

#[OA\Schema(
    schema: 'ExchangeRates',
    properties: [
        new OA\Property(property: 'BTC', type: 'number', format: 'float', example: 97234.56),
        new OA\Property(property: 'ETH', type: 'number', format: 'float', example: 3456.78),
        new OA\Property(property: 'source', type: 'string', enum: ['coingecko', 'fallback'], example: 'coingecko'),
        new OA\Property(property: 'cached_at', type: 'string', format: 'date-time', nullable: true, example: '2025-01-01T12:00:00.000000Z'),
    ]
)]

// ============================================================================
// Response Schemas - Auth
// ============================================================================

#[OA\Schema(
    schema: 'LoginResponse',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'token', type: 'string', description: 'Bearer token for authentication', example: '1|abc123xyz...'),
    ]
)]

#[OA\Schema(
    schema: 'RegisterResponse',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'token', type: 'string', description: 'Bearer token for authentication', example: '1|abc123xyz...'),
    ]
)]

#[OA\Schema(
    schema: 'LogoutResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully'),
        new OA\Property(property: 'tokens_revoked', type: 'integer', description: 'Number of tokens revoked', example: 1),
    ]
)]

#[OA\Schema(
    schema: 'CreateOrderResponse',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/OrderResource'),
        new OA\Property(
            property: 'trade',
            ref: '#/components/schemas/TradeResource',
            nullable: true,
            description: 'Trade executed if order was immediately matched'
        ),
    ]
)]

// ============================================================================
// Error Response Schemas
// ============================================================================

#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            ),
            example: ['email' => ['The email field is required.']]
        ),
    ]
)]

#[OA\Schema(
    schema: 'UnauthorizedError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
    ]
)]

#[OA\Schema(
    schema: 'NotFoundError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Resource not found.'),
    ]
)]

#[OA\Schema(
    schema: 'ServerError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Server Error'),
    ]
)]

final class Schemas {}
