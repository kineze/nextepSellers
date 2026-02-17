@extends('layouts.site.app')

@section('content')
<section class="relative left-1/2 right-1/2 -mx-[50vw] w-screen overflow-hidden bg-gradient-to-b from-sky-50 via-cyan-50 to-white pt-16 dark:from-slate-950 dark:via-slate-950 dark:to-slate-950 sm:pt-20">
    <div class="pointer-events-none absolute -left-10 -top-10 h-56 w-56 rounded-full bg-sky-300/30 blur-3xl dark:bg-sky-500/20"></div>
    <div class="pointer-events-none absolute -right-12 top-1/2 h-64 w-64 rounded-full bg-cyan-300/20 blur-3xl dark:bg-cyan-500/20"></div>
    <div class="pointer-events-none absolute left-1/2 top-24 h-64 w-64 -translate-x-1/2 rounded-full bg-emerald-300/20 blur-3xl dark:bg-emerald-500/10"></div>

    <div class="mx-auto w-full max-w-6xl px-6">
        <div class="reveal text-center">
            <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-4 py-2 text-xs font-bold uppercase tracking-wide text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-500 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-sky-500"></span>
                </span>
                Join 2,000+ Active Sellers
            </div>

            <h1 class="mx-auto mt-6 max-w-5xl text-5xl font-extrabold leading-tight text-slate-900 dark:text-white sm:text-6xl lg:text-7xl">
                Smart Selling,
                <span class="text-sky-600 dark:text-sky-400">Made Simple</span>
            </h1>
            <p class="mx-auto mt-6 max-w-3xl text-lg leading-relaxed text-slate-600 dark:text-slate-300 sm:text-xl">
                Access a curated portfolio of high-demand products. We handle operations and shipping, so you can focus on growing your income with confidence.
            </p>

            <div class="mt-10 flex flex-col justify-center gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('sellerRegistration') }}" class="rounded-2xl bg-slate-900 px-7 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 hover:-translate-y-0.5 hover:bg-black dark:bg-white dark:text-slate-900">
                    Join as Seller
                </a>
                <a href="{{ route('learnMore') }}" class="rounded-2xl border border-slate-200 bg-white px-7 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    Learn More
                </a>
            </div>

            <section id="growth" class="py-20">

                <div class="grid gap-6 lg:grid-cols-3">
                    <article class="reveal rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-300">
                            <i class="fas fa-chart-column"></i>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Conversion Lift</p>
                        <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">+24%</p>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Product-market alignment and fast shipping improve close rates.</p>
                    </article>

                    <article class="reveal rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300">
                            <i class="fas fa-rotate"></i>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Repeat Customers</p>
                        <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">41%</p>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Reliable fulfillment drives trust and repeat purchases.</p>
                    </article>

                    <article class="reveal rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">
                            <i class="fas fa-arrow-trend-up"></i>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Revenue Growth</p>
                        <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">+3.2x</p>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Sellers scale faster with proven catalog strategy and support.</p>
                    </article>
                </div>

                <div class="reveal mt-8 rounded-3xl border border-sky-200 bg-sky-50 p-7 dark:border-sky-500/30 dark:bg-sky-500/10">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700 dark:text-sky-300">Growth Plan</p>
                    <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">Launch with high-intent products, optimize weekly using dashboard insights, and reinvest in top-performing categories to compound growth each month.</p>
                </div>
            </section>
        </div>
    </div>
</section>

<section id="how" class="py-12">
    <div class="mb-12 text-center reveal">
        <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">How It Works</h2>
        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Three simple steps to start selling professionally.</p>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        <article class="reveal rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-300">
                <i class="fas fa-user-plus"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">1. Register</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Apply quickly and join the curated nextepSellers network.</p>
        </article>

        <article class="reveal rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-300">
                <i class="fas fa-box-open"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">2. Pick Products</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Choose from high-converting products backed by fulfillment support.</p>
        </article>

        <article class="reveal rounded-3xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300">
                <i class="fas fa-wallet"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">3. Earn Weekly</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Sell across channels and receive reliable weekly commissions.</p>
        </article>
    </div>
</section>

