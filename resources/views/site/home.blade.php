@extends('layouts.site.app')

@section('content')
<section class="relative left-1/2 right-1/2 -mx-[50vw] w-screen -mt-20 overflow-hidden bg-gradient-to-br from-sky-100 via-cyan-100 to-emerald-100 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900 sm:pt-20">
    <div class="pointer-events-none absolute -left-16 -top-10 h-72 w-72 rounded-full bg-fuchsia-300/35 blur-3xl dark:bg-fuchsia-600/20"></div>
    <div class="pointer-events-none absolute -right-12 top-28 h-80 w-80 rounded-full bg-cyan-300/35 blur-3xl dark:bg-cyan-500/20"></div>
    <div class="pointer-events-none absolute bottom-10 left-1/2 h-72 w-72 -translate-x-1/2 rounded-full bg-emerald-300/35 blur-3xl dark:bg-emerald-500/15"></div>
    <div class="pointer-events-none absolute inset-0 opacity-20 [background:radial-gradient(circle_at_20%_30%,#38bdf8_0,transparent_40%),radial-gradient(circle_at_80%_20%,#f472b6_0,transparent_35%),radial-gradient(circle_at_50%_85%,#34d399_0,transparent_35%)]"></div>

    <div class="mx-auto w-full max-w-screen-2xl px-6 pb-16 pt-28 sm:pt-32">
        <div class="grid items-center gap-10 lg:grid-cols-2">
            <div class="reveal">
                <div class="inline-flex items-center gap-2 rounded-full border border-sky-200/70 bg-white/80 px-4 py-2 text-xs font-bold uppercase tracking-wide text-sky-700 shadow-sm backdrop-blur dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-500 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-sky-500"></span>
                    </span>
                    Join 2,000+ Active Sellers
                </div>

                <h1 class="mt-6 max-w-2xl text-5xl font-extrabold leading-tight text-slate-900 dark:text-white sm:text-6xl lg:text-7xl">
                    Smart Selling,
                    <span class="bg-gradient-to-r from-sky-600 via-cyan-600 to-emerald-600 bg-clip-text text-transparent dark:from-sky-400 dark:via-cyan-300 dark:to-emerald-300">Made Simple</span>
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-slate-700 dark:text-slate-300 sm:text-xl">
                    Explore products, learn with practical video guides, and grow faster with reliable fulfillment and weekly payouts.
                </p>

                <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <a href="{{ route('sellerRegistration') }}" class="rounded-2xl bg-slate-900 px-7 py-3 text-sm font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 hover:-translate-y-0.5 hover:bg-black dark:bg-white dark:text-slate-900">
                        Join as Seller
                    </a>
                    <a href="{{ route('learnMore') }}" class="rounded-2xl border border-slate-200 bg-white/90 px-7 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 shadow-sm hover:bg-white dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                        Learn More
                    </a>
                </div>

                <div class="mt-8 grid max-w-xl gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/60 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Active Sellers</p>
                        <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">2,000+</p>
                    </div>
                    <div class="rounded-2xl border border-white/60 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Avg. Growth</p>
                        <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">3.2x</p>
                    </div>
                    <div class="rounded-2xl border border-white/60 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Weekly Payouts</p>
                        <p class="mt-1 text-xl font-extrabold text-slate-900 dark:text-white">On Time</p>
                    </div>
                </div>
            </div>

            <div class="reveal relative mx-auto w-full max-w-xl lg:max-w-none">
                <div class="grid gap-4 sm:grid-cols-2">
                    <article class="overflow-hidden rounded-3xl border border-white/70 bg-white/80 shadow-xl backdrop-blur dark:border-slate-700 dark:bg-slate-900/70 sm:col-span-2">
                        <img src="{{asset('/assets/img/conversion-growth.webp')}}" alt="Seller reviewing product catalog on laptop" class="h-56 w-full object-cover sm:h-64" loading="eager">
                    </article>
                    <article class="overflow-hidden rounded-3xl border border-white/70 bg-white/80 shadow-xl backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
                        <img src="{{asset('/assets/img/accelerate_sales_growth.webp')}}" alt="Packed orders ready for dispatch" class="h-44 w-full object-cover sm:h-52" loading="lazy">
                    </article>
                    <article class="overflow-hidden rounded-3xl border border-white/70 bg-white/80 shadow-xl backdrop-blur dark:border-slate-700 dark:bg-slate-900/70">
                        <img src="{{asset('/assets/img/counting money.webp')}}" alt="Business growth analytics dashboard" class="h-44 w-full object-cover sm:h-52" loading="lazy">
                    </article>
                </div>

                <div class="absolute -bottom-4 -left-4 rounded-2xl border border-emerald-200 bg-white/90 px-4 py-3 shadow-lg backdrop-blur dark:border-emerald-500/30 dark:bg-slate-900/80">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">Conversion Lift</p>
                    <p class="mt-1 text-lg font-extrabold text-slate-900 dark:text-white">+24%</p>
                </div>
                <div class="absolute -right-4 top-6 rounded-2xl border border-sky-200 bg-white/90 px-4 py-3 shadow-lg backdrop-blur dark:border-sky-500/30 dark:bg-slate-900/80">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-sky-700 dark:text-sky-300">Repeat Customers</p>
                    <p class="mt-1 text-lg font-extrabold text-slate-900 dark:text-white">41%</p>
                </div>
            </div>
        </div>

        <section id="growth" class="pt-14">
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

            <div class="reveal mt-8 rounded-3xl border border-sky-200 bg-white/70 p-7 shadow-sm backdrop-blur dark:border-sky-500/30 dark:bg-sky-500/10">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700 dark:text-sky-300">Growth Plan</p>
                <p class="mt-2 text-sm text-slate-700 dark:text-slate-200">Launch with high-intent products, optimize weekly using dashboard insights, and reinvest in top-performing categories to compound growth each month.</p>
            </div>
        </section>
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

