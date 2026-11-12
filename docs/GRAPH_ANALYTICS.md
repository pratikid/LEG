# Neo4j Graph Analytics for LEG Platform

## Overview

The LEG genealogy platform now includes comprehensive graph analytics powered by Neo4j, enabling advanced relationship analysis, centrality metrics, community detection, and genealogical insights at scale. This system transforms family tree data into actionable intelligence for researchers, historians, and genealogy enthusiasts.

## Features

### Core Graph Analytics
- **Network Metrics**: Node counts, relationship counts, density, clustering coefficients, network diameter
- **Centrality Analysis**: Degree, betweenness, closeness centrality, and PageRank scoring
- **Community Detection**: Louvain and label propagation algorithms for family cluster identification
- **Path Analysis**: Shortest paths, all paths between individuals, genealogical distance calculation
- **Relationship Strength**: Quantified connection strength between any two individuals
- **Common Ancestors**: Most Recent Common Ancestor (MRCA) detection for multiple individuals

### Advanced Features
- **Influential Individual Detection**: Identify key figures in family networks
- **Family Cluster Analysis**: Geographic, temporal, and surname-based groupings
- **Relationship Pattern Analysis**: Marriage patterns, family size trends, endogamy detection
- **Ancestry Path Insights**: Lineage completeness, pedigree collapse analysis
- **Network Comparison**: Compare multiple family trees structurally

## Architecture

### Components

```
┌─────────────────────┐    ┌─────────────────────┐    ┌─────────────────────┐
│   Analytics API     │    │  Neo4j Analytics    │    │    Neo4j Graph      │
│     Controller      │◄──►│      Service        │◄──►│     Database        │
└─────────────────────┘    └─────────────────────┘    └─────────────────────┘
           │                          │                          │
           │                          ▼                          │
           │               ┌─────────────────────┐               │
           │               │   PostgreSQL        │               │
           └──────────────►│  Analytics Cache    │◄──────────────┘
                          └─────────────────────┘
```

### Key Classes

1. **`Neo4jAnalyticsService`** - Main service class providing graph analytics functionality
2. **`AnalyticsController`** - REST API endpoints for graph analytics
3. **`AnalyticsETLService`** - Data pipeline for syncing PostgreSQL metrics with Neo4j insights
4. **Migration**: `add_relationship_analytics_to_fact_individuals` - Database schema for caching results

## Installation & Configuration

### Prerequisites
- Neo4j 5.0+ with Graph Data Science (GDS) library
- PHP 8.1+
- Laravel 10+
- PostgreSQL 12+

### Setup

1. **Install Neo4j GDS Plugin**
```bash
# Download and install Neo4j GDS
NEO4J_HOME/bin/neo4j-admin dbms set-initial-password your_password
NEO4J_HOME/bin/neo4j start
```

2. **Configure Laravel Connection**
```php
// config/database.php
'neo4j' => [
    'driver' => 'neo4j',
    'host' => env('NEO4J_HOST', 'localhost'),
    'port' => env('NEO4J_PORT', 7687),
    'username' => env('NEO4J_USERNAME', 'neo4j'),
    'password' => env('NEO4J_PASSWORD', 'secret'),
    'database' => env('NEO4J_DATABASE', 'neo4j'),
],
```

3. **Run Database Migrations**
```bash
php artisan migrate --path=database/migrations/add_relationship_analytics_to_fact_individuals.php
```

4. **Initialize Graph Data**
```bash
# Run ETL to populate analytics tables
php artisan analytics:etl --tree-id=1

# Compute initial graph metrics
php artisan tinker
>>> app(App\Services\Analytics\Neo4jAnalyticsService::class)->computeAndCacheAnalytics(1, ['network', 'centrality', 'communities']);
```

## API Usage

### Authentication
All graph analytics endpoints require API authentication:
```bash
curl -H "Authorization: Bearer {api_token}" \
     -H "Content-Type: application/json" \
     https://your-domain.com/api/v1/analytics/trees/1/graph
```

### Network Metrics
Get comprehensive network statistics for a family tree:

```bash
GET /api/v1/analytics/trees/{treeId}/graph?section=network
```

**Response:**
```json
{
  "success": true,
  "data": {
    "network_metrics": {
      "node_count": 1247,
      "relationship_count": 2156,
      "density": 0.0278,
      "clustering_coefficient": 0.421,
      "diameter": 12,
      "connected_components": 3,
      "average_degree": 3.46,
      "degree_distribution": {
        "min": 1,
        "max": 18,
        "median": 3,
        "mean": 3.46,
        "std_dev": 2.13
      }
    }
  },
  "meta": {
    "tree_id": 1,
    "section": "network",
    "cache_ttl_minutes": 60
  }
}
```

