{{-- Sidebar Navigation Items --}}
@php
    $tab = $activeTab ?? '';
@endphp

{{-- Dashboard --}}
<li>
    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded transition {{ $tab === 'dashboard' ? 'bg-gray-200 font-semibold' : '' }}">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span x-show="open">Dashboard</span>
    </a>
</li>

{{-- My Trees Group --}}
@include('components.sidebar-group', [
    'label' => 'My Trees',
    'icon' => 'tree',
    'active' => in_array($tab, ['trees', 'create-tree', 'import-gedcom']),
    'children' => [
        ['route' => 'trees.index', 'label' => 'All Trees', 'active' => $tab === 'trees'],
        ['route' => 'trees.create', 'label' => 'Create New Tree', 'active' => $tab === 'create-tree'],
        ['route' => 'trees.import', 'label' => 'Import GEDCOM', 'active' => $tab === 'import-gedcom'],
    ],
])

{{-- Individuals Group --}}
@include('components.sidebar-group', [
    'label' => 'Individuals',
    'icon' => 'users',
    'active' => in_array($tab, ['individuals', 'add-individual', 'timeline']),
    'children' => [
        ['route' => 'individuals.index', 'label' => 'All Individuals', 'active' => $tab === 'individuals'],
        ['route' => 'individuals.create', 'label' => 'Add Individual', 'active' => $tab === 'add-individual'],
        ['route' => 'individuals.timeline', 'label' => 'Timeline View', 'active' => $tab === 'timeline'],
    ],
])

{{-- Groups Group --}}
@include('components.sidebar-group', [
    'label' => 'Groups',
    'icon' => 'group',
    'active' => in_array($tab, ['groups', 'create-group']),
    'children' => [
        ['route' => 'groups.index', 'label' => 'All Groups', 'active' => $tab === 'groups'],
        ['route' => 'groups.create', 'label' => 'Create Group', 'active' => $tab === 'create-group'],
    ],
])

{{-- Sources Group --}}
@include('components.sidebar-group', [
    'label' => 'Sources',
    'icon' => 'bookmark',
    'active' => in_array($tab, ['sources', 'add-source']),
    'children' => [
        ['route' => 'sources.index', 'label' => 'All Sources', 'active' => $tab === 'sources'],
        ['route' => 'sources.create', 'label' => 'Add Source', 'active' => $tab === 'add-source'],
    ],
])

{{-- Media Library Group --}}
@include('components.sidebar-group', [
    'label' => 'Media Library',
    'icon' => 'photo',
    'active' => in_array($tab, ['media', 'upload-media']),
    'children' => [
        ['route' => 'media.index', 'label' => 'All Media', 'active' => $tab === 'media'],
        ['route' => 'media.create', 'label' => 'Upload Media', 'active' => $tab === 'upload-media'],
    ],
])

{{-- Stories Group --}}
@include('components.sidebar-group', [
    'label' => 'Stories',
    'icon' => 'book-open',
    'active' => in_array($tab, ['stories', 'add-story']),
    'children' => [
        ['route' => 'stories.index', 'label' => 'All Stories', 'active' => $tab === 'stories'],
        ['route' => 'stories.create', 'label' => 'Add Story', 'active' => $tab === 'add-story'],
    ],
])

{{-- Events Group --}}
@include('components.sidebar-group', [
    'label' => 'Events',
    'icon' => 'calendar',
    'active' => in_array($tab, ['events', 'add-event', 'calendar']),
    'children' => [
        ['route' => 'events.index', 'label' => 'All Events', 'active' => $tab === 'events'],
        ['route' => 'events.create', 'label' => 'Add Event', 'active' => $tab === 'add-event'],
        ['route' => 'events.calendar', 'label' => 'Calendar View', 'active' => $tab === 'calendar'],
    ],
])

{{-- Community Group --}}
@include('components.sidebar-group', [
    'label' => 'Community',
    'icon' => 'users',
    'active' => in_array($tab, ['community', 'my-groups', 'forums']),
    'children' => [
        ['route' => 'community.directory', 'label' => 'Directory', 'active' => $tab === 'community'],
        ['route' => 'community.my-groups', 'label' => 'My Groups', 'active' => $tab === 'my-groups'],
        ['route' => 'community.forums', 'label' => 'Forums', 'active' => $tab === 'forums'],
    ],
])

{{-- Tools Group --}}
@include('components.sidebar-group', [
    'label' => 'Tools',
    'icon' => 'wrench',
    'active' => in_array($tab, ['templates', 'export', 'reports']),
    'children' => [
        ['route' => 'tools.templates', 'label' => 'Templates & Guides', 'active' => $tab === 'templates'],
        ['route' => 'tools.export', 'label' => 'Export/Import', 'active' => $tab === 'export'],
        ['route' => 'tools.reports', 'label' => 'Reports', 'active' => $tab === 'reports'],
    ],
])

{{-- Search --}}
<li>
    <a href="{{ route('search') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded transition {{ $tab === 'search' ? 'bg-gray-200 font-semibold' : '' }}">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <span x-show="open">Search</span>
    </a>
</li>

{{-- Admin (role-based) --}}
@can('admin')
@include('components.sidebar-group', [
    'label' => 'Admin',
    'icon' => 'shield',
    'active' => in_array($tab, ['users', 'logs', 'settings', 'notifications']),
    'children' => [
        ['route' => 'admin.users', 'label' => 'User Management', 'active' => $tab === 'users'],
        ['route' => 'admin.logs', 'label' => 'Activity Logs', 'active' => $tab === 'logs'],
        ['route' => 'admin.settings', 'label' => 'System Settings', 'active' => $tab === 'settings'],
        ['route' => 'admin.notifications', 'label' => 'Notifications', 'active' => $tab === 'notifications'],
    ],
])
@endcan

{{-- Help Group --}}
@include('components.sidebar-group', [
    'label' => 'Help',
    'icon' => 'question-mark-circle',
    'active' => in_array($tab, ['user-guide', 'tutorials', 'support']),
    'children' => [
        ['route' => 'help.user-guide', 'label' => 'User Guide', 'active' => $tab === 'user-guide'],
        ['route' => 'help.tutorials', 'label' => 'Tutorials', 'active' => $tab === 'tutorials'],
        ['route' => 'help.support', 'label' => 'Support', 'active' => $tab === 'support'],
    ],
])

{{-- Profile Group --}}
@include('components.sidebar-group', [
    'label' => 'Profile',
    'icon' => 'user-circle',
    'active' => in_array($tab, ['profile', 'preferences', 'logout']),
    'children' => [
        ['route' => 'profile.settings', 'label' => 'Profile Settings', 'active' => $tab === 'profile'],
        ['route' => 'profile.preferences', 'label' => 'Preferences', 'active' => $tab === 'preferences'],
        ['route' => 'logout', 'label' => 'Logout', 'active' => $tab === 'logout'],
    ],
]) 