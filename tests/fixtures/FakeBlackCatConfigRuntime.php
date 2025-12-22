<?php

declare(strict_types=1);

namespace BlackCat\Config\Runtime;

final class Config
{
    private static ?ConfigRepository $repo = null;

    public static function isInitialized(): bool
    {
        return self::$repo !== null;
    }

    public static function initFromJsonFileIfNeeded(string $path): void
    {
        if (self::$repo !== null) {
            return;
        }
        self::$repo = ConfigRepository::fromJsonFile($path);
    }

    public static function tryInitFromFirstAvailableJsonFile(): bool
    {
        return self::$repo !== null;
    }

    public static function repo(): ConfigRepository
    {
        if (self::$repo === null) {
            throw new \RuntimeException('Config is not initialized.');
        }
        return self::$repo;
    }
}

final class ConfigRepository
{
    /**
     * @param array<string,mixed> $data
     */
    private function __construct(private readonly array $data)
    {
    }

    public static function fromJsonFile(string $path): self
    {
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new \RuntimeException('Unable to read config file: ' . $path);
        }

        /** @var mixed $decoded */
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid config JSON: ' . $path);
        }

        /** @var array<string,mixed> $decoded */
        return new self($decoded);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return $default;
        }

        $cur = $this->data;
        foreach (explode('.', $key) as $segment) {
            if ($segment === '') {
                return $default;
            }
            if (!is_array($cur) || !array_key_exists($segment, $cur)) {
                return $default;
            }
            $cur = $cur[$segment];
        }

        return $cur;
    }
}

