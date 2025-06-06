# Code Owners

This file defines the owners of different parts of the codebase. These owners will be automatically requested for review when changes are made to their areas.

## Core Team

* @pratikid - Project Lead
* @pratikid - Technical Lead - Looking for a maintainer
* @pratikid - Security Lead - Looking for a maintainer
* @pratikid - Documentation Lead - Looking for a maintainer
* @pratikid - DevOps Lead - Looking for a maintainer
* @pratikid - QA Lead - Looking for a maintainer
* @pratikid - Frontend Lead - Looking for a maintainer
* @pratikid - Backend Lead - Looking for a maintainer
* @pratikid - Database Lead - Looking for a maintainer
* @pratikid - API Lead - Looking for a maintainer
* @pratikid - Neo4j Lead - Looking for a maintainer

## Areas of Ownership

### Backend
/app/Http/Controllers/ @backend-team
/app/Services/ @backend-team
/app/Models/ @backend-team
/app/Jobs/ @backend-team
/app/Events/ @backend-team
/app/Listeners/ @backend-team
/app/Providers/ @backend-team
/app/Exceptions/ @backend-team
/app/Console/ @backend-team

### Frontend
/resources/js/ @frontend-team
/resources/css/ @frontend-team
/resources/views/ @frontend-team
/public/ @frontend-team

### Database
/database/migrations/ @database-team
/database/seeders/ @database-team
/database/factories/ @database-team

### Testing
/tests/ @qa-team
/phpunit.xml @qa-team
/.github/workflows/ @qa-team

### Documentation
/docs/ @docs-team
/README.md @docs-team
/CONTRIBUTING.md @docs-team
/CHANGELOG.md @docs-team

### Infrastructure
/docker/ @devops-team
/docker-compose.yml @devops-team
/.env.example @devops-team
/setup.sh @devops-team
/setup.bat @devops-team

### Security
/app/Http/Middleware/ @security-team
/config/auth.php @security-team
/config/sanctum.php @security-team

### API
/routes/api.php @api-team
/app/Http/Controllers/Api/ @api-team

### Neo4j Integration
/app/Services/Neo4j/ @neo4j-team
/config/neo4j.php @neo4j-team

## Review Process

1. All PRs require at least one review from the relevant code owners
2. Security-related changes require review from @security-team
3. Breaking changes require review from @pratikid
4. Documentation changes require review from @docs-team

## Team Definitions

@backend-team
- @backend-lead
- @backend-dev1
- @backend-dev2

@frontend-team
- @frontend-lead
- @frontend-dev1
- @frontend-dev2

@database-team
- @db-lead
- @db-dev1

@qa-team
- @qa-lead
- @qa-engineer1
- @qa-engineer2

@docs-team
- @docs-lead
- @technical-writer

@devops-team
- @devops-lead
- @devops-engineer

@security-team
- @security-lead
- @security-engineer

@api-team
- @api-lead
- @api-dev1

@neo4j-team
- @neo4j-lead
- @neo4j-dev1 