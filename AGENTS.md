# LEG coding instructions

- This is a Laravel 12 application using PHP 8.4, Blade, Vite, and Tailwind CSS.
- Before changing code, inspect the relevant implementation and tests. Explain the planned change briefly.
- Make focused changes and preserve existing behavior.
- Add or update tests for behavior changes.
- Do not use real family-history data, credentials, or production services in tests.
- Do not change dependencies or database schemas unless the task requires it.
- After PHP changes, run the relevant tests and `composer lint`.
- Run `composer analyse` when practical.
- After frontend or asset changes, run `npm run build`.
- Report commands run, results, and any checks blocked by missing services.
- Ask before destructive commands, dependency upgrades, migrations against non-test databases, or changes outside this repository.