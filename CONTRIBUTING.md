# Contributing to LEG

Thank you for your interest in contributing to LEG! This document provides guidelines and instructions for contributing to the project.

## Code of Conduct

By participating in this project, you agree to abide by our [Code of Conduct](CODE_OF_CONDUCT.md).

## How Can I Contribute?

### Reporting Bugs

- Check if the bug has already been reported in the issues section
- Use the bug report template when creating a new issue
- Include detailed steps to reproduce the bug
- Provide screenshots or videos if applicable
- Include your environment details (OS, browser, etc.)

### Suggesting Features

- Check if the feature has already been suggested
- Use the feature request template
- Provide a clear description of the feature
- Explain why this feature would be useful
- Include any relevant examples or mockups

### Pull Requests

1. Fork the repository
2. Create a new branch for your feature/fix
3. Make your changes
4. Write or update tests as needed
5. Ensure all tests pass
6. Update documentation if necessary
7. Submit a pull request

### Development Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/pratikid/LEG.git
   cd LEG
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Set up environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Start the development server:
   ```bash
   ./setup.sh  # For Linux/Mac
   # or
   setup.bat   # For Windows
   ```

## Coding Standards

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Write clear and concise comments
- Keep functions small and focused
- Write unit tests for new features
- Update documentation for API changes

## Git Workflow

1. Create a new branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes and commit:
   ```bash
   git add .
   git commit -m "feat: add new feature"
   ```

3. Push to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

4. Create a pull request

## Commit Message Format

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

- `feat:` for new features
- `fix:` for bug fixes
- `docs:` for documentation changes
- `style:` for formatting changes
- `refactor:` for code refactoring
- `test:` for adding tests
- `chore:` for maintenance tasks

## Testing

- Write tests for new features
- Ensure all tests pass before submitting PR
- Run the full test suite:
  ```bash
  php artisan test
  ```

## Documentation

- Update relevant documentation
- Add comments for complex code
- Include examples for new features
- Update API documentation if needed

## Review Process

1. All PRs require at least one review
2. Address review comments promptly
3. Keep PRs focused and manageable
4. Respond to CI/CD feedback

## Getting Help

- Check the [documentation](docs/README.md)
- Join our [Discord community](https://discord.gg/leg)
- Open an issue for questions

## License

By contributing to LEG, you agree that your contributions will be licensed under the project's [MIT License](LICENSE.md). 