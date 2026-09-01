<x-layout :title="config('app.name') . ' | Home'">
    <section class="mx-auto flex max-w-3xl flex-col items-center px-4 py-16 text-center sm:px-6 lg:px-8">
        <img
            src="{{ Storage::url('main_page/logo.svg') }}"
            alt="{{ config('app.name') }} logo"
            class="h-24 w-24 object-contain"
        >

        <h1 class="mt-6 text-3xl font-bold text-brand-darkest sm:text-4xl">
            Welcome to {{ config('app.name') }}
        </h1>

        <p class="mt-4 text-base leading-relaxed text-brand-darkest">
            We're your trusted technician computer site — expert diagnostics, repair, and
            maintenance for desktops, laptops, and everything in between. Whatever's wrong with
            your device, our team is ready to get it running again.
        </p>
    </section>
</x-layout>
