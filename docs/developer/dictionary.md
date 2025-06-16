# Project Dictionary

This document defines the key terminology, naming conventions, and domain-specific terms used throughout the LEG genealogy application. This serves as a reference for developers, designers, and contributors.

## Domain Terms

### Genealogy Concepts
| Term | Definition |
|------|------------|
| Individual | A person in the family tree with associated metadata (birth, death, relationships) |
| Family | A group of individuals connected by marriage or parent-child relationships |
| Event | A significant occurrence in an individual's life (birth, marriage, death, etc.) |
| Source | Documentation or evidence supporting genealogical information |
| GEDCOM | Genealogical Data Communication format for data exchange |
| Tree | A collection of individuals and their relationships forming a family structure |

### Data Structures
| Term | Definition |
|------|------------|
| Node | A vertex in the graph representing an individual |
| Edge | A connection between nodes representing relationships |
| Graph | The complete network of nodes and edges in Neo4j |
| Document | A MongoDB record containing detailed individual information |
| Cache | Temporary storage in Redis for frequently accessed data |

## Technical Terms

### Architecture Components
| Term | Definition |
|------|------------|
| Service | A modular component handling specific functionality |
| Repository | Data access layer for database operations |
| Controller | Request handler managing business logic |
| Middleware | Request/response processing layer |
| Provider | Service container configuration and bootstrapping |

### UI Components
| Term | Definition |
|------|------------|
| Layout | Base template structure for pages |
| Component | Reusable UI element (Blade/Livewire) |
| View | Page template rendering data |
| Partial | Reusable view fragment |
| Modal | Overlay dialog for focused interactions |

## Naming Conventions

### File Structure
- **Controllers**: `PascalCaseController.php` (e.g., `IndividualController.php`)
- **Models**: `PascalCase.php` (e.g., `Individual.php`)
- **Views**: `kebab-case.blade.php` (e.g., `individual-profile.blade.php`)
- **Components**: `kebab-case.blade.php` (e.g., `tree-node.blade.php`)
- **Migrations**: `YYYY_MM_DD_HHMMSS_description.php`
- **Tests**: `PascalCaseTest.php` (e.g., `IndividualTest.php`)

### Code Style
- **Variables**: `camelCase` (e.g., `$individualName`)
- **Constants**: `UPPER_SNAKE_CASE` (e.g., `MAX_TREE_SIZE`)
- **Functions**: `camelCase` (e.g., `getIndividualById()`)
- **Classes**: `PascalCase` (e.g., `IndividualService`)
- **Interfaces**: `PascalCase` with `Interface` suffix (e.g., `TreeInterface`)

### Database
- **Tables**: `snake_case` (e.g., `individuals`)
- **Columns**: `snake_case` (e.g., `birth_date`)
- **Foreign Keys**: `singular_table_name_id` (e.g., `individual_id`)
- **Indexes**: `idx_table_column` (e.g., `idx_individuals_name`)

## Common Abbreviations

| Abbreviation | Full Form | Usage Context |
|--------------|-----------|---------------|
| GEDCOM | Genealogical Data Communication | Data import/export |
| API | Application Programming Interface | External integrations |
| UI | User Interface | Frontend development |
| UX | User Experience | Design and interaction |
| DB | Database | Data storage |
| ID | Identifier | Primary/Foreign keys |

## Version Control

### Branch Naming
- Feature: `feature/description`
- Bugfix: `fix/description`
- Hotfix: `hotfix/description`
- Release: `release/version`

### Commit Messages
- Format: `type(scope): description`
- Types: feat, fix, docs, style, refactor, test, chore
- Example: `feat(tree): add zoom controls to visualization`

## Environment Variables

| Variable | Purpose | Example |
|----------|---------|---------|
| `APP_ENV` | Application environment | `production` |
| `DB_CONNECTION` | Database type | `mongodb` |
| `NEO4J_URI` | Graph database URI | `bolt://localhost:7687` |
| `REDIS_HOST` | Cache server | `localhost` |

---

*This dictionary is regularly updated as new terms and conventions are introduced.*

*Last updated: June 2025* 