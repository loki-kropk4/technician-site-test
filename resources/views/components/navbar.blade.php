@php
    $navItems = [
        ['label' => 'Home', 'route' => 'home'],
        // Add more links here as pages are added, e.g.:
        // ['label' => 'Services', 'route' => 'services'],
        // ['label' => 'About', 'route' => 'about'],
    ];
@endphp

<header class="fixed inset-x-0 top-0 z-50 h-16 bg-brand-darkest shadow">
    <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="text-lg font-semibold text-brand-pale">
            {{ config('app.name') }}
        </a>

        <ul class="flex items-center gap-2">
            @foreach ($navItems as $item)
                <li>
                    <a
                        href="{{ route($item['route']) }}"
                        class="rounded-md px-3 py-2 text-sm font-medium transition-colors
                            {{ request()->routeIs($item['route'])
                                ? 'bg-brand-primary text-brand-pale'
                                : 'text-brand-pale hover:text-brand-light' }}"
                    >
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</header>
