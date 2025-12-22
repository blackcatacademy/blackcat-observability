<?php
declare(strict_types=1);

namespace BlackCat\Observability;

use BlackCat\Observability\Config\ObservabilityConfig;
use BlackCat\Observability\Events\EventBus;
use BlackCat\Observability\Metrics\MetricsRegistry;
use BlackCat\Observability\Storage\LocalStore;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ObservabilityManager
{
    private LocalStore $store;
    private EventBus $events;
    private MetricsRegistry $metrics;

    private function __construct(
        ObservabilityConfig $config,
        LoggerInterface $logger
    ) {
        $this->store = new LocalStore($config->storageDir);
        $this->events = new EventBus($config, $this->store, $logger);
        $this->metrics = new MetricsRegistry($config, $this->store);
    }

    public static function boot(
        ?ObservabilityConfig $config = null,
        ?LoggerInterface $logger = null
    ): self {
        return new self(
            $config ?? ObservabilityConfig::fromEnv(),
            $logger ?? new NullLogger()
        );
    }

    public function events(): EventBus
    {
        return $this->events;
    }

    public function metrics(): MetricsRegistry
    {
        return $this->metrics;
    }
}
