# Docker Container Monitoring Setup

This directory contains the configuration for monitoring your Docker containers using Prometheus and Grafana.

## Services Added

### Core Monitoring
- **Prometheus** (Port 9090) - Metrics collection and storage
- **Grafana** (Port 3000) - Metrics visualization and dashboards
- **cAdvisor** (Port 8080) - Container metrics collection

### Exporters
- **Node Exporter** (Port 9100) - System/host metrics
- **PostgreSQL Exporter** (Port 9187) - PostgreSQL database metrics
- **Redis Exporter** (Port 9121) - Redis cache metrics
- **MongoDB Exporter** (Port 9216) - MongoDB database metrics

## Quick Start

1. **Start your application (monitoring starts automatically):**
   ```bash
   docker-compose up -d
   ```
   
   OR to see URLs after startup:
   ```bash
   docker-compose up -d && .\show-monitoring-urls.ps1
   ```

2. **Access Grafana:**
   - URL: http://localhost:3000
   - Username: `admin`
   - Password: `admin`

3. **Access Prometheus:**
   - URL: http://localhost:9090

4. **Access cAdvisor:**
   - URL: http://localhost:8080

## Pre-configured Dashboards

### LEG Application - Comprehensive Monitoring
A comprehensive dashboard showing:
- Container CPU and memory usage
- System CPU and memory usage
- PostgreSQL connections and transactions
- Redis metrics (clients, memory)
- MongoDB metrics (connections, operations)
- Container network I/O
- Service status (UP/DOWN)

### LEG Application - Debug Metrics
A debug dashboard showing raw metrics to help troubleshoot monitoring issues:
- All targets status
- Raw container metrics
- Raw database metrics

Both dashboards are automatically provisioned and available in Grafana.

## Key Metrics Available

### Container Metrics (from cAdvisor)
- `container_cpu_usage_seconds_total` - CPU usage
- `container_memory_usage_bytes` - Memory usage
- `container_network_receive_bytes_total` - Network RX
- `container_network_transmit_bytes_total` - Network TX
- `container_last_seen` - Container status

### System Metrics (from Node Exporter)
- `node_cpu_seconds_total` - CPU usage
- `node_memory_MemTotal_bytes` - Total memory
- `node_memory_MemAvailable_bytes` - Available memory
- `node_filesystem_size_bytes` - Disk space
- `node_load1`, `node_load5`, `node_load15` - System load

### Database Metrics
- **PostgreSQL**: Connection counts, query performance, cache hits
- **Redis**: Memory usage, commands/sec, keyspace info
- **MongoDB**: Operations/sec, connections, memory usage

## Configuration Files

- `prometheus.yml` - Prometheus configuration with all scrape targets
- `grafana/provisioning/datasources/prometheus.yml` - Grafana datasource config
- `grafana/provisioning/dashboards/dashboard.yml` - Dashboard provisioning config
- `grafana/provisioning/dashboards/comprehensive-monitoring.json` - Main monitoring dashboard
- `grafana/provisioning/dashboards/debug-metrics.json` - Debug dashboard
- `verify-metrics.sh` - Linux/Mac verification script
- `verify-metrics.bat` - Windows verification script

## Ports Used

| Service | Port | Description |
|---------|------|-------------|
| Grafana | 3000 | Web UI for dashboards |
| Prometheus | 9090 | Metrics database and web UI |
| cAdvisor | 8080 | Container metrics web UI |
| Node Exporter | 9100 | System metrics endpoint |
| PostgreSQL Exporter | 9187 | PostgreSQL metrics endpoint |
| Redis Exporter | 9121 | Redis metrics endpoint |
| MongoDB Exporter | 9216 | MongoDB metrics endpoint |

## Customization

### Adding New Dashboards
1. Export dashboard JSON from Grafana
2. Place in `grafana/provisioning/dashboards/`
3. Restart Grafana container

### Adding New Metrics Targets
1. Edit `prometheus.yml`
2. Add new job under `scrape_configs`
3. Restart Prometheus container

### Modifying Resource Limits
Resource limits are set in `docker-compose.yml` under each service's `deploy` section.

## Troubleshooting

### No Data in Dashboards
If you see "No data" in the Grafana dashboards:

1. **Run the verification script:**
   ```bash
   # Linux/Mac
   ./monitoring/verify-metrics.sh
   
   # Windows
   monitoring\verify-metrics.bat
   ```

2. **Check if all services are running:**
   ```bash
   docker-compose ps
   ```

3. **Check Prometheus targets:**
   - Open http://localhost:9090/targets
   - Ensure all targets show "UP" status

4. **Check individual exporter endpoints:**
   - cAdvisor: http://localhost:8080/metrics
   - MongoDB: http://localhost:9216/metrics
   - Redis: http://localhost:9121/metrics
   - PostgreSQL: http://localhost:9187/metrics
   - Node: http://localhost:9100/metrics

5. **Restart monitoring services:**
   ```bash
   docker-compose restart prometheus grafana cadvisor mongodb-exporter
   ```

### cAdvisor on Windows
On Windows, cAdvisor might have limited functionality. If you encounter issues:
1. Remove the `privileged: true` and `devices` sections
2. Consider using Windows-specific monitoring tools

### Permission Issues
If you encounter permission issues with volume mounts:
1. Ensure Docker has access to the required directories
2. Check that the monitoring directory has proper permissions

### Memory Usage
The monitoring stack uses approximately:
- Prometheus: 256-512MB
- Grafana: 256-512MB
- cAdvisor: 128-256MB
- Exporters: 64-128MB each

Adjust resource limits in docker-compose.yml if needed.

## Security Notes

- Default Grafana credentials are admin/admin - change these in production
- Consider enabling authentication for Prometheus in production
- Restrict network access to monitoring ports in production environments
