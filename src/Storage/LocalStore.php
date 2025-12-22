<?php
declare(strict_types=1);

namespace BlackCat\Observability\Storage;

final class LocalStore
{
    private string $eventsFile;
    private string $metricsFile;

    private string $storageDir;

    public function __construct(string $storageDir)
    {
        if (!is_dir($storageDir) && !@mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
            throw new \RuntimeException("Unable to create storage dir {$storageDir}");
        }
        $this->storageDir = rtrim($storageDir, DIRECTORY_SEPARATOR);
        $this->eventsFile = rtrim($storageDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'events.ndjson';
        $this->metricsFile = rtrim($storageDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'metrics.ndjson';
    }

    public function storageDir(): string
    {
        return $this->storageDir;
    }

    public function eventsFilePath(): string
    {
        return $this->eventsFile;
    }

    public function metricsFilePath(): string
    {
        return $this->metricsFile;
    }

    /**
     * @param array<string,mixed> $event
     */
    public function appendEvent(array $event): void
    {
        $event['timestamp'] = time();
        file_put_contents($this->eventsFile, json_encode($event) . PHP_EOL, FILE_APPEND);
    }

    /**
     * @param array<string,mixed> $metric
     */
    public function appendMetric(array $metric): void
    {
        $metric['timestamp'] = time();
        file_put_contents($this->metricsFile, json_encode($metric) . PHP_EOL, FILE_APPEND);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function events(): array
    {
        return $this->readFile($this->eventsFile);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function metrics(): array
    {
        return $this->readFile($this->metricsFile);
    }

    public function clearEvents(): void
    {
        $this->truncate($this->eventsFile);
    }

    public function clearMetrics(): void
    {
        $this->truncate($this->metricsFile);
    }

    private function truncate(string $file): void
    {
        if (!is_file($file)) {
            return;
        }
        @file_put_contents($file, '');
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function readFile(string $file): array
    {
        if (!is_file($file)) {
            return [];
        }

        $entries = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return [];
        }

        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $entries[] = $decoded;
            }
        }
        return $entries;
    }
}
