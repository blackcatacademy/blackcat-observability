<?php

declare(strict_types=1);

namespace BlackCat\Observability\Tests;

use BlackCat\Observability\Config\ObservabilityConfig;
use PHPUnit\Framework\TestCase;

final class RuntimeConfigIntegrationTest extends TestCase
{
    public function testFromRuntimeConfigFallsBackWithoutBlackcatConfig(): void
    {
        if (class_exists(\BlackCat\Config\Runtime\Config::class)) {
            self::markTestSkipped('blackcat-config is available; fallback-only test is not applicable.');
        }

        $cfg = ObservabilityConfig::fromRuntimeConfig();
        self::assertSame('blackcat-app', $cfg->service);
        self::assertStringEndsWith('/var', str_replace('\\', '/', $cfg->storageDir));
    }

    public function testFromRuntimeConfigLoadsValuesFromRuntimeJsonWhenAvailable(): void
    {
        if (class_exists(\BlackCat\Config\Runtime\Config::class)) {
            self::markTestSkipped('blackcat-config is available; fake runtime config fixture is not used.');
        }

        require_once __DIR__ . '/fixtures/FakeBlackCatConfigRuntime.php';

        $tmp = sys_get_temp_dir() . '/bc-obs-runtime-' . bin2hex(random_bytes(6)) . '.json';
        file_put_contents($tmp, json_encode([
            'observability' => [
                'service' => 'svc-test',
                'storage_dir' => '/tmp/bc-obs',
            ],
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        $cfg = ObservabilityConfig::fromRuntimeConfig($tmp);
        self::assertSame('svc-test', $cfg->service);
        self::assertSame('/tmp/bc-obs', $cfg->storageDir);
    }
}

