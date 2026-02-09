<nav class="w-full border-b border-slate-200/70 bg-white/80 backdrop-blur dark:border-slate-800/70 dark:bg-slate-950/70">
    <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-6 py-4">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <img src="{{ asset('assets/img/nextep-icon.webp') }}" alt="Nextep" class="h-8 w-8">
            <span class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-900 dark:text-white">Nextep</span>
        </a>

        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
                Login
            </a>
            <a href="{{ route('login') }}" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 hover:bg-black dark:bg-white dark:text-slate-900">
                Dashboard
            </a>
        </div>
    </div>
</nav>
