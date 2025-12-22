# Observability QA

## Smoke Test
- Write a couple of entries into the local store:
  - `var/events.ndjson`
  - `var/metrics.ndjson`

Example:
```bash
php -r 'require "vendor/autoload.php"; $obs=BlackCat\\Observability\\ObservabilityManager::boot(); $obs->events()->publish("qa.smoke", ["ok"=>true]); $obs->metrics()->counter("qa_smoke_total")->inc();'
```

Then inspect via `blackcat-cli` (optional):
```bash
blackcat observability events:tail --limit=5
blackcat observability metrics:snapshot
```

## Future work
- Integration tests with `blackcat-simulator` (emit events into the store).
- Prometheus exporter validation.
