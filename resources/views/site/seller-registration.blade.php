@extends('layouts.site.app')

@section('content')
<section class="relative left-1/2 right-1/2 -mx-[50vw] w-screen bg-slate-50 py-14 dark:bg-slate-950">
    <div class="mx-auto w-full max-w-6xl px-6">
        <div class="reveal mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-600 dark:text-sky-300">Why Become a Seller</p>
            <h2 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">Build Consistent Growth With Lower Operational Stress</h2>
            <p class="mt-4 max-w-3xl text-base text-slate-600 dark:text-slate-300">
                nextepSellers helps you grow faster with better product focus, structured support, and a clear onboarding path.
                Instead of managing everything alone, you get a system that supports sustainable scaling.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <article class="reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">High-Demand Catalog</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Sell from a curated product range selected for demand, margin potential, and repeat customer value.
                </p>
            </article>

            <article class="reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">
                    <i class="fas fa-gears"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Operational Structure</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Reduce execution friction through a guided process, document-ready onboarding, and smoother handoff to review.
                </p>
            </article>

            <article class="reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200">
                    <i class="fas fa-arrow-trend-up"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Scale With Confidence</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Start lean, validate quickly, and scale progressively with clearer decisions based on performance.
                </p>
            </article>
        </div>
    </div>
</section>

<section class="relative left-1/2 right-1/2 mb-12 -mx-[50vw] w-screen bg-white dark:bg-black">
    <div class="mx-auto w-full max-w-6xl px-6">
        <seller-registration-form
            submit-url="{{ route('sellerRegistration.store') }}"
            email-otp-send-url="{{ route('sellerRegistration.emailOtp.send') }}"
            email-otp-verify-url="{{ route('sellerRegistration.emailOtp.verify') }}"
            csrf-token="{{ csrf_token() }}"
        ></seller-registration-form>
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
