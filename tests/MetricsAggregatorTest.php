<?php

declare(strict_types=1);

namespace BlackCat\Observability\Tests;

use BlackCat\Observability\Export\PrometheusTextExporter;
use BlackCat\Observability\Metrics\MetricsAggregator;
use PHPUnit\Framework\TestCase;

final class MetricsAggregatorTest extends TestCase
{
    public function testCountersAreSummed(): void
    {
        $metrics = [
            [
                'service' => 'svc',
                'type' => 'counter',
                'name' => 'hits_total',
                'value' => 1,
                'labels' => ['route' => '/'],
                'timestamp' => 100,
            ],
            [
                'service' => 'svc',
                'type' => 'counter',
                'name' => 'hits_total',
                'value' => 2,
                'labels' => ['route' => '/'],
                'timestamp' => 101,
            ],
        ];

        $series = MetricsAggregator::sumBySeries($metrics, true);
        self::assertCount(1, $series);
        self::assertSame('hits_total', $series[0]['name']);
        self::assertSame('counter', $series[0]['type']);
        self::assertSame(3.0, $series[0]['value']);
    }

    public function testGaugesKeepLastValueByTimestamp(): void
    {
        $metrics = [
            [
                'service' => 'svc',
                'type' => 'gauge',
                'name' => 'trust_ok',
                'value' => 0,
                'labels' => [],
                'timestamp' => 100,
            ],
            [
                'service' => 'svc',
                'type' => 'gauge',
                'name' => 'trust_ok',
                'value' => 1,
                'labels' => [],
                'timestamp' => 101,
            ],
            // Out-of-order (older ts) must not override.
            [
                'service' => 'svc',
                'type' => 'gauge',
                'name' => 'trust_ok',
                'value' => 0,
                'labels' => [],
                'timestamp' => 99,
            ],
        ];

        $series = MetricsAggregator::sumBySeries($metrics, true);
        self::assertCount(1, $series);
        self::assertSame('trust_ok', $series[0]['name']);
        self::assertSame('gauge', $series[0]['type']);
        self::assertSame(1.0, $series[0]['value']);

        $prom = PrometheusTextExporter::export($metrics, true);
        self::assertStringContainsString("# TYPE trust_ok gauge\n", $prom);
        self::assertStringContainsString("trust_ok{service=\"svc\"} 1\n", $prom);
    }
}

