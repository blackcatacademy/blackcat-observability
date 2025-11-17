<?php

declare(strict_types=1);

return [
    'service' => 'blackcat-platform',
    'storage_dir' => __DIR__ . '/../var',
    'exporters' => [
        'prometheus' => [
            'type' => 'pull',
            'port' => 9200,
        ],
        'otlp' => [
            'type' => 'otlp',
            'endpoint' => '${env:OTLP_ENDPOINT}',
        ],
    ],
];
