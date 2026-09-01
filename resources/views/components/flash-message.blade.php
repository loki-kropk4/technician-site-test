@if (session('success'))
    <div class="mt-4 rounded-md border border-brand-primary bg-brand-pale px-4 py-3 text-sm font-medium text-brand-darkest">
        {{ session('success') }}
    </div>
@endif
