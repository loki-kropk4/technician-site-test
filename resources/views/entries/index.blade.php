<?php use App\Enums\UserRole; ?>
<x-layout :title="config('app.name') . ' | Entries'">
    <section class="w-full px-4 py-10 sm:px-6">
        <h1 class="text-2xl font-bold text-brand-darkest sm:text-3xl">List of Entries</h1>

        <x-flash-message />

        @php
            $isStaff = in_array(auth()->user()->role, [UserRole::Technician, UserRole::Admin], true);
        @endphp

        @if (auth()->user()->role === UserRole::Customer)
            <div class="mt-6 flex justify-end">
                <a
                    href="{{ route('entries.create') }}"
                    class="rounded-md bg-brand-primary px-4 py-2 text-sm font-medium text-brand-pale transition-colors hover:bg-brand-light"
                >
                    Make Entry
                </a>
            </div>
        @endif

        <div class="mt-4 overflow-x-auto rounded-md shadow">
            <table class="w-full divide-y divide-brand-light">
                <thead class="bg-brand-darkest">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-pale">Entry ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-pale">Customer</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-pale">Unit</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-pale">Date &amp; Time</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-brand-pale">Status</th>
                        @if ($isStaff)
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-brand-pale">
                                Actions
                            </th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-light bg-brand-pale">
                    @forelse ($entries as $entry)
                        <tr>
                            <td class="px-4 py-3 text-sm text-brand-darkest">{{ $entry->entry_id }}</td>
                            <td class="px-4 py-3 text-sm text-brand-darkest">{{ $entry->customer->name }}</td>
                            <td class="px-4 py-3 text-sm text-brand-darkest">{{ $entry->name_unit }}</td>
                            <td class="px-4 py-3 text-sm text-brand-darkest">
                                {{ $entry->entry_date->format('M j, Y') }} &middot; {{ $entry->entry_time->format('H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-brand-darkest">{{ $entry->entryStatus->status }}</td>
                            @if ($isStaff)
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route('entries.edit', $entry) }}"
                                            aria-label="Edit entry {{ $entry->entry_id }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-brand-primary transition-colors hover:bg-brand-light"
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
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-brand-darkest transition-colors hover:bg-brand-primary"
                                            >
                                                <img src="{{ asset('icons/trash.svg') }}" alt="" class="h-4 w-4">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isStaff ? 6 : 5 }}" class="px-4 py-6 text-center text-sm text-brand-darkest/70">
                                No entries yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $entries->links() }}</div>
    </section>
</x-layout>
