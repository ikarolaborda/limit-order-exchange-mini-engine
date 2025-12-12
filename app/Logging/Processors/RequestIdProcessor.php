<?php

declare(strict_types=1);

namespace App\Logging\Processors;

use Illuminate\Support\Str;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class RequestIdProcessor implements ProcessorInterface
{
    private static ?string $requestId = null;

    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;
        $extra['request_id'] = $this->getRequestId();

        if (request()) {
            $extra['ip_address'] = request()->ip();
            $extra['user_agent'] = request()->userAgent();
            $extra['url'] = request()->fullUrl();
            $extra['method'] = request()->method();
        }

        return $record->with(extra: $extra);
    }

    private function getRequestId(): string
    {
        if (self::$requestId === null) {
            self::$requestId = request()?->header('X-Request-ID') ?? Str::uuid()->toString();
        }

        return self::$requestId;
    }

    public static function resetRequestId(): void
    {
        self::$requestId = null;
    }
}
