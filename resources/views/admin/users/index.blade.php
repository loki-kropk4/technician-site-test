<x-layout :title="config('app.name') . ' | Admin Panel'">
    <section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-brand-darkest sm:text-3xl">
            {{ $adminName ? "Welcome back, {$adminName}" : 'Admin Panel' }}
        </h1>
        <p class="mt-1 text-sm text-brand-darkest/70">Manage user accounts.</p>

        <div class="mt-6 flex justify-end">
            <a
                href="{{ route('admin.users.create') }}"
                class="rounded-md bg-brand-primary px-4 py-2 text-sm font-medium text-brand-pale transition-colors hover:bg-brand-light"
            >
                + New Technician
            </a>
        </div>

        <x-flash-message />

        <div class="mt-4 overflow-x-auto rounded-md shadow">
            <table class="min-w-full divide-y divide-brand-light">
                <thead class="bg-brand-darkest">
                    <tr>
                        <x-admin.sortable-header column="id" label="ID" :sort="$sort" :direction="$direction" />
                        <x-admin.sortable-header column="name" label="Name" :sort="$sort" :direction="$direction" />
                        <x-admin.sortable-header column="email" label="Email" :sort="$sort" :direction="$direction" />
                        <x-admin.sortable-header column="role" label="Role" :sort="$sort" :direction="$direction" />
                        <x-admin.sortable-header column="created_at" label="Joined" :sort="$sort" :direction="$direction" />
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-brand-pale">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-light bg-brand-pale">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3 text-sm text-brand-darkest">{{ $user->id }}</td>
                            <td class="px-4 py-3 text-sm text-brand-darkest">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-sm text-brand-darkest">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-sm capitalize text-brand-darkest">{{ $user->role->value }}</td>
                            <td class="px-4 py-3 text-sm text-brand-darkest">{{ $user->created_at->format('M j, Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.users.edit', $user) }}"
                                        aria-label="Edit {{ $user->name }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-brand-primary transition-colors hover:bg-brand-light"
                                    >
                                        <img src="{{ asset('icons/edit.svg') }}" alt="" class="h-4 w-4">
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.destroy', $user) }}"
                                        onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            aria-label="Delete {{ $user->name }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-brand-darkest transition-colors hover:bg-brand-primary"
                                        >
                                            <img src="{{ asset('icons/trash.svg') }}" alt="" class="h-4 w-4">
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-brand-darkest/70">
                                No users yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </section>
</x-layout>