<section id="features" class="py-20">
    <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
        <div class="reveal">
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">Platform Features</h2>
            <p class="mt-4 text-base leading-relaxed text-slate-600 dark:text-slate-300">
                nextepSellers gives you the complete seller stack in one place: product selection, operational support, growth tracking, and transparent payouts. You focus on selling while the platform handles the heavy lifting behind the scenes.
            </p>

            <div class="mt-7 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Premium Catalog</p>
                    <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">Curated high-demand products ready to sell.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Operational Support</p>
                    <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">Fulfillment and shipping support to reduce friction.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Growth Analytics</p>
                    <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">Actionable performance metrics for weekly optimization.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Transparent Payouts</p>
                    <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">Track earnings clearly with predictable commissions.</p>
                </div>
            </div>
        </div>

        <div class="reveal rounded-[2rem] border border-sky-200 bg-white/80 p-5 shadow-sm backdrop-blur dark:border-sky-500/30 dark:bg-slate-900/70">
            <svg class="h-auto w-full" viewBox="0 0 560 360" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect x="16" y="24" width="528" height="312" rx="24" fill="#E0F2FE" />
                <rect x="46" y="56" width="468" height="248" rx="16" fill="white" />
                <rect x="76" y="92" width="116" height="18" rx="9" fill="#BAE6FD" />
                <rect x="76" y="126" width="88" height="10" rx="5" fill="#E2E8F0" />
                <rect x="76" y="146" width="118" height="10" rx="5" fill="#E2E8F0" />
                <rect x="76" y="166" width="68" height="10" rx="5" fill="#E2E8F0" />
                <rect x="76" y="202" width="180" height="58" rx="14" fill="#F0F9FF" />
                <rect x="278" y="92" width="206" height="168" rx="14" fill="#F8FAFC" />
                <path d="M302 220L340 180L374 196L416 144L458 164" stroke="#0284C7" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" />
                <circle cx="458" cy="164" r="9" fill="#0EA5E9" />
                <rect x="302" y="242" width="122" height="10" rx="5" fill="#E2E8F0" />
                <rect x="302" y="260" width="162" height="10" rx="5" fill="#E2E8F0" />
                <circle cx="504" cy="74" r="16" fill="#FDE68A" />
                <path d="M497 74H511M504 67V81" stroke="#92400E" stroke-width="3" stroke-linecap="round" />
            </svg>
        </div>
    </div>
</section>



<section id="stories" class="py-20">
    <h2 class="mb-10 text-center text-3xl font-extrabold text-slate-900 dark:text-white">Seller Success Stories</h2>
    <div class="grid gap-6 md:grid-cols-2">
        <article class="reveal flex gap-4 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <img src="https://i.pravatar.cc/120?u=1" alt="Alex Rivera" class="h-16 w-16 rounded-2xl object-cover">
            <div>
                <p class="text-sm italic text-slate-600 dark:text-slate-300">"The platform is intuitive. I doubled my income in three months selling tech accessories."</p>
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Alex Rivera, Platinum Seller</p>
            </div>
        </article>
        <article class="reveal flex gap-4 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <img src="https://i.pravatar.cc/120?u=2" alt="Priya K" class="h-16 w-16 rounded-2xl object-cover">
            <div>
                <p class="text-sm italic text-slate-600 dark:text-slate-300">"Finally a network with quality products and less operational noise."</p>
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Priya K., Global Partner</p>
            </div>
        </article>
    </div>
</section>

<section class="relative left-1/2 right-1/2 -mx-[50vw] w-screen overflow-hidden bg-gradient-to-r from-sky-600 to-cyan-500 py-20">
    <div class="mx-auto w-full max-w-6xl px-6">
        <div class="reveal relative overflow-hidden rounded-[2.5rem] border border-white/20 bg-white/10 p-10 text-center text-white backdrop-blur-sm sm:p-14">
            <h2 class="text-3xl font-extrabold sm:text-4xl">Ready to Take the Next Step?</h2>
            <p class="mx-auto mt-4 max-w-xl text-sm text-sky-100 sm:text-base">Join a growing community of high-performing sellers and start scaling your business today.</p>
            <a href="{{ route('sellerRegistration') }}" class="mt-8 inline-flex rounded-2xl bg-white px-8 py-3 text-sm font-semibold uppercase tracking-wide text-sky-700">
                Start Seller Registration
            </a>
            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/15"></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.15
            });

            elements.forEach((el) => observer.observe(el));
        });
    </script>
@endpush
