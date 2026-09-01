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

            @auth
                <li class="ml-2 flex items-center gap-3 border-l border-brand-light/30 pl-3">
                    <span class="text-sm text-brand-light">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md px-3 py-2 text-sm font-medium text-brand-pale transition-colors hover:text-brand-light">
                            Logout
                        </button>
                    </form>
                </li>
            @else
                <li class="ml-2">
                    <a
                        href="{{ route('login') }}"
                        class="rounded-md px-3 py-2 text-sm font-medium transition-colors
                            {{ request()->routeIs('login') ? 'bg-brand-primary text-brand-pale' : 'text-brand-pale hover:text-brand-light' }}"
                    >
                        Login
                    </a>
                </li>
            @endauth
        </ul>
    </nav>
</header>
