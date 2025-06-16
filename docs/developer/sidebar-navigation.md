# Sidebar Navigation Implementation Guide

This document provides a comprehensive guide to the sidebar navigation system in the LEG genealogy application, including its implementation, customization, and extension capabilities.

## Architecture Overview

### Component Structure
```
resources/views/components/
├── sidebar/
│   ├── index.blade.php      # Main sidebar container
│   ├── items.blade.php      # Navigation items definition
│   ├── group.blade.php      # Collapsible group component
│   └── link.blade.php       # Individual link component
```

### Key Components
- **Sidebar Container**: Responsive layout with mobile support
- **Navigation Items**: Hierarchical menu structure
- **Group Component**: Collapsible sections with icons
- **Link Component**: Individual menu items with active states

## Implementation Details

### 1. Main Sidebar Component
```blade
<!-- resources/views/components/sidebar/index.blade.php -->
<div class="sidebar" x-data="{ open: true }">
    <div class="sidebar-header">
        <img src="{{ asset('images/logo.svg') }}" alt="LEG Logo">
        <button @click="open = !open" class="toggle-btn">
            <x-icon name="menu" />
        </button>
    </div>
    
    <nav class="sidebar-nav" :class="{ 'collapsed': !open }">
        <x-sidebar.items />
    </nav>
</div>
```

### 2. Navigation Items Definition
```blade
<!-- resources/views/components/sidebar/items.blade.php -->
<div class="nav-items">
    <!-- Dashboard -->
    <x-sidebar.link 
        route="dashboard" 
        icon="home" 
        label="Dashboard" 
        :active="$activeTab === 'dashboard'" 
    />

    <!-- Trees Group -->
    <x-sidebar.group 
        label="My Trees" 
        icon="tree" 
        :active="in_array($activeTab, ['trees', 'tree-create', 'tree-import'])"
    >
        <x-sidebar.link 
            route="trees.index" 
            label="All Trees" 
            :active="$activeTab === 'trees'" 
        />
        <x-sidebar.link 
            route="trees.create" 
            label="Create New Tree" 
            :active="$activeTab === 'tree-create'" 
        />
        <x-sidebar.link 
            route="trees.import" 
            label="Import GEDCOM" 
            :active="$activeTab === 'tree-import'" 
        />
    </x-sidebar.group>

    <!-- Individuals Group -->
    <x-sidebar.group 
        label="Individuals" 
        icon="users" 
        :active="in_array($activeTab, ['individuals', 'individual-create'])"
    >
        <x-sidebar.link 
            route="individuals.index" 
            label="All Individuals" 
            :active="$activeTab === 'individuals'" 
        />
        <x-sidebar.link 
            route="individuals.create" 
            label="Add Individual" 
            :active="$activeTab === 'individual-create'" 
        />
    </x-sidebar.group>
</div>
```

### 3. Group Component
```blade
<!-- resources/views/components/sidebar/group.blade.php -->
<div class="nav-group" x-data="{ open: true }">
    <button 
        class="group-header" 
        @click="open = !open"
        :class="{ 'active': $active }"
    >
        <x-icon :name="$icon" />
        <span>{{ $label }}</span>
        <x-icon name="chevron-down" class="transition-transform" :class="{ 'rotate-180': open }" />
    </button>
    
    <div class="group-content" x-show="open" x-collapse>
        {{ $slot }}
    </div>
</div>
```

### 4. Link Component
```blade
<!-- resources/views/components/sidebar/link.blade.php -->
<a 
    href="{{ route($route) }}" 
    class="nav-link {{ $active ? 'active' : '' }}"
>
    @if($icon)
        <x-icon :name="$icon" />
    @endif
    <span>{{ $label }}</span>
</a>
```

## Active State Management

### 1. Route to Tab Mapping
```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    View::composer('*', function ($view) {
        $view->with('activeTab', $this->getActiveTab());
    });
}

protected function getActiveTab()
{
    $route = request()->route()->getName();
    $tabMap = [
        'dashboard' => 'dashboard',
        'trees.index' => 'trees',
        'trees.create' => 'tree-create',
        'trees.import' => 'tree-import',
        'individuals.index' => 'individuals',
        'individuals.create' => 'individual-create',
        // Add new mappings here
    ];
    
    return $tabMap[$route] ?? null;
}
```

### 2. CSS Classes
```css
/* resources/css/sidebar.css */
.nav-link {
    @apply flex items-center px-4 py-2 text-gray-600 hover:bg-gray-100;
}

.nav-link.active {
    @apply bg-indigo-50 text-indigo-600;
}

.nav-group {
    @apply border-b border-gray-200;
}

.group-header {
    @apply flex items-center justify-between w-full px-4 py-2 text-gray-600 hover:bg-gray-100;
}

.group-header.active {
    @apply bg-indigo-50 text-indigo-600;
}
```

## Adding New Navigation Items

### 1. Define the Route
```php
// routes/web.php
Route::get('/tools/new-feature', [ToolController::class, 'newFeature'])
    ->name('tools.new-feature');
```

### 2. Add to Sidebar Items
```blade
<!-- resources/views/components/sidebar/items.blade.php -->
<x-sidebar.group 
    label="Tools" 
    icon="wrench" 
    :active="in_array($activeTab, ['tools', 'new-feature'])"
>
    <!-- Existing tools -->
    <x-sidebar.link 
        route="tools.new-feature" 
        label="New Feature" 
        :active="$activeTab === 'new-feature'" 
    />
</x-sidebar.group>
```

### 3. Update Tab Mapping
```php
// app/Providers/AppServiceProvider.php
protected function getActiveTab()
{
    $tabMap = [
        // Existing mappings
        'tools.new-feature' => 'new-feature',
    ];
    // ...
}
```

## Mobile Responsiveness

### 1. Toggle Button
```blade
<!-- resources/views/components/sidebar/index.blade.php -->
<button 
    class="md:hidden fixed top-4 left-4 z-50"
    @click="open = !open"
>
    <x-icon name="menu" />
</button>
```

### 2. Mobile Styles
```css
/* resources/css/sidebar.css */
@media (max-width: 768px) {
    .sidebar {
        @apply fixed inset-y-0 left-0 transform -translate-x-full transition-transform duration-200 ease-in-out;
    }
    
    .sidebar.open {
        @apply translate-x-0;
    }
}
```

## Best Practices

1. **Consistent Naming**
   - Use kebab-case for route names
   - Use camelCase for tab keys
   - Match route names with controller methods

2. **Group Organization**
   - Group related features logically
   - Limit groups to 5-7 items
   - Use clear, descriptive labels

3. **Icons**
   - Use consistent icon style
   - Choose intuitive icons
   - Maintain icon size consistency

4. **Performance**
   - Lazy load icons
   - Cache active tab state
   - Minimize DOM updates

## Troubleshooting

### Common Issues

1. **Active State Not Working**
   - Check route name in `$tabMap`
   - Verify route is registered
   - Clear route cache

2. **Mobile Menu Issues**
   - Check z-index values
   - Verify transform classes
   - Test touch events

3. **Styling Problems**
   - Check Tailwind classes
   - Verify CSS specificity
   - Test dark mode

## References

- [Laravel Blade Documentation](https://laravel.com/docs/blade)
- [Alpine.js Documentation](https://alpinejs.dev/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)

---

*This guide is regularly updated as the navigation system evolves.*

*Last updated: June 2025* 