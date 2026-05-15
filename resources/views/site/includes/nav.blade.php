@php
    $isLanding = request()->routeIs('index');
    $currentUser = auth()->user();
@endphp

<nav id="siteNav"
    data-is-landing="{{ $isLanding ? 'true' : 'false' }}"
    class="{{ $isLanding
        ? 'sticky top-0 z-50 w-full border-b border-transparent bg-transparent backdrop-blur-md transition-colors duration-300 dark:border-transparent dark:bg-transparent'
        : 'sticky top-0 z-50 w-full border-b border-slate-200/70 bg-white/80 backdrop-blur-md transition-colors duration-300 dark:border-slate-800/70 dark:bg-slate-950/70' }}">
    <div class="mx-auto grid w-full max-w-screen-2xl grid-cols-[1fr_auto] items-center gap-4 px-6 py-4 md:grid-cols-[1fr_auto_1fr]">
        <a href="{{ url('/') }}" class="flex items-center gap-3 justify-self-start">
            <img src="{{ asset('assets/img/nextep-logo.webp') }}" alt="Nextep" class="h-9 w-auto dark:hidden">
            <img src="{{ asset('assets/img/nextep-logo-dark.webp') }}" alt="Nextep" class="hidden h-9 w-auto dark:block">
        </a>

        <div class="hidden items-center justify-center gap-6 md:flex">
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
        </div>

        <div class="hidden items-center justify-end gap-3 md:flex">
            <div class="flex items-center rounded-full border border-slate-200 bg-slate-100 p-1 dark:border-slate-700 dark:bg-slate-800">
                <button type="button" onclick="updateSiteTheme('light')" class="rounded-full px-2 py-1 text-[12px] font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Light mode">
                    <i class="fas fa-sun"></i>
                </button>
                <button type="button" onclick="updateSiteTheme('dark')" class="rounded-full px-2 py-1 text-[12px] font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Dark mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
                @guest
                    <a href="{{ route('login') }}"
                        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-900"
                        aria-label="Login">
                        <i class="fas fa-user" aria-hidden="true"></i>
                        <span class="pointer-events-none absolute right-0 top-full mt-2 whitespace-nowrap rounded-lg bg-slate-900 px-2 py-1 text-[11px] font-semibold text-white opacity-0 shadow-lg transition group-hover:opacity-100 group-focus:opacity-100 dark:bg-white dark:text-slate-900">
                            Login
                        </span>
                </a>
                <a href="{{ route('sellerRegistration') }}"
                    class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-sky-700 hover:bg-sky-100 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200 dark:hover:bg-sky-500/20">
                    Become a Seller
                </a>
            @endguest
            @auth
                <div class="relative">
                    <button id="siteAccountToggle" type="button"
                        class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-left shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800"
                        aria-expanded="false"
                        aria-controls="siteAccountMenu">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-bold uppercase text-white dark:bg-white dark:text-slate-900">
                            {{ strtoupper(substr($currentUser?->name ?? $currentUser?->email ?? 'U', 0, 1)) }}
                        </span>
                        <span class="min-w-0">
                            <span class="block max-w-36 truncate text-xs font-semibold text-slate-900 dark:text-white">{{ $currentUser?->name ?? 'User' }}</span>
                            <span class="block max-w-36 truncate text-[10px] text-slate-500 dark:text-slate-400">{{ $currentUser?->email }}</span>
                        </span>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                    </button>

                    <div id="siteAccountMenu"
                        class="absolute right-0 mt-2 hidden w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $currentUser?->name ?? 'User' }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $currentUser?->email }}</p>
                        </div>
                        <div class="p-2">
                            <a href="{{ route('dashboard') }}"
                                class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">
                                <i class="fas fa-gauge-high"></i>
                                Go to Dashboard
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-500/10">
                                    <i class="fas fa-right-from-bracket"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endauth
        </div>

        <button id="mobileNavToggle" type="button"
            class="inline-flex items-center justify-center justify-self-end rounded-lg border border-slate-300 p-2 text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900 md:hidden"
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
            <button type="button" onclick="updateSiteTheme('dark')" class="flex-1 rounded-lg px-2 py-2 text-xs font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Dark mode">
                <i class="fas fa-moon"></i>
            </button>
        </div>
        @guest
            <a href="{{ route('login') }}"
                class="block rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
                Login
            </a>
            <a href="{{ route('sellerRegistration') }}"
                class="block rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold uppercase tracking-wide text-sky-700 hover:bg-sky-100 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200 dark:hover:bg-sky-500/20">
                Become a Seller
            </a>
        @endguest
        @auth
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $currentUser?->name ?? 'User' }}</p>
                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $currentUser?->email }}</p>
                <div class="mt-4 grid gap-2">
                    <a href="{{ route('dashboard') }}"
                        class="block rounded-xl bg-slate-900 px-4 py-3 text-center text-sm font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 hover:bg-black dark:bg-white dark:text-slate-900">
                        Go to Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold uppercase tracking-wide text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:bg-slate-950 dark:text-rose-300 dark:hover:bg-rose-500/10">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</aside>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nav = document.getElementById('siteNav');
            const toggleBtn = document.getElementById('mobileNavToggle');
            const closeBtn = document.getElementById('mobileNavClose');
            const overlay = document.getElementById('mobileNavOverlay');
            const sidebar = document.getElementById('mobileNavSidebar');
            const accountToggle = document.getElementById('siteAccountToggle');
            const accountMenu = document.getElementById('siteAccountMenu');

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

            if (accountToggle && accountMenu) {
                const closeAccountMenu = () => {
                    accountMenu.classList.add('hidden');
                    accountToggle.setAttribute('aria-expanded', 'false');
                };

                accountToggle.addEventListener('click', function(event) {
                    event.stopPropagation();
                    const isOpen = !accountMenu.classList.contains('hidden');
                    accountMenu.classList.toggle('hidden', isOpen);
                    accountToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                });

                document.addEventListener('click', function(event) {
                    if (!accountMenu.contains(event.target) && !accountToggle.contains(event.target)) {
                        closeAccountMenu();
                    }
                });

                document.addEventListener('keydown', function(event) {
                    if (event.key === 'Escape') closeAccountMenu();
                });
            }

            const isLanding = nav?.dataset.isLanding === 'true';

            if (nav && isLanding) {
                const applyNavTransparency = () => {
                    const isTransparent = window.scrollY <= 2;

                    nav.classList.toggle('bg-transparent', isTransparent);
                    nav.classList.toggle('border-transparent', isTransparent);
                    nav.classList.toggle('dark:bg-transparent', isTransparent);
                    nav.classList.toggle('dark:border-transparent', isTransparent);

                    nav.classList.toggle('bg-white/80', !isTransparent);
                    nav.classList.toggle('border-slate-200/70', !isTransparent);
                    nav.classList.toggle('dark:bg-slate-950/70', !isTransparent);
                    nav.classList.toggle('dark:border-slate-800/70', !isTransparent);

                    nav.dataset.transparent = isTransparent ? 'true' : 'false';
                };

                applyNavTransparency();
                window.addEventListener('scroll', applyNavTransparency, { passive: true });
            }

            const THEME_KEY = 'nextep-theme-pref';
            const themeButtons = Array.from(document.querySelectorAll('[onclick*="updateSiteTheme"]'));

            const applyTheme = (theme) => {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.remove('theme-preload-dark');
                document.body.classList.remove('theme-light', 'theme-dark');

                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                    document.body.classList.add('theme-dark');
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
