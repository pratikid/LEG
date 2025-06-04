# Contributing to LEG

Welcome! This guide will help you get started as a contributor to the LEG genealogy platform.

## Prerequisites
- Docker Desktop (recommended for local development)
- Git
- PHP 8.4+
- Node.js & npm
- Basic knowledge of Laravel, Docker, and web development

## Setup
1. Clone the repository:
   ```sh
   git clone https://github.com/pratikid/LEG.git
   cd LEG
   ```
2. Follow the [SETUP_GUIDE.md](docs/SETUP_GUIDE.md) for Docker and environment setup.
3. Copy `.env.example` to `.env` and configure as needed.
4. Start the containers:
   ```sh
   docker compose up -d --build
   ```
5. Run migrations:
   ```sh
   docker compose exec app php artisan migrate
   ```
6. (Optional) Build frontend assets:
   ```sh
   docker compose exec node npm run dev
   ```

## Running Tests & Linting
- Run backend tests:
  ```sh
  docker compose exec app php artisan test
  ```
- Run frontend build/lint:
  ```sh
  docker compose exec node npm run lint
  docker compose exec node npm run dev
  ```
- Use [Laravel Pint](https://laravel.com/docs/12.x/pint) for code style:
  ```sh
  docker compose exec app ./vendor/bin/pint
  ```

## Commit & PR Guidelines
- Follow [COMMIT.md](docs/COMMIT.md) for commit message conventions.
- Use clear, descriptive PR titles and link to relevant issues/features.
- Make each PR a logical unit; avoid mixing unrelated changes.
- Reference the feature/requirement you are addressing (see [FEATURES.md](docs/FEATURES.md) and [requirements.md](prd/requirements.md)).

## Where to Find Things
- **Features & Roadmap:** [docs/FEATURES.md], [prd/features.md]
- **Requirements:** [prd/requirements.md]
- **Architecture:** [prd/implementation.md], [prd/project-structure.md]
- **UI/UX:** [docs/UI_UX.md], [docs/TREE_VIEW.md]
- **Setup:** [docs/SETUP_GUIDE.md]
- **Commit Guidelines:** [docs/COMMIT.md]

## Getting Help
- Check the [USER_GUIDE.md](docs/USER_GUIDE.md) for common issues.
- For technical questions, open a GitHub issue or contact a maintainer.

Happy contributing! 