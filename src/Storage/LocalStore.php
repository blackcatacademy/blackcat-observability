<?php
declare(strict_types=1);

namespace BlackCat\Observability\Storage;

final class LocalStore
{
    private string $eventsFile;
    private string $metricsFile;

    public function __construct(string $storageDir)
    {
        if (!is_dir($storageDir) && !@mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
            throw new \RuntimeException("Unable to create storage dir {$storageDir}");
        }
        $this->eventsFile = rtrim($storageDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'events.ndjson';
        $this->metricsFile = rtrim($storageDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'metrics.ndjson';
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

    /**
     * @return array<int,array<string,mixed>>
     */
    private function readFile(string $file): array
    {
        if (!is_file($file)) {
            return [];
        }

        $entries = [];
        foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $entries[] = $decoded;
            }
        }
        return $entries;
    }
}
