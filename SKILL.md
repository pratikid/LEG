# SKILL.md

## Model
- Preferred model: `qwen3-coder:30b`
- Purpose: local coding agent for this Laravel 12 genealogy application
- Use this guide as the repo-specific operating manual for implementation, debugging, and validation

## Project profile
- Repository: `pratikid/LEG`
- Description: Lineage Exploration and Genealogy - A family history platform
- Stack: Laravel 12, PHP 8.4+, Blade, Vite, Tailwind CSS, PostgreSQL, Neo4j, MongoDB, Redis
- Primary app type: web application for family trees, genealogy data, import/export, search, timelines, and admin tooling

## Architecture snapshot
- `app/` contains controllers, models, services, jobs, traits, notifications, providers, and policies
- `routes/web.php` and `routes/api.php` define web and REST endpoints
- `resources/views/` contains Blade templates and reusable components
- `database/migrations/` defines schema evolution
- `database/seeders/` populates reference/default data
- `tests/` contains PHPUnit feature and unit tests
- `public/` is web entrypoint and static assets
- `docker/` contains local runtime and service configuration
- `monitoring/` contains Prometheus/Grafana config

## Mission
- Fix bugs and improve the project while preserving existing behavior
- Keep changes small, correct, and verifiable
- Prefer production-quality solutions over hacks
- Never stop at a partial fix when a root cause is still unresolved

## Required workflow
1. Start with narrow discovery
   - Identify the relevant file(s) before editing
   - Read the exact implementation and any directly related tests
2. Determine root cause
   - Check whether the problem is a syntax issue, logic bug, configuration bug, dependency issue, migration issue, runtime issue, or missing test coverage
3. Apply the minimal correct fix
   - Prefer surgical changes over refactors
   - Preserve compatibility with current features unless the task explicitly requires a breaking change
4. Update tests when behavior changes
   - Add or fix tests for changed behavior
   - Do not add low-value tests solely to satisfy a metric
5. Validate before claiming completion
   - Run the smallest relevant commands
   - If a broader check is necessary, run the target suite or build

## Critical operating rules
- Do not modify unrelated files
- Do not add dependencies without strong justification
- Do not change database schema or seeded data unless the task explicitly requires it
- Do not expose secrets, tokens, or environment variables in code, logs, or output
- Do not leave known failing tests uninvestigated
- Do not claim a fix is complete without a validation command
- Do not assume a bug is fixed without checking the actual code path and running relevant tests

## Security rules
- Never expose `phpinfo()`, environment details, stack traces, or sensitive config through public routes
- Keep authentication and authorization behavior intact
- Do not bypass CSRF or validation checks unless explicitly required and justified
- Do not commit hard-coded credentials or local secrets
- Treat family data as sensitive and avoid leaking it in logs or prompts

## Validation commands
Use the smallest relevant validation set. For this repo, the normal commands are:

### PHP / Laravel
```bash
composer lint
composer analyse
php artisan test
```

### Frontend / Vite
```bash
npm run build
```

### Full app boot checks
```bash
php artisan config:clear
php artisan test
php artisan view:clear
php artisan route:clear
```

### Docker-specific checks when relevant
```bash
docker compose config
docker compose build
docker compose up -d
```

## Repo-specific things to watch
- File `public/info.php` must not be used to expose PHP runtime details
- `.env.example` sets database and service credentials for local/dev usage; do not commit a real `.env`
- The project uses multiple data stores: PostgreSQL, Neo4j, MongoDB, Redis
- GEDCOM import logic and multi-database interactions are high-risk areas; verify behavior carefully
- Route definitions in `routes/web.php` and API endpoints in `routes/api.php` should remain consistent with controllers and tests
- Reusable Blade components live under `resources/views/components/`

## Suggested coding style
- Keep PHP strict: prefer explicit types where appropriate
- Preserve Laravel conventions and naming patterns
- Keep methods small and cohesive
- Prefer existing patterns in the repo over inventing new abstractions
- Favor readable code over clever code
- Use comments only when they explain non-obvious logic

## Preferred task framing
When responding to a request, do this:
1. Explain the likely root cause and file(s) involved
2. State the minimal fix plan
3. Make the code change
4. Run the relevant validation command(s)
5. Summarize the result, including blockers if validation cannot run in this environment

## Example of a good action pattern
- “I’m checking the route/controller/test path for this behavior. The likely issue is X in file Y.”
- “I found the root cause: Z. I’m patching the specific call path and will run the targeted Laravel tests afterward.”
- “The fix is in place. I ran `php artisan test --filter=...` and the relevant check passed.”

## Output expectations
- Keep responses concise but complete
- Include files changed and why
- Mention validation commands and outcomes
- If execution is blocked, clearly state what is missing and why
- Do not claim runtime success without a real check

## Known risk areas
- authentication/login flows
- role-based admin routes
- relation handling with Neo4j
- MongoDB and PostgreSQL consistency during imports
- large GEDCOM import performance and memory use
- queue/worker jobs and background processing
- config and env differences between local/dev/test environments

## Final rule
When in doubt: read the exact implementation, verify the bug using the relevant code path, fix the root cause, and validate with the smallest correct command.

## Model-specific note for qwen3-coder:30b
- This model is strong for code editing, test generation, and refactoring
- It should still be used in a review loop: inspect code carefully, ask for the relevant patch scope, and validate changes with project commands
- Prefer minimal, explicit edits over broad rewrites
- For difficult debugging, reason step by step and confirm assumptions against source before patching
