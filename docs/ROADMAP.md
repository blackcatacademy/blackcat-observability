# BlackCat Observability – Roadmap

## Stage 1 – Foundations ✅
- `ObservabilityConfig`, `ObservabilityManager`, in-memory store + CLI (`events:tail`, `metrics:snapshot`).
- File-based dev storage + PSR logger adapter.

## Stage 2 – Metrics Pipeline
- Prometheus exporter (pull mode), remote write (push), multi-registry aggregátor.
- Metric DSL (counter/gauge/histogram, buckets) sdílený se všemi repozitáři.
- Auto-binding (request_id, tenant, service) + safe labeling (PII masking).

## Stage 3 – Event Bus & Alerts
- SSE + WebSocket streaming, webhook connectors (Slack, Teams, PagerDuty).
- Alert rule engine (YAML) s throttle a silence support.
- CLI `alerts:simulate`, `events:replay`, integrace s `blackcat-messaging`.

## Stage 4 – Tracing & Context Propagation
- OTLP exporter, `ContextCarrier` integration s `blackcat-auth` tokens (traceparent, baggage).
- Auto instrumentation hooks (PDO, Redis, HTTP clients).
- Trace analyzer dashboard (sampling configuration + heuristics).

## Stage 5 – Unified Observability UI
- React SPA (Grafana-lite) s embed API, multi-tenant RBAC (závislé na `blackcat-auth`).
- Correlation view (logs ↔ traces ↔ metrics ↔ audit events).
- SLO management, error budget tracking, ROI reporting.
