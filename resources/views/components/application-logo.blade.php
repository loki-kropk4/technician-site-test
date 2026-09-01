@props(['class' => 'h-9 w-9'])

<img
    src="{{ Storage::url('main_page/logo.svg') }}"
    alt="{{ config('app.name') }} logo"
    {{ $attributes->merge(['class' => $class.' object-contain']) }}
>
