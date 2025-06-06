# Sidebar Navigation Structure & Extensibility

This document explains the structure of the sidebar navigation in the LEG genealogy app, how it is managed, and how to add new routes/features for future contributors.

---

## Overview

- The sidebar is implemented as a reusable Blade component: `resources/views/components/sidebar.blade.php`.
- Navigation items and groups are defined in `resources/views/components/sidebar-items.blade.php` and `sidebar-group.blade.php`.
- Sidebar visibility and active tab highlighting are managed automatically using a Laravel View Composer (see `AppServiceProvider`).
- The sidebar is included in the main layout (`resources/views/layouts/app.blade.php`) and is **not** shown on authentication, onboarding, error, or public/marketing pages.

---

## Sidebar Structure

- **Component:** `<x-sidebar />` (see `sidebar.blade.php`)
- **Navigation Items:** Defined in `sidebar-items.blade.php` using a combination of single links and grouped children via `sidebar-group.blade.php`.
- **Active Tab:** The `$activeTab` variable is injected into all views by a View Composer, based on the current route name.
- **Collapsible Groups:** Groups (e.g., "My Trees", "Individuals") are collapsible and highlight when any child is active.
- **Logout Button:** Always present at the bottom, implemented as a secure POST form.

### Example Structure (Simplified)

```
<x-sidebar />
└── sidebar-items.blade.php
    ├── Dashboard (single link)
    ├── My Trees (group)
    │   ├── All Trees
    │   ├── Create New Tree
    │   └── Import GEDCOM
    ├── Individuals (group)
    │   ├── All Individuals
    │   ├── Add Individual
    │   └── Timeline View
    └── ... (other groups and links)
```

---

## How Active Tab Highlighting Works

- The View Composer in `AppServiceProvider` maps route names to sidebar tab keys.
- When a route is matched, the corresponding tab is highlighted automatically.
- No need to manually set `$activeTab` in controllers or views.

---

## How to Add a New Sidebar Route/Feature

1. **Add Your Route**
   - Define your new route in `routes/web.php` with a unique route name (e.g., `'tools.new-feature'`).

2. **Create Controller & View**
   - Add a controller method and a Blade view for your new feature as needed.

3. **Update the Sidebar Items**
   - Edit `resources/views/components/sidebar-items.blade.php`:
     - Add a new link or group entry in the appropriate place.
     - Example for a new tool:
       ```blade
       @include('components.sidebar-group', [
           'label' => 'Tools',
           'icon' => 'wrench',
           'active' => in_array($tab, ['templates', 'export', 'reports', 'new-tool']),
           'children' => [
               // ...existing children
               ['route' => 'tools.new-feature', 'label' => 'New Tool', 'active' => $tab === 'new-tool'],
           ],
       ])
       ```

4. **Map the Route to a Tab Key**
   - In `app/Providers/AppServiceProvider.php`, update the `$tabMap` array in the `boot()` method:
     ```php
     'tools.new-feature' => 'new-tool',
     ```
   - The key is your route name, the value is the tab key used in the sidebar.

5. **Done!**
   - The sidebar will now automatically highlight your new tab when the route is active.
   - No controller changes are needed for tab highlighting.

---

## Best Practices

- **Keep the `$tabMap` in sync** with sidebar items for accurate highlighting.
- **Group related features** under logical sidebar groups for clarity.
- **Use descriptive tab keys** (e.g., `'add-individual'`, `'import-gedcom'`).
- **Test** your new route to ensure the sidebar highlights as expected.

---

## References

- Sidebar Blade: `resources/views/components/sidebar.blade.php`
- Sidebar Items: `resources/views/components/sidebar-items.blade.php`
- Sidebar Groups: `resources/views/components/sidebar-group.blade.php`
- View Composer: `app/Providers/AppServiceProvider.php`
- Main Layout: `resources/views/layouts/app.blade.php`

---

For further questions, see the code comments or contact the maintainers. 