<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Trade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

final class OrderFilledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Trade $trade,
        public readonly string $side,
    ) {
        $this->afterCommit();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function databaseType(object $notifiable): string
    {
        return 'order-filled';
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $total = bcmul($this->trade->price, $this->trade->amount, 8);

        return [
            'trade_id' => $this->trade->id,
            'symbol' => $this->trade->symbol,
            'price' => $this->trade->price,
            'amount' => $this->trade->amount,
            'total' => $total,
            'side' => $this->side,
        ];
    }
}
