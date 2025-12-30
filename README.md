# BlackCat Observability

Observability SDK for the BlackCat ecosystem (events + metrics today; exporters/tracing planned).

## What it provides

- `ObservabilityManager` with a minimal API (`events()`, `metrics()`).
- `LocalStore` (NDJSON files) for dev/testing storage.
- PSR-3 friendly hooks (can integrate with existing loggers).
- Minimal metrics DSL:
  - counters (`metrics()->counter(...)->inc()`)
  - gauges (`metrics()->gauge(...)->set()`)

## CLI tooling

This repo ships a `blackcat-cli` manifest (`blackcat-cli.json`). CLI behavior lives in `blackcat-cli`:

```bash
blackcat observability events:tail --limit=10
blackcat observability metrics:snapshot
blackcat observability metrics:export prom
blackcat observability config:print
```

Storage directory resolution (highest priority first):
- `--storage-dir=...`
- runtime config `observability.storage_dir` (via `blackcat-config`, if available)
- workspace default (`blackcat-monitoring/logs/observability` when present, otherwise `blackcat-observability/var`)

## Monitoring stack (optional)

Operational assets (Prometheus/Grafana/Loki dev stack) live in `blackcat-monitoring` and are controlled via `blackcat-cli`:

```bash
blackcat monitoring stack up --pull
blackcat monitoring stack info
blackcat verify
```

## Runtime config (blackcat-config)

When `blackcat-config` runtime config is available, `ObservabilityManager::boot()` reads:
- `observability.service`
- `observability.storage_dir`

Minimal snippet:

```json
{
  "observability": {
    "service": "blackcat-app",
    "storage_dir": "/var/log/blackcat/observability"
  }
}
```

## Quick start

```php
use BlackCat\Observability\ObservabilityManager;

$obs = ObservabilityManager::boot();
$obs->events()->publish('auth.login', ['tenant' => 'eu-1', 'result' => 'success']);
$obs->metrics()->counter('auth_logins_total')->inc(['result' => 'success']);
$obs->metrics()->gauge('trust_kernel_ok')->set([], 1);
```

Prometheus export semantics:
- counters are summed across events,
- gauges keep the last value by timestamp (last-write-wins).
