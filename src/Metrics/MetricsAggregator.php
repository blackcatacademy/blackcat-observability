<?php
declare(strict_types=1);

namespace BlackCat\Observability\Metrics;

final class MetricsAggregator
{
    /**
     * Sum metrics by name (ignores labels).
     *
     * @param array<int,array<string,mixed>> $metrics
     * @return array<string,float>
     */
    public static function sumByName(array $metrics): array
    {
        $aggregate = [];
        foreach ($metrics as $metric) {
            if (!is_array($metric)) {
                continue;
            }

            $name = is_string($metric['name'] ?? null) ? (string) $metric['name'] : '';
            if ($name === '') {
                $name = 'unknown';
            }

            $rawValue = $metric['value'] ?? 0;
            $value = is_numeric($rawValue) ? (float) $rawValue : 0.0;
            $aggregate[$name] = ($aggregate[$name] ?? 0.0) + $value;
        }

        ksort($aggregate);
        return $aggregate;
    }

    /**
     * Sum metrics by (name + labels + optional service label).
     *
     * @param array<int,array<string,mixed>> $metrics
     * @return list<array{name:string,type:string,labels:array<string,string>,value:float}>
     */
    public static function sumBySeries(array $metrics, bool $includeServiceLabel = true): array
    {
        /** @var array<string,array{name:string,type:string,labels:array<string,string>,value:float}> $series */
        $series = [];

        foreach ($metrics as $metric) {
            if (!is_array($metric)) {
                continue;
            }

            $name = is_string($metric['name'] ?? null) ? trim((string) $metric['name']) : '';
            if ($name === '') {
                continue;
            }

            $type = is_string($metric['type'] ?? null) ? trim((string) $metric['type']) : '';
            if ($type === '') {
                $type = 'gauge';
            }

            $rawValue = $metric['value'] ?? null;
            if (!is_numeric($rawValue)) {
                continue;
            }
            $value = (float) $rawValue;

            $labels = [];
            $rawLabels = $metric['labels'] ?? null;
            if (is_array($rawLabels)) {
                foreach ($rawLabels as $k => $v) {
                    if (!is_string($k) || $k === '') {
                        continue;
                    }
                    if (!is_scalar($v) && $v !== null) {
                        continue;
                    }
                    $labels[$k] = (string) ($v ?? '');
                }
            }

            if ($includeServiceLabel) {
                $svc = is_string($metric['service'] ?? null) ? trim((string) $metric['service']) : '';
                if ($svc !== '') {
                    $labels['service'] = $svc;
                }
            }

            ksort($labels);
            $seriesKey = $name . '|' . $type . '|' . json_encode($labels, JSON_UNESCAPED_SLASHES);

            if (!isset($series[$seriesKey])) {
                $series[$seriesKey] = [
                    'name' => $name,
                    'type' => $type,
                    'labels' => $labels,
                    'value' => 0.0,
                ];
            }
            $series[$seriesKey]['value'] += $value;
        }

        ksort($series);
        return array_values($series);
    }
}

