@if ($errors->any())
    <div class="mb-6 rounded-md border border-brand-primary bg-brand-pale px-4 py-3 text-sm text-brand-darkest">
        <p class="font-semibold">There were some problems with your submission:</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
