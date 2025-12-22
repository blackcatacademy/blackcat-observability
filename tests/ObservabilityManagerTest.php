<?php

declare(strict_types=1);

namespace BlackCat\Observability\Tests;

use BlackCat\Observability\Config\ObservabilityConfig;
use BlackCat\Observability\ObservabilityManager;
use BlackCat\Observability\Storage\LocalStore;
use PHPUnit\Framework\TestCase;

final class ObservabilityManagerTest extends TestCase
{
    public function testPublishesEventsAndMetricsToLocalStore(): void
    {
        $dir = sys_get_temp_dir() . '/bc-obs-' . bin2hex(random_bytes(6));
        $config = new ObservabilityConfig('qa-test', $dir);
        $obs = ObservabilityManager::boot($config);

        $obs->events()->publish('auth.login', ['result' => 'success']);
        $obs->metrics()->counter('auth_logins_total')->inc(['result' => 'success']);

        $store = new LocalStore($dir);

        $events = $store->events();
        self::assertCount(1, $events);
        self::assertSame('qa-test', $events[0]['service'] ?? null);
        self::assertSame('auth.login', $events[0]['event'] ?? null);

        $metrics = $store->metrics();
        self::assertCount(1, $metrics);
        self::assertSame('qa-test', $metrics[0]['service'] ?? null);
        self::assertSame('counter', $metrics[0]['type'] ?? null);
        self::assertSame('auth_logins_total', $metrics[0]['name'] ?? null);
    }
}

