# Observability QA

## Smoke Test
- `tests/test.cli` – nastaví `OBS_SERVICE`/`OBS_STORAGE`, spustí `bin/observability events:tail` a `metrics:snapshot`.
- Použití: `bash tests/test.cli` (vyžaduje PHP).

## Budoucí práce
- Integration testy s `blackcat-simulator` (emit událostí do store).
- Prometheus exporter validation.
