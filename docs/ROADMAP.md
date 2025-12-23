# BlackCat Observability – Roadmap

## Stage 1 – Foundations ✅
- `ObservabilityConfig`, `ObservabilityManager`, file-based dev storage (`LocalStore`).
- CLI discovery via `blackcat-cli.json` + builtin `blackcat observability ...`.
- File-based dev storage + PSR logger adapter.

## Stage 2 – Metrics Pipeline
- Prometheus exporter (pull mode), remote write (push), multi-registry aggregator.
- Metric DSL (counter/gauge/histogram, buckets) shared across all repos.
- Auto-binding (request_id, tenant, service) + safe labeling (PII masking).
- Standardize trust-kernel telemetry series (quorum health, attestation age, safe-mode events) so `blackcat-monitoring` can ship dashboards/alerts without per-app glue.

## Stage 3 – Event Bus & Alerts
- SSE + WebSocket streaming, webhook connectors (Slack, Teams, PagerDuty).
- Alert rule engine (YAML) with throttle + silence support.
- CLI `alerts:simulate`, `events:replay`, integration with `blackcat-messaging`.

## Stage 4 – Tracing & Context Propagation
- OTLP exporter, `ContextCarrier` integration with `blackcat-auth` tokens (traceparent, baggage).
- Auto instrumentation hooks (PDO, Redis, HTTP clients).
- Trace analyzer dashboard (sampling configuration + heuristics).

## Stage 5 – Unified Observability UI
- React SPA (Grafana-lite) with embed API, multi-tenant RBAC (depends on `blackcat-auth`).
- Correlation view (logs ↔ traces ↔ metrics ↔ audit events).
- SLO management, error budget tracking, ROI reporting.
