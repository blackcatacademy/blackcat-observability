<?php

declare(strict_types=1);

namespace BlackCat\Observability\Tests;

use BlackCat\Observability\Storage\LocalStore;
use PHPUnit\Framework\TestCase;

final class LocalStoreTest extends TestCase
{
    public function testAppendsAndReadsEventsAndMetrics(): void
    {
        $dir = sys_get_temp_dir() . '/bc-obs-' . bin2hex(random_bytes(6));
        $store = new LocalStore($dir);

        $store->appendEvent(['event' => 'qa.smoke']);
        $store->appendMetric(['name' => 'qa_metric_total', 'value' => 1]);

        $events = $store->events();
        self::assertCount(1, $events);
        self::assertSame('qa.smoke', $events[0]['event'] ?? null);
        self::assertIsInt($events[0]['timestamp'] ?? null);

        $metrics = $store->metrics();
        self::assertCount(1, $metrics);
        self::assertSame('qa_metric_total', $metrics[0]['name'] ?? null);
        self::assertSame(1, $metrics[0]['value'] ?? null);
        self::assertIsInt($metrics[0]['timestamp'] ?? null);
    }
}

