# BlackCat Observability

Observability SDK for the BlackCat ecosystem (events + metrics today; exporters/tracing planned).

## What it provides

- `ObservabilityManager` with a minimal API (`events()`, `metrics()`).
- `LocalStore` (NDJSON files) for dev/testing storage.
- PSR-3 friendly hooks (can integrate with existing loggers).

## CLI tooling

This repo ships a `blackcat-cli` manifest (`blackcat-cli.json`). CLI behavior lives in `blackcat-cli`:

```bash
blackcat observability events:tail --limit=10
blackcat observability metrics:snapshot
blackcat observability metrics:export prom
```

By default, the local store directory is `blackcat-observability/var` (override with `--storage-dir=...`).

## Quick start

```php
use BlackCat\Observability\ObservabilityManager;

$obs = ObservabilityManager::boot();
$obs->events()->publish('auth.login', ['tenant' => 'eu-1', 'result' => 'success']);
$obs->metrics()->counter('auth_logins_total')->inc(['result' => 'success']);
```
