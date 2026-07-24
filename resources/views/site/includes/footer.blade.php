<footer class="relative border-t border-slate-200/70 bg-white/80 backdrop-blur-md dark:border-slate-800/70 dark:bg-slate-950/70">
    <div class="mx-auto grid w-full max-w-screen-2xl gap-10 px-6 py-12 md:grid-cols-2 lg:grid-cols-4">
        <div>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                <img src="{{ asset('assets/img/nextep-icon.webp') }}" alt="Nextep" class="h-8 w-8">
                <span class="text-base font-bold text-slate-900 dark:text-white">nextepSellers</span>
            </a>
            <p class="mt-4 max-w-xs text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                Professional seller onboarding, quality products, and streamlined operations in one platform.
            </p>
            <div class="mt-5 flex items-center gap-3">
                <a href="#" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 hover:border-sky-300 hover:text-sky-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-sky-500/40 dark:hover:text-sky-300" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 hover:border-sky-300 hover:text-sky-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-sky-500/40 dark:hover:text-sky-300" aria-label="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <a href="#" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-500 hover:border-sky-300 hover:text-sky-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-sky-500/40 dark:hover:text-sky-300" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
            </div>
        </div>

        <div>
            <h4 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Navigation</h4>
            <ul class="mt-4 space-y-3 text-sm">
                <li><a href="{{ url('/#how') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">How It Works</a></li>
                <li><a href="{{ url('/#features') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Features</a></li>
                <li><a href="{{ url('/#stories') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Success Stories</a></li>
            </ul>
        </div>

        <div>
            <h4 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Seller</h4>
            <ul class="mt-4 space-y-3 text-sm">
                <li><a href="{{ route('sellerRegistration') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Registration Steps</a></li>
                <li><a href="{{ route('sellerRegistration') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Create Account</a></li>
                @guest
                    <li><a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Login</a></li>
                @endguest
                @auth
                    <li><a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Dashboard</a></li>
                @endauth
            </ul>
        </div>

        <div>
            <h4 class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Company</h4>
            <ul class="mt-4 space-y-3 text-sm">
                <li><a href="{{ route('privacyPolicy') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Privacy Policy</a></li>
                <li><a href="{{ route('about') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">About</a></li>
                <li><a href="{{ route('contact') }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Contact</a></li>
                <li><a href="mailto:support@nextepsellers.com" class="text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">support@nextepsellers.com</a></li>
            </ul>
        </div>
    </div>

    <div class="border-t border-slate-200/70 dark:border-slate-800/70">
        <div class="mx-auto flex w-full max-w-screen-2xl flex-col gap-2 px-6 py-5 text-xs text-slate-500 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between">
            <span>© {{ date('Y') }} nextepSellers. All rights reserved.</span>
            <span>Built for modern sellers.</span>
        </div>
    </div>
</footer>
