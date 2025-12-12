<?php

declare(strict_types=1);

namespace App\Logging;

use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

final class KafkaLogHandler extends AbstractProcessingHandler
{
    private string $topic;

    private string $serviceName;

    public function __construct(
        string $topic = 'logs.laravel',
        string $serviceName = 'laravel-exchange',
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
        $this->topic = $topic;
        $this->serviceName = $serviceName;
    }

    protected function write(LogRecord $record): void
    {
        try {
            $payload = $this->formatRecord($record);

            $message = new Message(
                body: $payload,
                key: $record->channel,
            );

            Kafka::publish(config('kafka.brokers'))
                ->onTopic($this->topic)
                ->withMessage($message)
                ->send();
        } catch (Throwable $e) {
            // Silently fail to avoid infinite loops if Kafka is down
            // Log to stderr as last resort
            error_log(sprintf(
                '[KafkaLogHandler] Failed to send log to Kafka: %s',
                $e->getMessage()
            ));
        }
    }

    private function formatRecord(LogRecord $record): array
    {
        return [
            '@timestamp' => $record->datetime->format('Y-m-d\TH:i:s.vP'),
            'level' => strtolower($record->level->name),
            'level_code' => $record->level->value,
            'service' => $this->serviceName,
            'channel' => $record->channel,
            'message' => $record->message,
            'context' => $record->context,
            'extra' => $record->extra,
        ];
    }
}