### Centrality Analysis
Identify influential individuals in the family network:

```bash
GET /api/v1/analytics/trees/{treeId}/graph/centrality
```

**Response:**
```json
{
  "success": true,
  "data": {
    "centrality_analysis": {
      "degree_centrality": {
        "top_individuals": [
          {
            "individual_id": 123,
            "name": "John Smith",
            "score": 0.85,
            "rank": 1,
            "connections": 12
          }
        ],
        "average_score": 0.23
      },
      "betweenness_centrality": {
        "top_individuals": [
          {
            "individual_id": 456,
            "name": "Mary Bridge-Builder",
            "score": 0.45,
            "rank": 1,
            "bridge_role": "Connects Smith and Johnson families"
          }
        ],
        "average_score": 0.11
      },
      "pagerank": {
        "top_individuals": [
          {
            "individual_id": 789,
            "name": "Ancestral Patriarch",
            "score": 0.023,
            "rank": 1,
            "influence_reason": "Many descendants"
          }
        ],
        "average_score": 0.007
      }
    }
  }
}
```

### Path Finding
Find all paths between two individuals:

```bash
GET /api/v1/analytics/trees/{treeId}/paths?from_individual_id=123&to_individual_id=456&max_depth=8
```

**Response:**
```json
{
  "success": true,
  "data": {
    "paths": [
      {
        "length": 4,
        "individuals": [123, 789, 345, 456],
        "relationships": ["parent", "spouse", "child"],
        "strength": 0.82,
        "description": "John Smith → Father → Father's Wife → Mary Johnson"
      }
    ],
    "shortest_path_length": 4,
    "total_paths_found": 3,
    "genealogical_relationship": "2nd cousins"
  }
}
```

### Most Recent Common Ancestor
Find common ancestors for multiple individuals:

```bash
GET /api/v1/analytics/trees/{treeId}/mrca?individual_ids=123,456,789
```

**Response:**
```json
{
  "success": true,
  "data": {
    "mrca": {
      "individual_id": 999,
      "name": "Common Ancestor",
      "birth_year": 1820,
      "death_year": 1885,
      "generation_depth": 5
    },
    "paths_to_mrca": {
      "123": {
        "path_length": 3,
        "generations_up": 3,
        "relationship": "great-grandchild"
      },
      "456": {
        "path_length": 4,
        "generations_up": 4,
        "relationship": "great-great-grandchild"
      }
    },
    "relationship_coefficient": 0.125,
    "estimated_shared_dna_percentage": 1.56
  }
}
```

### Community Detection
Identify family clusters and communities:

```bash
GET /api/v1/analytics/trees/{treeId}/graph/communities
```

**Response:**
```json
{
  "success": true,
  "data": {
    "communities": {
      "algorithm_used": "louvain",
      "community_count": 8,
      "modularity_score": 0.74,
      "communities": [
        {
          "community_id": 1,
          "size": 67,
          "density": 0.45,
          "characteristics": {
            "primary_surname": "Smith",
            "geographic_center": "Boston, Massachusetts",
            "time_period": "1840-1920",
            "avg_generation_depth": 3.2,
            "dominant_occupation": "Farmer"
          },
          "key_members": [
            {"individual_id": 123, "name": "John Smith", "role": "founder"},
            {"individual_id": 456, "name": "Mary Smith", "role": "matriarch"}
          ]
        }
      ]
    }
  }
}
```

### Relationship Strength
Calculate connection strength between two individuals:

```bash
GET /api/v1/analytics/trees/{treeId}/relationship-strength?individual1_id=123&individual2_id=456
```

**Response:**
```json
{
  "success": true,
  "data": {
    "relationship_strength": 0.75,
    "direct_relationship": true,
    "relationship_type": "spouse",
    "common_connections": 8,
    "shared_descendants": 4,
    "path_strength": 1.0,
    "genealogical_distance": 1,
    "coefficient_of_relationship": 0.0,
    "factors": {
      "direct_connection": 0.4,
      "shared_family": 0.2,
      "geographic_proximity": 0.1,
      "temporal_overlap": 0.05
    }
  }
}
```

## Advanced Usage

### Batch Operations
Process multiple relationship analyses efficiently:

```bash
POST /api/v1/analytics/bulk/relationship-batch
Content-Type: application/json

{
  "tree_id": 1,
  "individual_pairs": [
    [123, 456],
    [123, 789],
    [456, 789]
  ]
}
```

### Graph Computation
Trigger comprehensive analytics computation (Premium feature):

```bash
POST /api/v1/analytics/trees/{treeId}/graph/compute
Content-Type: application/json

{
  "sections": ["centrality", "communities", "network"],
  "force": false
}
```

