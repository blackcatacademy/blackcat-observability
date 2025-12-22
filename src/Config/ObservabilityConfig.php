<?php
declare(strict_types=1);

namespace BlackCat\Observability\Config;

use InvalidArgumentException;

final class ObservabilityConfig
{
    /**
     * @param array<int,array<string,mixed>> $exporters
     */
    public function __construct(
        public readonly string $service,
        public readonly string $storageDir,
        public readonly array $exporters = [],
    ) {}

    /**
     * Build config from BlackCat runtime config (`blackcat-config`) if available.
     *
     * Runtime keys:
     * - observability.service
     * - observability.storage_dir
     *
     * If runtime config is not available, falls back to safe defaults.
     */
    public static function fromRuntimeConfig(?string $runtimeConfigJsonFile = null): self
    {
        $defaults = new self(self::defaultService(), self::defaultStorageDir(), []);

        if (!class_exists(\BlackCat\Config\Runtime\Config::class)) {
            return $defaults;
        }

        if (is_string($runtimeConfigJsonFile) && trim($runtimeConfigJsonFile) !== '') {
            \BlackCat\Config\Runtime\Config::initFromJsonFileIfNeeded(trim($runtimeConfigJsonFile));
        } else {
            \BlackCat\Config\Runtime\Config::tryInitFromFirstAvailableJsonFile();
        }

        if (!\BlackCat\Config\Runtime\Config::isInitialized()) {
            return $defaults;
        }

        $repo = \BlackCat\Config\Runtime\Config::repo();

        $serviceRaw = $repo->get('observability.service', $defaults->service);
        $storageRaw = $repo->get('observability.storage_dir', $defaults->storageDir);

        $service = self::nonEmptyStringOrDefault($serviceRaw, $defaults->service);
        $storageDir = self::nonEmptyStringOrDefault($storageRaw, $defaults->storageDir);

        return new self($service, $storageDir, $defaults->exporters);
    }

    /**
     * Legacy loader.
     *
     * This is kept for backward compatibility but should not be relied upon in
     * environments where `getenv()` may be blocked.
     */
    public static function fromEnv(): self
    {
        $configFile = self::safeGetenv('BLACKCAT_OBS_CONFIG');
        if (is_string($configFile) && $configFile !== '' && is_file($configFile)) {
            return self::fromFile($configFile);
        }

        $service = self::safeGetenv('OBS_SERVICE') ?: self::defaultService();
        $storage = self::safeGetenv('OBS_STORAGE') ?: self::defaultStorageDir();

        return new self($service, $storage, []);
    }

    public static function fromFile(string $path): self
    {
        if (!is_file($path)) {
            throw new InvalidArgumentException("Observability config not found: {$path}");
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $payload = match ($ext) {
            'php' => require $path,
            'json' => json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR),
            'yml', 'yaml' => self::parseYaml($path),
            default => throw new InvalidArgumentException("Unsupported config format: {$ext}"),
        };

        if (!is_array($payload)) {
            throw new InvalidArgumentException("Observability config must return array: {$path}");
        }

        $payload = self::resolvePlaceholders($payload);

        return new self(
            $payload['service'] ?? 'blackcat-app',
            $payload['storage_dir'] ?? __DIR__ . '/../../var',
            $payload['exporters'] ?? []
        );
    }

    /**
     * @return array<string,mixed>
     */
    private static function parseYaml(string $path): array
    {
        if (!function_exists('yaml_parse_file')) {
            throw new InvalidArgumentException('ext-yaml required to parse observability config');
        }

        $parsed = yaml_parse_file($path);
        if (!is_array($parsed)) {
            throw new InvalidArgumentException("Invalid YAML config {$path}");
        }

        return $parsed;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function resolvePlaceholders(mixed $value): mixed
    {
        if (is_string($value)) {
            if (preg_match('/^\$\{env:([^}]+)}/', $value, $m)) {
                return self::safeGetenv($m[1]) ?: '';
            }
            if (preg_match('/^\$\{file:([^}]+)}/', $value, $m)) {
                return is_file($m[1]) ? trim((string) file_get_contents($m[1])) : '';
            }
            return $value;
        }

        if (is_array($value)) {
            $resolved = [];
            foreach ($value as $key => $inner) {
                $resolved[$key] = self::resolvePlaceholders($inner);
            }
            return $resolved;
        }

        return $value;
    }

    private static function defaultService(): string
    {
        return 'blackcat-app';
    }

    private static function defaultStorageDir(): string
    {
        return __DIR__ . '/../../var';
    }

    private static function nonEmptyStringOrDefault(mixed $value, string $default): string
    {
        if (!is_string($value)) {
            return $default;
        }
        $value = trim($value);
        return $value !== '' ? $value : $default;
    }

    private static function safeGetenv(string $key): string|false
    {
        if (!function_exists('getenv')) {
            return false;
        }
        /** @var string|false $value */
        $value = @getenv($key);
        return $value;
    }
}
