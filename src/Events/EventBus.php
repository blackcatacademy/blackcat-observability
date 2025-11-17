<?php
declare(strict_types=1);

namespace BlackCat\Observability\Events;

use BlackCat\Observability\Config\ObservabilityConfig;
use BlackCat\Observability\Storage\LocalStore;
use Psr\Log\LoggerInterface;

final class EventBus
{
    public function __construct(
        private readonly ObservabilityConfig $config,
        private readonly LocalStore $store,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @param array<string,mixed> $payload
     */
    public function publish(string $name, array $payload = []): void
    {
        $record = [
            'service' => $this->config->service,
            'event' => $name,
            'payload' => $payload,
        ];
        $this->store->appendEvent($record);
        $this->logger->info('obs.event.' . $name, $payload);
    }
}
