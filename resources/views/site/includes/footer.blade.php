<footer class="border-t border-slate-200/70 bg-white/80 backdrop-blur dark:border-slate-800/70 dark:bg-slate-950/70">
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-3 px-6 py-6 text-xs text-slate-500 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between">
        <span>© {{ date('Y') }} Nextep. All rights reserved.</span>
        <div class="flex items-center gap-4">
            <a href="{{ route('login') }}" class="hover:text-slate-900 dark:hover:text-white">Login</a>
            <a href="{{ url('/system-users') }}" class="hover:text-slate-900 dark:hover:text-white">System Users</a>
        </div>
    </div>
</footer>
