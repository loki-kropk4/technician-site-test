<x-layout :title="config('app.name') . ' | Admin Panel'">
    <section class="mx-auto max-w-xl px-4 py-10 sm:px-6 lg:px-8">
        <x-error-summary />

        <h1 class="text-2xl font-bold text-brand-darkest sm:text-3xl">
            {{ $user ? "Editing {$user->name} Record" : 'Making New User Record' }}
        </h1>

        <form
            method="POST"
            action="{{ $user ? route('admin.users.update', $user) : route('admin.users.store') }}"
            class="mt-6 space-y-5"
        >
            @csrf
            @if ($user)
                @method('PUT')
            @endif

            <div>
                <label for="id" class="block text-sm font-medium text-brand-darkest">ID</label>
                <input
                    type="text"
                    id="id"
                    value="{{ $user->id ?? 'Auto-generated' }}"
                    disabled
                    class="mt-1 block w-full rounded-md border border-brand-light bg-brand-pale/50 px-3 py-2 text-sm text-brand-darkest/60"
                >
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-brand-darkest">Name</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    required
                    value="{{ old('name', $user->name ?? '') }}"
                    class="mt-1 block w-full rounded-md border border-brand-light px-3 py-2 text-sm text-brand-darkest focus:border-brand-primary focus:outline-none"
                >
                @error('name')
                    <p class="mt-1 text-xs text-brand-primary">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-brand-darkest">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    required
                    value="{{ old('email', $user->email ?? '') }}"
                    class="mt-1 block w-full rounded-md border border-brand-light px-3 py-2 text-sm text-brand-darkest focus:border-brand-primary focus:outline-none"
                >
                @error('email')
                    <p class="mt-1 text-xs text-brand-primary">{{ $message }}</p>
                @enderror
            </div>

            @if ($user)
                <div>
                    <label for="old_password" class="block text-sm font-medium text-brand-darkest">Old Password</label>
                    <input
                        type="password"
                        name="old_password"
                        id="old_password"
                        class="mt-1 block w-full rounded-md border border-brand-light px-3 py-2 text-sm text-brand-darkest focus:border-brand-primary focus:outline-none"
                    >
                    <p class="mt-1 text-xs text-brand-darkest/60">Only required if you're setting a new password.</p>
                    @error('old_password')
                        <p class="mt-1 text-xs text-brand-primary">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="new_password" class="block text-sm font-medium text-brand-darkest">New Password</label>
                    <input
                        type="password"
                        name="new_password"
                        id="new_password"
                        class="mt-1 block w-full rounded-md border border-brand-light px-3 py-2 text-sm text-brand-darkest focus:border-brand-primary focus:outline-none"
                    >
                    <p class="mt-1 text-xs text-brand-darkest/60">Leave blank to keep the current password.</p>
                    @error('new_password')
                        <p class="mt-1 text-xs text-brand-primary">{{ $message }}</p>
                    @enderror
                </div>
            @else
                <div>
                    <label for="password" class="block text-sm font-medium text-brand-darkest">Password</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        class="mt-1 block w-full rounded-md border border-brand-light px-3 py-2 text-sm text-brand-darkest focus:border-brand-primary focus:outline-none"
                    >
                    @error('password')
                        <p class="mt-1 text-xs text-brand-primary">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="flex items-center gap-3 pt-2">
                <button
                    type="submit"
                    class="rounded-md bg-brand-primary px-4 py-2 text-sm font-medium text-brand-pale transition-colors hover:bg-brand-light"
                >
                    {{ $user ? 'Save Changes' : 'Create Technician' }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-brand-darkest/70 hover:text-brand-primary">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</x-layout>
