<x-layout :title="config('app.name') . ' | Entries'">
    <section class="mx-auto max-w-xl px-4 py-10 sm:px-6 lg:px-8">
        <x-error-summary />

        <h1 class="text-2xl font-bold text-brand-darkest sm:text-3xl">
            {{ $entry ? 'Editing Entry' : 'Creating New Entry' }}
        </h1>

        <form
            method="POST"
            action="{{ $entry ? route('entries.update', $entry) : route('entries.store') }}"
            enctype="multipart/form-data"
            class="mt-6 space-y-5"
        >
            @csrf
            @if ($entry)
                @method('PUT')
            @endif

            @if ($entry)
                <div>
                    <label for="entry_id" class="block text-sm font-medium text-brand-darkest">Entry ID</label>
                    <input
                        type="text"
                        id="entry_id"
                        value="{{ $entry->entry_id }}"
                        disabled
                        class="mt-1 block w-full rounded-md border border-brand-light bg-brand-pale/50 px-3 py-2 text-sm text-brand-darkest/60"
                    >
                </div>
            @endif

            <div>
                <label for="name_unit" class="block text-sm font-medium text-brand-darkest">Unit Name</label>
                <input
                    type="text"
                    name="name_unit"
                    id="name_unit"
                    required
                    maxlength="40"
                    placeholder="ROG Flow Z13"
                    value="{{ old('name_unit', $entry->name_unit ?? '') }}"
                    class="mt-1 block w-full rounded-md border border-brand-light px-3 py-2 text-sm text-brand-darkest placeholder:text-brand-darkest/40 focus:border-brand-primary focus:outline-none"
                >
                @error('name_unit')
                    <p class="mt-1 text-xs text-brand-primary">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="problem" class="block text-sm font-medium text-brand-darkest">Describe the problem</label>
                <textarea
                    name="problem"
                    id="problem"
                    required
                    rows="4"
                    placeholder="My laptop doesn't display anything when I boot it up"
                    class="mt-1 block w-full rounded-md border border-brand-light px-3 py-2 text-sm text-brand-darkest placeholder:text-brand-darkest/40 focus:border-brand-primary focus:outline-none"
                >{{ old('problem', $entry->problem ?? '') }}</textarea>
                @error('problem')
                    <p class="mt-1 text-xs text-brand-primary">{{ $message }}</p>
                @enderror
            </div>

            @if ($entry)
                <div>
                    <label for="status" class="block text-sm font-medium text-brand-darkest">Status</label>
                    <select
                        name="status"
                        id="status"
                        required
                        class="mt-1 block w-full rounded-md border border-brand-light px-3 py-2 text-sm text-brand-darkest focus:border-brand-primary focus:outline-none"
                    >
                        @foreach ($statuses as $status)
                            <option value="{{ $status->status_id }}" @selected(old('status', $entry->status) === $status->status_id)>
                                {{ $status->status }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-xs text-brand-primary">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            @php
                $existingPictures = $entry
                    ? $entry->pictures->map(fn ($picture) => [
                        'id' => $picture->id,
                        'url' => $picture->url(),
                        'name' => $picture->file_name,
                        'size' => $picture->size(),
                    ])
                    : collect();
            @endphp

            <div>
                <span class="block text-sm font-medium text-brand-darkest">Pictures</span>

                <div
                    id="picture-dropzone"
                    data-existing='@json($existingPictures)'
                    class="mt-1 flex min-h-32 cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed border-brand-light px-4 py-8 text-center transition-colors hover:border-brand-primary"
                >
                    <div id="picture-dropzone-placeholder" class="flex flex-col items-center">
                        <p class="text-sm text-brand-darkest">Drag &amp; drop pictures here, or click to browse</p>
                        <p class="mt-1 text-xs text-brand-darkest/60">Up to 5 pictures, 10 MB total</p>
                    </div>

                    <div id="picture-dropzone-previews" class="hidden w-full grid-cols-3 gap-3 sm:grid-cols-5"></div>

                    <input
                        type="file"
                        id="pictures"
                        name="images[]"
                        accept="image/*,.jpg,.jpeg,.png,.gif,.webp,.bmp"
                        multiple
                        class="hidden"
                    >
                </div>

                @if ($entry)
                    <p class="mt-1 text-xs text-brand-darkest/60">
                        Removing an existing picture here only takes effect once you press Save Changes.
                    </p>
                @endif

                <div id="picture-dropzone-removed-inputs"></div>

                <p id="picture-dropzone-warning" class="mt-1 hidden text-xs text-brand-primary"></p>

                @error('images')
                    <p class="mt-1 text-xs text-brand-primary">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button
                    type="submit"
                    class="rounded-md bg-brand-primary px-4 py-2 text-sm font-medium text-brand-pale transition-colors hover:bg-brand-light"
                >
                    {{ $entry ? 'Save Changes' : 'Submit Entry' }}
                </button>
                <a href="{{ route('entries.index') }}" class="text-sm font-medium text-brand-darkest/70 hover:text-brand-primary">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</x-layout>
