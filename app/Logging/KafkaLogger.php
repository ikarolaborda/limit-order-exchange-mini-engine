<?php

declare(strict_types=1);

namespace App\Logging;

use App\Logging\Processors\RequestIdProcessor;
use App\Logging\Processors\UserContextProcessor;
use Monolog\Logger;

final class KafkaLogger
{
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('kafka');

        $handler = new KafkaLogHandler(
            topic: $config['topic'] ?? 'logs.laravel',
            serviceName: $config['service_name'] ?? 'laravel-exchange',
            level: $config['level'] ?? 'debug',
        );

        $logger->pushHandler($handler);
        $logger->pushProcessor(new UserContextProcessor());
        $logger->pushProcessor(new RequestIdProcessor());

        return $logger;
    }
}
