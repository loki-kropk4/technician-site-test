<x-layout :title="config('app.name') . ' | Entries'">
    <section class="mx-auto max-w-xl px-4 py-10 sm:px-6 lg:px-8">
        <x-flash-message />

        <h1 class="text-2xl font-bold text-brand-darkest sm:text-3xl">
            Entry {{ $entry->entry_id }}: {{ $entry->name_unit }}
        </h1>

        <div class="mt-6 space-y-5">
            <div>
                <span class="block text-sm font-medium text-brand-darkest">Problem</span>
                <div class="mt-1 block w-full whitespace-pre-wrap break-words rounded-md border border-brand-light bg-brand-pale/50 px-3 py-2 text-sm text-brand-darkest">
                    {{ $entry->problem }}
                </div>
            </div>

            <div>
                <span class="block text-sm font-medium text-brand-darkest">Pictures</span>

                @if ($entry->pictures->isNotEmpty())
                    <div class="mt-1 grid grid-cols-3 gap-3 sm:grid-cols-5">
                        @foreach ($entry->pictures as $picture)
                            <a href="{{ $picture->url() }}" target="_blank" rel="noopener">
                                <img
                                    src="{{ $picture->url() }}"
                                    alt="{{ $entry->name_unit }} picture"
                                    class="h-24 w-full rounded-md border border-brand-light object-cover"
                                >
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="mt-1 flex min-h-32 flex-col items-center justify-center rounded-md border-2 border-dashed border-brand-light px-4 py-8 text-center">
                        <p class="text-sm text-brand-darkest/60">
                            The customer of this entry did not insert any pictures
                        </p>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-3 pt-2">
                @if ($isStaff)
                    <a
                        href="{{ route('entries.edit', $entry) }}"
                        aria-label="Edit entry {{ $entry->entry_id }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-brand-primary transition-colors hover:bg-brand-light"
                    >
                        <img src="{{ asset('icons/edit.svg') }}" alt="" class="h-4 w-4">
                    </a>
                    <form
                        method="POST"
                        action="{{ route('entries.destroy', $entry) }}"
                        onsubmit="return confirm('Delete entry {{ $entry->entry_id }}? This cannot be undone.');"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            aria-label="Delete entry {{ $entry->entry_id }}"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-brand-darkest transition-colors hover:bg-brand-primary"
                        >
                            <img src="{{ asset('icons/trash.svg') }}" alt="" class="h-4 w-4">
                        </button>
                    </form>
                @endif

                <a href="{{ route('entries.index') }}" class="text-sm font-medium text-brand-darkest/70 hover:text-brand-primary">
                    Back to Entries
                </a>
            </div>
        </div>
    </section>
</x-layout>
