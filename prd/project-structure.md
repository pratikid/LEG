# Project Structure

## Main Directories & Their Roles

- `app/Http/Controllers/` – Laravel controllers for handling HTTP requests, orchestrating business logic, and returning responses. Each resource (Individual, Group, Tree, etc.) has a dedicated controller, supporting modularity and separation of concerns.
- `app/Livewire/` – Livewire components for dynamic, reactive UI. Enables AJAX-powered forms, modals, and real-time updates without custom JavaScript.
- `app/Models/` – Eloquent models for core entities (Individual, Group, Tree). Define relationships, accessors, and business rules. Central to data integrity and ORM operations.
- `app/Policies/` – Authorization policies for resource-level access control. Ensures secure, role-based permissions throughout the app.
- `app/Providers/` – Service providers for bootstrapping application services, event listeners, and custom bindings.
- `app/Services/` – Service classes encapsulating business logic (e.g., Neo4j integration, data import/export). Promotes code reuse and testability.
- `app/Traits/` – Shared code traits for cross-cutting concerns and DRY principles.
- `resources/views/` – Blade templates for UI. Organized by feature (e.g., individuals, groups, trees) for maintainability and rapid development.
- `resources/js/` – JavaScript assets, including D3.js visualizations and Alpine.js interactivity. Modular scripts for each visualization or UI feature.
- `resources/css/` – Tailwind CSS styles and customizations. Centralized for consistent theming and responsive design.
- `database/migrations/` – Database schema migrations for version-controlled, repeatable schema changes.
- `database/seeders/` – Seed data for development, testing, and demo purposes.
- `routes/` – Laravel route definitions, organized by web, API, and feature domains.
- `public/` – Public assets (images, compiled JS/CSS, entry point for web server).
- `docs/` – Project documentation, including architecture, API, and feature guides.
- `prd/` – Product requirements, planning docs, and project management artifacts.
- `tests/` – PHPUnit and Dusk tests for backend, frontend, and feature coverage.
- `config/` – Application and package configuration files.
- `storage/` – User uploads, logs, cache, and compiled views.
- `vendor/` – Composer-managed PHP dependencies.

## Key Technologies & Structure Rationale

- **Laravel:** Provides a robust MVC foundation, supporting modular controllers, models, and services.
- **Livewire:** Enables component-driven UI, reducing JavaScript complexity and improving maintainability.
- **D3.js:** Modular JS files for each visualization, supporting extensibility and reuse.
- **Neo4j:** Service classes and integration points are isolated for easy updates and testing.
- **Tailwind CSS:** Centralized config and utility classes ensure consistent, scalable design.
- **Testing:** Dedicated `tests/` directory for unit, feature, and browser tests, supporting CI/CD and code quality.

## Extensibility & Best Practices

- The structure supports SOLID principles, PSR-12, and Laravel conventions.
- New features can be added as new controllers, services, Livewire components, or Blade views without disrupting existing code.
- Documentation and PRD files are co-located for easy reference and onboarding. 