# Commit Guidelines for GitHub

This document outlines how to write clear, consistent, and meaningful commit messages when contributing to this project. Following these guidelines helps maintain a readable project history and improves collaboration.

---

## 1. Commit Message Format

Use the following format for your commit messages:

```
type: short, imperative summary of the change

Optional longer description (wrap at 72 characters)
```

- **type**: One of the conventional commit prefixes listed below.
- **summary**: A concise description of what the commit does.
- **body** (optional): More details about the change, reasoning, or context.

**Example:**
```
feat: add user profile page with avatar upload

This introduces a new user profile page where users can upload and update their avatar images. Includes validation and error handling.
```

---

## 2. Conventional Commit Types

Use these prefixes at the start of your commit message:

feat: introduce a new feature or significant enhancement  
fix: correct a bug, error, or unintended behavior  
docs: update or add documentation only  
style: make code style changes that do not affect code meaning  
refactor: restructure or improve code without changing its behavior  
perf: improve performance  
test: add or update tests  
chore: perform routine tasks, maintenance, or dependency updates  
build: modify the build system or external dependencies  
ci: update continuous integration configuration or scripts  
revert: undo a previous commit  
merge: merge branches together  
wip: indicate work in progress, not ready for production  
hotfix: apply a critical fix, usually for production issues  
security: address security-related changes

---

## 3. How to Commit in GitHub

1. **Stage your changes:**
   ```sh
   git add <file1> <file2> ...
   ```
2. **Write a commit message:**
   ```sh
   git commit -m "type: short, imperative summary"
   # or for a longer message
   git commit
   # (your editor will open; use the format above)
   ```
3. **Push your changes:**
   ```sh
   git push origin <branch-name>
   ```
4. **Open a Pull Request (PR):**
   - Go to your repository on GitHub.
   - Click "Compare & pull request".
   - Add a descriptive PR title and description.
   - Submit the PR for review.

---

## 4. Best Practices

- Use the correct type for your change.
- Write clear, concise summaries in the imperative mood (e.g., "add", "fix", "update").
- Reference related issues or PRs when relevant (e.g., `fix: correct login bug (#42)`).
- Make each commit a logical unit; avoid mixing unrelated changes.
- Use the body to explain "why" if the change is not obvious.

---

By following these guidelines, you help keep the project history clean, understandable, and easy to navigate for all contributors. 