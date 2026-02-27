@php
    $isLanding = request()->routeIs('index');
@endphp

<nav class="{{ $isLanding ? 'sticky top-0 z-50 w-full border-b border-white/30 bg-white/35 backdrop-blur-md dark:border-slate-800/40 dark:bg-slate-950/35' : 'sticky top-0 z-50 w-full border-b border-slate-200/70 bg-white/80 backdrop-blur-md dark:border-slate-800/70 dark:bg-slate-950/70' }}">
    <div class="mx-auto flex w-full max-w-screen-2xl items-center justify-between px-6 py-4">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <img src="{{ asset('assets/img/nextep-icon.webp') }}" alt="Nextep" class="h-8 w-8">
            <span class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-900 dark:text-white">Nextep</span>
        </a>

        <div class="hidden items-center gap-6 md:flex">
            <a href="{{ url('/#how') }}"
                class="text-xs font-semibold uppercase tracking-wide text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">
                Process
            </a>
            <a href="{{ url('/#features') }}"
                class="text-xs font-semibold uppercase tracking-wide text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">
                Features
            </a>
            <a href="{{ url('/#stories') }}"
                class="text-xs font-semibold uppercase tracking-wide text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">
                Stories
            </a>
            <a href="{{ route('learningMaterials') }}"
                class="text-xs font-semibold uppercase tracking-wide text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">
                Learning Materials
            </a>
            <div class="flex items-center rounded-full border border-slate-200 bg-slate-100 p-1 dark:border-slate-700 dark:bg-slate-800">
                <button type="button" onclick="updateSiteTheme('light')" class="rounded-full px-2 py-1 text-[12px] font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Light mode">
                    <i class="fas fa-sun"></i>
                </button>
                <button type="button" onclick="updateSiteTheme('comfort')" class="rounded-full px-2 py-1 text-[12px] font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Comfort mode">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" onclick="updateSiteTheme('dark')" class="rounded-full px-2 py-1 text-[12px] font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Dark mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
            @guest
                <a href="{{ route('login') }}"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
                    Login
                </a>
            @endguest
            <a href="{{ route('sellerRegistration') }}"
                class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-sky-700 hover:bg-sky-100 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200 dark:hover:bg-sky-500/20">
                Seller Registration
            </a>
            @auth
                <a href="{{ route('dashboard') }}"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 hover:bg-black dark:bg-white dark:text-slate-900">
                    Dashboard
                </a>
            @endauth
        </div>

        <button id="mobileNavToggle" type="button"
            class="inline-flex items-center justify-center rounded-lg border border-slate-300 p-2 text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900 md:hidden"
            aria-label="Open navigation menu" aria-controls="mobileNavSidebar" aria-expanded="false">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
</nav>

<div id="mobileNavOverlay" class="fixed inset-0 z-40 hidden bg-slate-900/40 md:hidden"></div>

<aside id="mobileNavSidebar"
    class="fixed right-0 top-0 z-50 h-full w-72 translate-x-full bg-white p-6 shadow-2xl transition-transform duration-300 dark:bg-slate-950 md:hidden"
    aria-hidden="true">
    <div class="mb-8 flex items-center justify-between">
        <span class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-900 dark:text-white">Menu</span>
        <button id="mobileNavClose" type="button"
            class="inline-flex items-center justify-center rounded-lg border border-slate-300 p-2 text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900"
            aria-label="Close navigation menu">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="space-y-3">
        <a href="{{ url('/#how') }}"
            class="block rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
            Process
        </a>
        <a href="{{ url('/#features') }}"
            class="block rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
            Features
        </a>
        <a href="{{ url('/#stories') }}"
            class="block rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
            Stories
        </a>
        <a href="{{ route('learningMaterials') }}"
            class="block rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
            Learning Materials
        </a>
        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-slate-700 dark:bg-slate-800">
            <button type="button" onclick="updateSiteTheme('light')" class="flex-1 rounded-lg px-2 py-2 text-xs font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Light mode">
                <i class="fas fa-sun"></i>
            </button>
            <button type="button" onclick="updateSiteTheme('comfort')" class="flex-1 rounded-lg px-2 py-2 text-xs font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Comfort mode">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" onclick="updateSiteTheme('dark')" class="flex-1 rounded-lg px-2 py-2 text-xs font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Dark mode">
                <i class="fas fa-moon"></i>
            </button>
        </div>
        @guest
            <a href="{{ route('login') }}"
                class="block rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
                Login
            </a>
        @endguest
        <a href="{{ route('sellerRegistration') }}"
            class="block rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-sky-700 hover:bg-sky-100 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200 dark:hover:bg-sky-500/20">
            Seller Registration
        </a>
        @auth
            <a href="{{ route('dashboard') }}"
                class="block rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 hover:bg-black dark:bg-white dark:text-slate-900">
                Dashboard
            </a>
        @endauth
    </div>
</aside>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('mobileNavToggle');
            const closeBtn = document.getElementById('mobileNavClose');
            const overlay = document.getElementById('mobileNavOverlay');
            const sidebar = document.getElementById('mobileNavSidebar');

            if (!toggleBtn || !closeBtn || !overlay || !sidebar) return;

            const openMenu = () => {
                sidebar.classList.remove('translate-x-full');
                overlay.classList.remove('hidden');
                sidebar.setAttribute('aria-hidden', 'false');
                toggleBtn.setAttribute('aria-expanded', 'true');
                document.body.classList.add('overflow-hidden');
            };

            const closeMenu = () => {
                sidebar.classList.add('translate-x-full');
                overlay.classList.add('hidden');
                sidebar.setAttribute('aria-hidden', 'true');
                toggleBtn.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('overflow-hidden');
            };

            toggleBtn.addEventListener('click', openMenu);
            closeBtn.addEventListener('click', closeMenu);
            overlay.addEventListener('click', closeMenu);
            sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeMenu();
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) closeMenu();
            });

            const THEME_KEY = 'nextep-theme-pref';
            const themeButtons = Array.from(document.querySelectorAll('[onclick*="updateSiteTheme"]'));

            const applyTheme = (theme) => {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.remove('theme-preload-dark', 'theme-preload-comfort');
                document.body.classList.remove('theme-light', 'theme-dark', 'theme-comfort');

                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.body.classList.add('theme-dark');
                } else if (theme === 'comfort') {
                    document.body.classList.add('theme-comfort');
                } else {
                    document.body.classList.add('theme-light');
                }

                themeButtons.forEach((btn) => {
                    const isActive = btn.getAttribute('onclick')?.includes(`'${theme}'`);
                    btn.classList.toggle('bg-white', !!isActive);
                    btn.classList.toggle('dark:bg-slate-700', !!isActive);
                    btn.classList.toggle('text-slate-900', !!isActive);
                    btn.classList.toggle('dark:text-white', !!isActive);
                });
            };

            window.updateSiteTheme = (theme) => {
                applyTheme(theme);
                localStorage.setItem(THEME_KEY, theme);
            };

            applyTheme(localStorage.getItem(THEME_KEY) || 'light');
        });
    </script>
@endpush
