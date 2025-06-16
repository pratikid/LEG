# Lumen to Laravel Migration Plan

## Overview
This document outlines the plan for migrating the LEG project from Lumen to Laravel framework. The migration is planned to take advantage of Laravel's full feature set while maintaining backward compatibility.

## Why Migrate?
- Access to Laravel's full feature set
- Better ecosystem support
- Enhanced development tools
- Improved testing capabilities
- Better documentation and community support

## Migration Scope

### Phase 1: Preparation
- [ ] Audit current Lumen codebase
- [ ] Document all custom implementations
- [ ] Create test coverage baseline
- [ ] Set up Laravel development environment

### Phase 2: Core Migration
- [ ] Set up new Laravel project structure
- [ ] Migrate core services
  - IndividualService
  - Neo4jIndividualService
  - TreeService
  - RelationshipService
- [ ] Update dependency injection
- [ ] Migrate middleware
- [ ] Update routing system

### Phase 3: Database & Models
- [ ] Migrate database migrations
- [ ] Update model relationships
- [ ] Implement Laravel's ORM features
- [ ] Optimize database queries

### Phase 4: API & Controllers
- [ ] Migrate API controllers
- [ ] Update request validation
- [ ] Implement Laravel's API resources
- [ ] Update response formatting

### Phase 5: Frontend Integration
- [ ] Update asset compilation
- [ ] Migrate view templates
- [ ] Update JavaScript integrations
- [ ] Implement Laravel Mix

### Phase 6: Testing & Quality
- [ ] Migrate test suites
- [ ] Implement Laravel's testing features
- [ ] Update CI/CD pipelines
- [ ] Performance testing

## Timeline
- Phase 1: Q3 2025
- Phase 2: Q3 2025
- Phase 3: Q3 2025
- Phase 4: Q4 2025
- Phase 5: Q4 2025
- Phase 6: Q4 2025

## Risk Mitigation
- Maintain parallel systems during migration
- Implement feature flags
- Regular backups
- Comprehensive testing
- Phased rollout

## Success Criteria
- Zero data loss
- Maintained API compatibility
- Improved performance metrics
- Complete test coverage
- Successful deployment

## Post-Migration
- Documentation updates
- Team training
- Performance monitoring
- Feature optimization

---

## Purpose
This document outlines which parts of the current Laravel codebase can be migrated to Lumen for improved scalability and performance. This migration is marked as **future scope** and is not part of the current development cycle.

---

## Why Lumen?
Lumen is a micro-framework by Laravel, optimized for stateless APIs, microservices, and high-throughput workloads. Migrating suitable components to Lumen can:
- Reduce application overhead
- Improve response times for APIs
- Enable independent scaling of microservices

---

## Candidates for Migration

| Laravel Directory/File                | Move to Lumen? | Notes                                      |
|---------------------------------------|:--------------:|---------------------------------------------|
| `app/Http/Controllers/Api/`           |      Yes       | Stateless API endpoints                     |
| `app/Services/`                       |      Yes*      | If stateless, not tightly Laravel-coupled   |
| `app/Models/`                         |      Yes*      | If not using advanced Laravel features      |
| `app/Jobs/`                           |      Yes*      | For queue workers/microservices             |
| `routes/api.php`                      |      Yes       | API routes                                 |
| `app/Http/Middleware/`                |      Yes*      | API-specific only                          |
| `resources/views/`                    |      No        | Blade not supported in Lumen                |
| `app/Livewire/`                       |      No        | Not supported in Lumen                      |
| `app/Http/Controllers/Auth/`          |      No*       | Only if simple, stateless auth              |

*Yes*: Only if the code is stateless and not dependent on advanced Laravel features (e.g., Blade, session, advanced authentication, Livewire, etc.)

---

## Migration Steps (For Future Reference)
1. **Extract API Controllers and Routes**
2. **Copy Models and Services** (stateless only)
3. **Migrate Middleware** (API-specific only)
4. **Setup Lumen Config and Environment**
5. **Test and Benchmark**

---

## Out of Scope
- Blade views, Livewire components, and web UI logic
- Features requiring advanced Laravel service providers or packages not supported by Lumen

---

## Status
**This migration is marked as future scope. No immediate action required.**

---

*Document generated on: {{DATE}}* 