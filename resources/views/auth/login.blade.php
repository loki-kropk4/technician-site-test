<x-layout :title="config('app.name') . ' | Login'">
    <section class="mx-auto max-w-md px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex justify-center">
            <x-application-logo class="h-16 w-16" />
        </div>

        <h1 class="mt-6 text-center text-2xl font-bold text-brand-darkest sm:text-3xl">
            Sign in
        </h1>

        <x-flash-message />
        <x-error-summary />

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <label for="login" class="block text-sm font-medium text-brand-darkest">Name or Email</label>
                <input
                    type="text"
                    name="login"
                    id="login"
                    required
                    autofocus
                    value="{{ old('login') }}"
                    class="mt-1 block w-full rounded-md border border-brand-light px-3 py-2 text-sm text-brand-darkest focus:border-brand-primary focus:outline-none"
                >
                @error('login')
                    <p class="mt-1 text-xs text-brand-primary">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-brand-darkest">Password</label>
                <div class="relative mt-1">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        class="block w-full rounded-md border border-brand-light px-3 py-2 pr-10 text-sm text-brand-darkest focus:border-brand-primary focus:outline-none"
                    >
                    <button
                        type="button"
                        data-password-toggle="#password"
                        data-icon-visible="{{ asset('icons/eye.svg') }}"
                        data-icon-hidden="{{ asset('icons/eye-slash.svg') }}"
                        aria-label="Show password"
                        class="absolute inset-y-0 right-0 flex items-center px-3"
                    >
                        <img src="{{ asset('icons/eye-slash.svg') }}" alt="Show password" class="h-4 w-4">
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-brand-primary">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between gap-3 pt-2">
                <a href="{{ route('register') }}" class="text-sm font-medium text-brand-darkest/70 hover:text-brand-primary">
                    Register
                </a>
                <button
                    type="submit"
                    class="rounded-md bg-brand-primary px-4 py-2 text-sm font-medium text-brand-pale transition-colors hover:bg-brand-light"
                >
                    Sign in
                </button>
            </div>
        </form>
    </section>
</x-layout>
