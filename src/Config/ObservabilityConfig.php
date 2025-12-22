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

    public static function fromEnv(): self
    {
        $configFile = getenv('BLACKCAT_OBS_CONFIG');
        if ($configFile && is_file($configFile)) {
            return self::fromFile($configFile);
        }

        $service = getenv('OBS_SERVICE') ?: 'blackcat-app';
        $storage = getenv('OBS_STORAGE') ?: __DIR__ . '/../../var';

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
                return getenv($m[1]) ?: '';
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
}
