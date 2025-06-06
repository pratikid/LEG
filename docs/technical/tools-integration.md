# Monitoring, Profiling, and Reporting Tools Integration

This guide explains how to integrate monitoring, profiling, and reporting tools into your LEG project using Docker Compose. The stack includes Prometheus, Grafana, Loki, Promtail, Jaeger, OpenTelemetry Collector, k6, and Pyroscope. These tools provide comprehensive observability for metrics, logs, traces, load testing, and profiling.

---

## 1. Docker Compose Integration

The following services are added to your `docker-compose.yml`:

- **Prometheus**: Metrics collection
- **Grafana**: Visualization and dashboards
- **Loki**: Log aggregation
- **Promtail**: Log shipping from containers to Loki
- **Jaeger**: Distributed tracing
- **OpenTelemetry Collector**: Receives, processes, and exports telemetry data
- **k6**: Synthetic monitoring and load testing
- **Pyroscope**: Continuous profiling

All core application services (nginx, app, postgres, neo4j, mongodb, redis, node) are connected to both the main and monitoring networks for seamless data collection.

---

## 2. Supporting Configuration Files

You must create the following directories and files for the monitoring stack to function:

### 2.1 Prometheus Configuration
- **Path:** `monitoring/prometheus/prometheus.yml`
- **Purpose:** Defines scrape targets for metrics.

```yaml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

scrape_configs:
  - job_name: 'prometheus'
    static_configs:
      - targets: ['localhost:9090']
  - job_name: 'node-exporter'
    static_configs:
      - targets: ['node-exporter:9100']
  - job_name: 'app-metrics'
    metrics_path: /metrics
    static_configs:
      - targets: ['app:8000']
  - job_name: 'nginx'
    static_configs:
      - targets: ['nginx:80/nginx_status']
  - job_name: 'redis'
    static_configs:
      - targets: ['redis:6379']
  - job_name: 'otel-collector'
    static_configs:
      - targets: ['otel-collector:8888']
```

### 2.2 Grafana Data Sources
- **Path:** `monitoring/grafana/provisioning/datasources/datasources.yml`
- **Purpose:** Auto-provisions Prometheus and Loki as data sources.

```yaml
apiVersion: 1

datasources:
  - name: Prometheus
    type: prometheus
    url: http://prometheus:9090
    access: proxy
    isDefault: true
    version: 1
    editable: false
  - name: Loki
    type: loki
    url: http://loki:3100
    access: proxy
    version: 1
    editable: false
```

### 2.3 Grafana Dashboards
- **Path:** `monitoring/grafana/provisioning/dashboards/dashboards.yml`
- **Purpose:** Auto-provisions dashboards from files.

```yaml
apiVersion: 1

providers:
  - name: 'Custom Dashboards'
    orgId: 1
    folder: ''
    type: file
    disableDeletion: false
    editable: true
    options:
      path: /etc/grafana/provisioning/dashboards
```
- Place your dashboard `.json` files in `monitoring/grafana/provisioning/dashboards/`.

### 2.4 Promtail Configuration
- **Path:** `monitoring/promtail/config.yml`
- **Purpose:** Scrapes Docker container logs and ships to Loki.

```yaml
server:
  http_listen_port: 9080
  grpc_listen_port: 0
positions:
  filename: /tmp/positions.yaml
clients:
  - url: http://loki:3100/loki/api/v1/push
scrape_configs:
  - job_name: system
    pipeline_stages:
      - docker: {}
      - labels:
          container:
          filename:
          job:
    static_configs:
      - targets: ['localhost']
        labels:
          job: containerlogs
          __path__: /var/lib/docker/containers/*/*log
  - job_name: app_logs
    docker_sd_configs:
      - host: unix:///var/run/docker.sock
        refresh_interval: 5s
    relabel_configs:
      - source_labels: ['__meta_docker_container_name']
        regex: '/(.*)'
        target_label: 'container'
      - source_labels: ['__meta_docker_container_id']
        target_label: 'instance'
```

### 2.5 OpenTelemetry Collector Configuration
- **Path:** `monitoring/otel-collector/otel-collector-config.yaml`
- **Purpose:** Receives traces, metrics, and logs; exports to Jaeger, Prometheus, and Loki.

```yaml
receivers:
  otlp:
    protocols:
      grpc:
      http:
exporters:
  jaeger:
    endpoint: jaeger:14250
    tls:
      insecure: true
  prometheus:
    endpoint: "0.0.0.0:8888"
  loki:
    endpoint: http://loki:3100/loki/api/v1/push
service:
  pipelines:
    traces:
      receivers: [otlp]
      exporters: [jaeger]
    metrics:
      receivers: [otlp]
      exporters: [prometheus]
    logs:
      receivers: [otlp]
      exporters: [loki]
```

### 2.6 k6 Example Test Script
- **Path:** `monitoring/k6-tests/example_test.js`
- **Purpose:** Example load test for Nginx.

```javascript
import http from 'k6/http';
import { sleep, check } from 'k6';

export default function () {
  const res = http.get('http://nginx:80');
  check(res, { 'status was 200': (r) => r.status == 200 });
  sleep(1);
}
```

---

## 3. Instrumentation & Configuration

- **Laravel/PHP**:
  - **Metrics**: Use a Prometheus PHP client (e.g., `promphp/prometheus_client_php`) to expose `/metrics`.
  - **Logs**: Configure Laravel to log to `stdout`/`stderr` for Promtail.
  - **Tracing**: Use OpenTelemetry PHP SDK (`open-telemetry/opentelemetry-php`) to send traces to `otel-collector`.
  - **Profiling**: Integrate a PHP profiler compatible with Pyroscope (e.g., pprof-compatible or custom integration).
- **Nginx**: Enable `stub_status` for Prometheus scraping at `/nginx_status`.
- **Redis, Postgres, Mongo, Neo4j**: Add Prometheus exporters for advanced metrics (not included by default).

---

## 4. Usage Instructions

1. **Create Directories & Files**
   - Create the `monitoring` directory and all subdirectories/files as described above.
2. **Build & Start Services**
   ```bash
   docker compose build
   docker compose up -d
   ```
3. **Access Dashboards & UIs**
   - Grafana: [http://localhost:3000](http://localhost:3000) (admin/prom_graf_admin)
   - Prometheus: [http://localhost:9090](http://localhost:9090)
   - Jaeger: [http://localhost:16686](http://localhost:16686)
   - Pyroscope: [http://localhost:4040](http://localhost:4040)

---

## 5. Notes & Recommendations

- **Security**: Change default passwords before production use.
- **Exporters**: For full database metrics, add exporters for Redis, Postgres, MongoDB, and Neo4j.
- **Instrumentation**: Instrument your application for metrics, logs, and traces as needed.
- **Custom Dashboards**: Import or create Grafana dashboards for your use case.
- **k6**: Use k6 for load testing; scripts can be placed in `monitoring/k6-tests/`.

---

This setup provides a robust observability stack for monitoring, profiling, and reporting, enabling data-driven performance optimization and troubleshooting for your Laravel-based LEG project. 