# Tech Stack

## Backend
- **Laravel (PHP 8.4+):** Modern MVC framework used for all backend logic, routing, authentication, and authorization. Implements strict typing, PSR-12 standards, and leverages Laravel's service container, middleware, and event system for modular, maintainable code.
- **Eloquent ORM:** Used for all relational data (PostgreSQL), providing expressive model relationships, query scopes, and database migrations. Handles validation, form requests, and data integrity.
- **PostgreSQL:** Primary relational database for structured, transactional data (users, trees, groups, individuals, etc.), accessed via Eloquent ORM with migrations and seeders for schema management.
- **Neo4j:** Graph database for efficient modeling and querying of complex family relationships (parent-child, spouse, sibling, ancestry, etc.). Integrated via the `laudis/neo4j-php-client` package. All relationship management and advanced queries are handled through dedicated service classes and controllers.
- **MongoDB (Planned):** Intended for flexible, large, or nested data (e.g., media, stories, logs), integrated via `mongodb/laravel-mongodb` package.
- **Redis:** Used for caching, session management, and queue backends, integrated via Laravel's cache and queue drivers.

## Frontend
- **Blade:** Laravel's templating engine for server-rendered views. Layouts, components, and partials are used for DRY, maintainable markup, with consistent use of Tailwind CSS classes for styling.
- **Tailwind CSS:** Utility-first CSS framework for rapid, responsive, and consistent UI. Custom configuration for dark mode, color palette, and design tokens. Used throughout all forms, layouts, and interactive components.
- **Livewire:** Enables reactive, AJAX-powered UI components without custom JavaScript. Used for dynamic forms, modals, and real-time updates.
- **D3.js:** JavaScript library for data-driven, interactive visualizations. Used for rendering family trees, timelines, and relationship graphs, following best practices for modular, reusable chart components.
- **Alpine.js:** Lightweight JavaScript framework for simple interactivity (e.g., sidebar toggles, dark mode switches).

## Other Tools
- **Composer:** PHP dependency management for Laravel, Neo4j, MongoDB, and other backend packages.
- **NPM & Vite:** JavaScript and CSS build tooling. Handles Tailwind CSS, D3.js, and other frontend dependencies.
- **PHPUnit/Dusk:** PHPUnit for backend unit and feature tests, Dusk for browser and end-to-end testing.
- **Mockery:** For mocking dependencies in tests.
- **Docker:** Containerized local development and deployment. Separate containers for app, database (PostgreSQL), Neo4j, and supporting services. Includes scripts for permission fixes and environment setup.

## Rationale
- The stack is chosen for its synergy, modern best practices, and suitability for genealogy data and visualization needs. It balances performance, flexibility, and developer productivity, with clear separation of concerns between relational, graph, and flexible data, and a rich, interactive UI/UX. 