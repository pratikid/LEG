# Project Dictionary

This document explains the nomenclature and naming conventions used throughout the LEG project. Update this file as new terms and conventions are introduced.

---

## UI Components

| Term                | Meaning/Usage                                              |
|---------------------|-----------------------------------------------------------|
| Layout              | Blade file providing the main structure for pages          |
| Component           | Reusable UI element (Blade or Livewire)                   |
| Sidebar             | Vertical navigation menu, usually for admin/management    |
| Timeline            | Chronological list of events or activities                |
| Tree View           | Hierarchical data visualisation (e.g., org chart)         |
| Modal               | Popup dialog for forms or confirmations                   |
| Pagination          | UI for navigating through multiple pages of data          |

## Models & Domain Terms

| Term                | Meaning/Usage                                              |
|---------------------|-----------------------------------------------------------|
| User                | Represents an authenticated person in the system          |
| Group               | Collection of users or entities                           |
| Individual          | Single user or entity                                     |
| Activity Log        | Record of actions performed by users/admins               |
| Report              | Generated summary or analysis of data                     |
| Admin               | User with elevated privileges                             |

## Naming Conventions

- **Blade Views**: snake_case, grouped by feature (e.g., `auth/login.blade.php`)
- **Livewire Components**: PascalCase, grouped by feature (e.g., `Livewire/Auth/Login`)
- **Controllers**: PascalCase, suffixed with `Controller` (e.g., `UserController`)
- **Models**: Singular, PascalCase (e.g., `User`, `Group`)

---

_Add new terms and conventions below as the project evolves._ 