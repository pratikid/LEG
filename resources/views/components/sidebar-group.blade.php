@props(['label', 'icon', 'active' => false, 'children' => []])
<li x-data="{ expanded: @js($active) }">
    <button
        @click="expanded = !expanded"
        class="flex items-center w-full px-4 py-2 text-gray-300 hover:bg-gray-800 rounded transition {{ $active ? 'bg-gray-800 font-semibold' : '' }}"
        :aria-expanded="expanded"
        aria-controls="sidebar-{{ Str::slug($label) }}-group"
        type="button"
    >
        {{-- Icon switcher --}}
        @switch($icon)
            @case('tree')
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 2v2m0 16v2m8-10h2m-18 0H2m15.07-7.07l1.42 1.42M4.93 19.07l1.42-1.42M19.07 19.07l-1.42-1.42M4.93 4.93L6.35 6.35M12 6a6 6 0 100 12 6 6 0 000-12z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @break
            @case('users')
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @break
            @case('group')
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @break
            @case('bookmark')
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 5v14l7-7 7 7V5a2 2 0 00-2-2H7a2 2 0 00-2 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @break
            @case('photo')
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="20" height="16" x="2" y="4" rx="2" ry="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="8.5" cy="11.5" r="2.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 15l-5-5L5 21" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @break
            @case('book-open')
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 20h9a1 1 0 001-1V5a1 1 0 00-1-1h-9m0 16H3a1 1 0 01-1-1V5a1 1 0 011-1h9m0 16V4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @break
            @case('calendar')
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2" ry="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 2v4M8 2v4m-6 4h20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @break
            @case('wrench')
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M14.7 6.3a8 8 0 11-2.4 2.4l2.1-2.1a2 2 0 112.8 2.8l-2.1 2.1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @break
            @case('shield')
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @break
            @case('question-mark-circle')
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.09 9a3 3 0 115.82 0c0 1.657-1.343 3-3 3s-3 1.343-3 3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="17" r="1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @break
            @case('user-circle')
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="7" r="4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5.5 21a8.38 8.38 0 0113 0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                @break
            @default
                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/></svg>
        @endswitch
        <span x-show="open">{{ $label }}</span>
        <svg x-show="open" class="ml-auto w-4 h-4 transform transition-transform duration-200 text-gray-400" :class="{ 'rotate-90': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/></svg>
    </button>
    <ul
        x-show="expanded"
        class="pl-8 space-y-1"
        x-cloak
        id="sidebar-{{ Str::slug($label) }}-group"
        role="menu"
        aria-label="{{ $label }} submenu"
    >
        @foreach($children as $child)
            <li>
                <a href="{{ route($child['route']) }}"
                   class="block px-4 py-2 text-gray-400 hover:bg-gray-800 rounded transition {{ $child['active'] ? 'bg-gray-800 font-semibold text-white' : '' }}"
                   role="menuitem"
                >
                    {{ $child['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</li> 