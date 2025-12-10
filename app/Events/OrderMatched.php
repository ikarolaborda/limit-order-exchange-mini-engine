<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Trade;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

final class OrderMatched implements ShouldBroadcast
{
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly Trade $trade,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('private-user.'.$this->trade->buyOrder->user_id),
            new PrivateChannel('private-user.'.$this->trade->sellOrder->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderMatched';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'symbol' => $this->trade->symbol,
            'price' => $this->trade->price,
            'amount' => $this->trade->amount,
            'fee' => $this->trade->fee,
            'buy_order_id' => $this->trade->buy_order_id,
            'sell_order_id' => $this->trade->sell_order_id,
        ];
    }
}
