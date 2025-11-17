<?php
declare(strict_types=1);

namespace BlackCat\Observability\Metrics;

use BlackCat\Observability\Config\ObservabilityConfig;
use BlackCat\Observability\Storage\LocalStore;

final class Counter
{
    public function __construct(
        private readonly string $name,
        private readonly ObservabilityConfig $config,
        private readonly LocalStore $store
    ) {}

    /**
     * @param array<string,string> $labels
     */
    public function inc(array $labels = [], float $value = 1.0): void
    {
        $this->store->appendMetric([
            'service' => $this->config->service,
            'type' => 'counter',
            'name' => $this->name,
            'value' => $value,
            'labels' => $labels,
        ]);
    }
}
