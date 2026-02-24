@extends('layouts.site.app')

@section('content')
<section class="relative left-1/2 right-1/2 -mx-[50vw] w-screen overflow-hidden bg-gradient-to-b from-sky-50 to-white py-16 dark:from-slate-950 dark:to-slate-950">
    <div class="mx-auto w-full max-w-screen-2xl px-6">
        <div class="reveal max-w-3xl">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-600 dark:text-sky-300">Learn More</p>
            <h1 class="mt-3 text-4xl font-extrabold text-slate-900 dark:text-white sm:text-5xl">How nextepSellers Helps You Grow</h1>
            <p class="mt-4 text-base leading-relaxed text-slate-600 dark:text-slate-300">
                nextepSellers is built for people who want to focus on sales while reducing operational overhead. From curated products to fulfillment support, the platform is designed to help you scale consistently.
            </p>
        </div>

        <div class="mt-10 grid gap-6 md:grid-cols-2">
            <article class="reveal rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">What You Get</h2>
                <ul class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                    <li>Curated product catalog with high-demand offers</li>
                    <li>Operational support for inventory and shipping</li>
                    <li>Transparent earnings and growth tracking</li>
                    <li>Structured onboarding and seller support</li>
                </ul>
            </article>

            <article class="reveal rounded-3xl border border-slate-200 bg-white p-7 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Why It Matters</h2>
                <p class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                    Most sellers lose momentum because operations consume time. With nextepSellers, you can spend more effort on customer acquisition, repeat sales, and building a predictable income stream.
                </p>
            </article>
        </div>

        <div class="mt-10 reveal rounded-3xl border border-sky-200 bg-sky-50 p-8 dark:border-sky-500/30 dark:bg-sky-500/10">
            <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white">Ready to start?</h3>
            <p class="mt-3 text-sm text-slate-700 dark:text-slate-300">View the onboarding flow and create your seller account when you are ready.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('sellerRegistration') }}" class="inline-flex rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900">
                    View Seller Registration
                </a>
                <a href="{{ route('register') }}" class="inline-flex rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    Create Account
                </a>
            </div>
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
