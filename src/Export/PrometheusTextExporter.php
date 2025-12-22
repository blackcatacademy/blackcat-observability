<?php
declare(strict_types=1);

namespace BlackCat\Observability\Export;

use BlackCat\Observability\Metrics\MetricsAggregator;

final class PrometheusTextExporter
{
    /**
     * Export a Prometheus text snapshot from raw metric events.
     *
     * @param array<int,array<string,mixed>> $metrics
     */
    public static function export(array $metrics, bool $includeServiceLabel = true): string
    {
        $series = MetricsAggregator::sumBySeries($metrics, $includeServiceLabel);
        if ($series === []) {
            return '';
        }

        $lines = [];
        $typesByName = [];

        foreach ($series as $row) {
            $name = self::sanitizeMetricName($row['name']);
            if ($name === '') {
                continue;
            }

            $type = strtolower(trim($row['type']));
            $type = match ($type) {
                'counter' => 'counter',
                default => 'gauge',
            };
            $typesByName[$name] = $typesByName[$name] ?? $type;

            $labels = self::sanitizeLabels($row['labels']);
            $value = $row['value'];
            if (!is_finite($value)) {
                continue;
            }

            $labelStr = self::formatLabels($labels);
            $lines[] = [$name, $labelStr, self::formatNumber($value)];
        }

        if ($lines === []) {
            return '';
        }

        ksort($typesByName);

        // Ensure stable output: sort by metric name, then labels.
        usort(
            $lines,
            static fn (array $a, array $b): int => ($a[0] <=> $b[0]) ?: ($a[1] <=> $b[1])
        );

        $out = [];
        foreach ($typesByName as $name => $type) {
            $out[] = '# TYPE ' . $name . ' ' . $type;
            foreach ($lines as $row) {
                if ($row[0] !== $name) {
                    continue;
                }
                $out[] = $row[1] !== '' ? ($name . '{' . $row[1] . '} ' . $row[2]) : ($name . ' ' . $row[2]);
            }
        }

        return implode("\n", $out) . "\n";
    }

    private static function sanitizeMetricName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        // Prometheus: [a-zA-Z_:][a-zA-Z0-9_:]*
        $name = preg_replace('~[^a-zA-Z0-9_:]+~', '_', $name) ?? '';
        if ($name === '') {
            return '';
        }
        if (!preg_match('~^[a-zA-Z_:]~', $name)) {
            $name = 'm_' . $name;
        }
        return $name;
    }

    /**
     * @param array<string,string> $labels
     * @return array<string,string>
     */
    private static function sanitizeLabels(array $labels): array
    {
        $out = [];
        foreach ($labels as $k => $v) {
            $k = trim((string) $k);
            if ($k === '') {
                continue;
            }

            // Label: [a-zA-Z_][a-zA-Z0-9_]*
            $k = preg_replace('~[^a-zA-Z0-9_]+~', '_', $k) ?? '';
            if ($k === '') {
                continue;
            }
            if (!preg_match('~^[a-zA-Z_]~', $k)) {
                $k = 'label_' . $k;
            }

            $out[$k] = (string) $v;
        }

        ksort($out);
        return $out;
    }

    /**
     * @param array<string,string> $labels
     */
    private static function formatLabels(array $labels): string
    {
        if ($labels === []) {
            return '';
        }

        $pairs = [];
        foreach ($labels as $k => $v) {
            $pairs[] = $k . '="' . self::escapeLabelValue($v) . '"';
        }
        return implode(',', $pairs);
    }

    private static function escapeLabelValue(string $value): string
    {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace("\n", '\\n', $value);
        $value = str_replace('"', '\\"', $value);
        return $value;
    }

    private static function formatNumber(float $value): string
    {
        // Keep it compact and stable for both ints and floats.
        $out = sprintf('%.10F', $value);
        $out = rtrim($out, '0');
        $out = rtrim($out, '.');
        return $out === '' ? '0' : $out;
    }
}