<section id="learning" class="py-20">
    <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="reveal">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700 dark:text-cyan-300">Learning Materials</p>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">
                {{ $defaultLearningBlock?->title ?? 'Seller Learning Hub' }}
            </h2>
            <p class="mt-3 max-w-2xl text-sm text-slate-600 dark:text-slate-300">
                {{ $defaultLearningBlock?->description ?: 'Watch short training videos and improve your selling workflow.' }}
            </p>
        </div>
        <a href="{{ route('learningMaterials') }}" class="reveal inline-flex items-center gap-2 rounded-2xl border border-cyan-200 bg-cyan-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-cyan-700 hover:bg-cyan-100 dark:border-cyan-500/30 dark:bg-cyan-500/10 dark:text-cyan-200 dark:hover:bg-cyan-500/20">
            View More
            <i class="fas fa-arrow-right text-[11px]"></i>
        </a>
    </div>

    @php
        $homeLearningVideos = $defaultLearningBlock?->videos?->take(4) ?? collect();
    @endphp

    @if($homeLearningVideos->isEmpty())
        <div class="reveal rounded-2xl border border-dashed border-slate-300 px-5 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            Learning videos are coming soon.
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($homeLearningVideos as $video)
                <article class="reveal overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="aspect-video overflow-hidden border-b border-slate-200 bg-slate-100 dark:border-slate-800 dark:bg-slate-950">
                        <iframe
                            src="{{ $video->embed_url ?: $video->embedded_link }}"
                            class="h-full w-full"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen
                        ></iframe>
                    </div>
                    <div class="p-4">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ $video->title }}</h3>
                        <p class="mt-2 line-clamp-3 text-xs text-slate-600 dark:text-slate-300">
                            {{ $video->description ?: 'No description available.' }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
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
           <article class="overflow-hidden rounded-3xl border border-white/70 bg-white/80 shadow-xl backdrop-blur dark:border-slate-700 dark:bg-slate-900/70 sm:col-span-2">
                <img src="{{asset('/assets/img/conversion-growth.webp')}}" alt="Seller reviewing product catalog on laptop" class="h-56 w-full object-cover sm:h-96" loading="eager">
            </article>
        </div>
    </div>
</section>

<section id="stories" class="py-20">
    <div class="reveal rounded-[2rem] border border-sky-200 bg-gradient-to-br from-sky-50 via-white to-cyan-50 p-8 shadow-sm dark:border-sky-500/30 dark:from-slate-900 dark:via-slate-900 dark:to-slate-950 sm:p-10">
        <div class="mb-10 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-700 dark:text-sky-300">Seller Stories</p>
            <h2 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">Real Seller Results</h2>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-600 dark:text-slate-300">Hear how sellers are scaling faster using our catalog, fulfillment support, and learning resources.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <article class="reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-md shadow-sky-100/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                <div class="flex items-center gap-3">
                    <img src="https://i.pravatar.cc/120?u=1" alt="Alex Rivera" class="h-14 w-14 rounded-2xl object-cover">
                    <div>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">Alex Rivera</p>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Platinum Seller</p>
                    </div>
                </div>
                <p class="mt-4 text-sm italic text-slate-600 dark:text-slate-300">"The platform is intuitive. I doubled my income in three months selling tech accessories."</p>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Revenue up 2x in 90 days</p>
            </article>

            <article class="reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-md shadow-sky-100/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                <div class="flex items-center gap-3">
                    <img src="https://i.pravatar.cc/120?u=2" alt="Priya K" class="h-14 w-14 rounded-2xl object-cover">
                    <div>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">Priya K.</p>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Global Partner</p>
                    </div>
                </div>
                <p class="mt-4 text-sm italic text-slate-600 dark:text-slate-300">"Finally a network with quality products and less operational noise."</p>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Fulfillment issues down 60%</p>
            </article>

            <article class="reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-md shadow-sky-100/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                <div class="flex items-center gap-3">
                    <img src="https://i.pravatar.cc/120?u=3" alt="Rashan Silva" class="h-14 w-14 rounded-2xl object-cover">
                    <div>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">Rashan Silva</p>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Growth Seller</p>
                    </div>
                </div>
                <p class="mt-4 text-sm italic text-slate-600 dark:text-slate-300">"The learning videos gave me a clear system. I now close more orders every week."</p>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Orders up 48% QoQ</p>
            </article>

            <article class="reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-md shadow-sky-100/60 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                <div class="flex items-center gap-3">
                    <img src="https://i.pravatar.cc/120?u=4" alt="Maya Fernando" class="h-14 w-14 rounded-2xl object-cover">
                    <div>
                        <p class="text-sm font-bold text-slate-900 dark:text-white">Maya Fernando</p>
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pro Seller</p>
                    </div>
                </div>
                <p class="mt-4 text-sm italic text-slate-600 dark:text-slate-300">"I started part-time and now run this as my main income stream with stable payouts."</p>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Weekly payouts on schedule</p>
            </article>
        </div>
    </div>
</section>

<section class="relative left-1/2 right-1/2 -mx-[50vw] w-screen overflow-hidden bg-gradient-to-r from-sky-600 to-cyan-500 py-20">
    <div class="mx-auto w-full max-w-screen-2xl px-6">
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
