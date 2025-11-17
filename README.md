# BlackCat Observability

Centralizovaná observability vrstva pro celý BlackCat stack. Cílem je sjednotit logování, metriky (Prometheus, OTLP), tracing, alerting a real-time event streaming (SSE/Webhooks) tak, aby každé repo (auth, database, messaging, crypto…) implementovalo stejné standardy.

## Klíčové prvky

- **Unified SDK** – `ObservabilityManager` poskytuje jednoduché API (`metrics()`, `events()`, `traces()`) pro publikační a sběrné moduly.
- **Multi-export** – podporujeme Prometheus, OpenTelemetry/OTLP, Loki, Kafka + webhooky, volitelně „firehose“ pro SecOps.
- **Sensitive data guard** – integrace s `blackcat-crypto` (masking + envelope) a `blackcat-auth` policy (tenant/quota).
- **Dev tooling** – CLI `bin/observability` s příkazy `metrics:tail`, `events:stream`, `alerts:test`.

## Struktura

```
blackcat-observability/
├── src/
│   ├── ObservabilityManager.php
│   ├── Metrics/
│   ├── Events/
│   └── Exporter/
├── docs/ROADMAP.md
├── README.md
├── composer.json
└── tests/
```

## Integrace

- `blackcat-auth` → posílá audit události přes `AuthEventHook` + SSE feed (`/events/stream`).
- `blackcat-messaging` → připojuje se do event pipeline (lag metrics, consumer status).
- `blackcat-database` / `blackcat-database-sync` → metriky replikace, drift detection, CDC throughput.
- `blackcat-crypto` → wrap queue metrics a KMS health state.

## Rychlý start

```bash
composer install
php bin/observability help

# tail posledních 10 událostí / snapshot metrik
php bin/observability events:tail
php bin/observability metrics:snapshot
```

```php
$obs = ObservabilityManager::boot();
$obs->events()->publish('auth.login', ['tenant' => 'eu-1', 'result' => 'success']);
$obs->metrics()->counter('auth_logins_total')->inc(['result' => 'success']);
```
