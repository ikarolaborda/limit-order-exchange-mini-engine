<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use Illuminate\Support\Facades\Auth;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class UserContextProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;

        if (Auth::check()) {
            $user = Auth::user();
            $extra['user_id'] = $user->id;
            $extra['user_email'] = $user->email;
        }

        return $record->with(extra: $extra);
    }
}
