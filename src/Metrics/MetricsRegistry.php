<?php
declare(strict_types=1);

namespace BlackCat\Observability\Metrics;

use BlackCat\Observability\Config\ObservabilityConfig;
use BlackCat\Observability\Storage\LocalStore;

final class MetricsRegistry
{
    public function __construct(
        private readonly ObservabilityConfig $config,
        private readonly LocalStore $store
    ) {}

    public function counter(string $name): Counter
    {
        return new Counter($name, $this->config, $this->store);
    }
}
