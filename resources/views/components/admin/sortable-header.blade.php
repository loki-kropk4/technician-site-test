@props(['column', 'label', 'sort', 'direction'])
@php
    $isActive = $sort === $column;
    $nextDirection = $isActive && $direction === 'asc' ? 'desc' : 'asc';
@endphp
<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-pale">
    <a
        href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDirection, 'page' => 1]) }}"
        class="inline-flex items-center gap-1 transition-colors hover:text-brand-light"
    >
        {{ $label }}
        <img
            src="{{ asset('icons/sort.svg') }}"
            alt=""
            class="h-3 w-3 transition-transform
                {{ $isActive && $direction === 'desc' ? 'rotate-180' : '' }}
                {{ $isActive ? 'opacity-100' : 'opacity-40' }}"
        >
    </a>
</th>