### Multi-Tree Comparison
Compare network structures across family trees:

```bash
POST /api/v1/analytics/bulk/compare-trees
Content-Type: application/json

{
  "tree_ids": [1, 2, 3]
}
```

## Performance & Caching

### Caching Strategy
- **Network metrics**: Cached for 1 hour
- **Centrality scores**: Cached for 2 hours  
- **Community detection**: Cached for 4 hours
- **Path finding**: No caching (dynamic queries)
- **Relationship strength**: Cached for 30 minutes

### Performance Guidelines
- **Small trees (< 500 individuals)**: Real-time computation
- **Medium trees (500-5000)**: Background computation with caching
- **Large trees (5000+)**: Scheduled batch processing

### Memory Requirements
- **Neo4j**: Minimum 2GB heap, recommended 8GB+ for large datasets
- **PHP**: 512MB memory limit for analytics operations

## Data Privacy & Security

### Privacy Controls
- **Data Anonymization**: Public endpoints remove personal identifiers
- **Access Control**: Tree-level permissions enforced
- **Audit Logging**: All analytics access logged
- **Data Retention**: Cached results expire automatically

### Subscription Tiers
- **Basic**: Network metrics, simple path finding
- **Plus**: Centrality analysis, community detection, exports
- **Pro**: Advanced algorithms, batch processing, multi-tree comparison

## Troubleshooting

### Common Issues

**Q: "Neo4j connection failed"**
A: Check Neo4j service status and credentials:
```bash
systemctl status neo4j
neo4j status
```

**Q: "Graph analytics computation timeout"**  
A: Increase PHP execution time limit:
```php
ini_set('max_execution_time', 300); // 5 minutes
```

**Q: "Memory limit exceeded during community detection"**
A: Reduce community algorithm parameters or increase Neo4j heap:
```
dbms.memory.heap.max_size=8G
```

### Performance Optimization

1. **Index Creation**
```cypher
CREATE INDEX individual_tree_index FOR (n:Individual) ON (n.tree_id);
CREATE INDEX relationship_type_index FOR ()-[r:RELATIONSHIP]-() ON (r.type);
```

2. **Query Optimization**
- Use `LIMIT` clauses for large result sets
- Leverage cached metrics where possible
- Consider pagination for community member lists

### Monitoring
Monitor graph analytics performance:
```bash
# Check Neo4j query performance
CALL db.stats.query.list() YIELD query, elapsedTimeMillis 
WHERE elapsedTimeMillis > 1000 
RETURN query, elapsedTimeMillis;

# Laravel analytics cache hit rates
php artisan analytics:stats --cache-metrics
```

## Development

### Testing
Run the comprehensive test suite:
```bash
# Unit tests for Neo4j service
php artisan test --filter=Neo4jAnalyticsService

# Integration tests for API endpoints  
php artisan test --filter=GraphAnalyticsApiTest

# Performance benchmarks
php artisan analytics:benchmark --tree-id=1
```

### Custom Analytics
Extend the system with custom graph algorithms:

```php
class CustomAnalyticsService extends Neo4jAnalyticsService 
{
    public function getCustomMetric(int $treeId): array
    {
        $query = "
            MATCH (n:Individual {tree_id: $treeId})
            // Your custom Cypher query
            RETURN collect(n) as result
        ";
        
        return $this->executeQuery($query, ['treeId' => $treeId]);
    }
}
```

## Roadmap

### Future Enhancements
- [ ] **DNA Matching Integration**: Correlate graph relationships with genetic data
- [ ] **Historical Timeline Visualization**: Temporal network evolution  
- [ ] **Machine Learning Insights**: Predictive relationship modeling
- [ ] **Migration Pattern Analysis**: Geographic movement tracking
- [ ] **Social Network Integration**: Connect with external genealogy platforms
- [ ] **Real-time Collaboration**: Live graph editing and analysis

### Version History
- **v1.0.0**: Core graph analytics, centrality analysis, community detection
- **v1.1.0**: Path finding, MRCA calculation, relationship strength (Planned)
- **v1.2.0**: Batch processing, multi-tree comparison (Planned)

## Support

For technical support and feature requests:
- **Documentation**: `/docs/GRAPH_ANALYTICS.md`
- **API Reference**: `/api/v1/docs/analytics`  
- **Issue Tracker**: GitHub Issues
- **Community Forum**: LEG Platform Community

## Contributing

Contributions welcome! Please see `CONTRIBUTING.md` for guidelines on:
- Code style and standards
- Testing requirements  
- Documentation updates
- Performance benchmarks

---

*Last updated: December 2024*
*LEG Platform Graph Analytics Documentation*
