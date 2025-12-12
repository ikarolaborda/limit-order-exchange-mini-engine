<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class UserWallet extends Model
{
    protected $fillable = [
        'user_id',
        'address',
        'label',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outgoingTransactions(): HasMany
    {
        return $this->hasMany(BlockchainTransaction::class, 'from_address', 'address');
    }

    public function incomingTransactions(): HasMany
    {
        return $this->hasMany(BlockchainTransaction::class, 'to_address', 'address');
    }
}
