@extends('layouts.site.app')

@section('content')
<section class="reg-shell relative left-1/2 right-1/2 -mx-[50vw] w-screen overflow-hidden bg-gradient-to-b from-sky-50 to-white py-16 dark:from-slate-950 dark:to-slate-950">
    <div class="mx-auto w-full max-w-6xl px-6">
        <div class="mb-10 reveal">
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-600 dark:text-sky-300">Seller Registration</p>
            <h1 class="mt-3 text-4xl font-extrabold text-slate-900 dark:text-white sm:text-5xl">Become a nextepSellers Partner</h1>
            <p class="mt-4 max-w-2xl text-base text-slate-600 dark:text-slate-300">
                Follow the onboarding flow below. Once approved, you can access your seller dashboard and begin selling immediately.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            <article class="reg-card reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="reg-step-dot mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700 dark:bg-sky-500/20 dark:text-sky-200">1</div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Create Account</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Complete the registration form with your legal name, phone number, and email address.</p>
            </article>

            <article class="reg-card reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="reg-step-dot mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700 dark:bg-sky-500/20 dark:text-sky-200">2</div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Verify Phone and Email</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Use the verification codes and email link to confirm your contact details.</p>
            </article>

            <article class="reg-card reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="reg-step-dot mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700 dark:bg-sky-500/20 dark:text-sky-200">3</div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Seller KYC Verification</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Upload your identity and business verification documents for compliance checks.</p>
            </article>

            <article class="reg-card reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="reg-step-dot mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700 dark:bg-sky-500/20 dark:text-sky-200">4</div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Review Stage</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Our team reviews your profile, verifies documents, and confirms your onboarding status.</p>
            </article>

            <article class="reg-card reveal rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="reg-step-dot mb-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700 dark:bg-sky-500/20 dark:text-sky-200">5</div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Start Selling</h2>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Once approved, access your seller portal, list products, and start earning commissions.</p>
            </article>
        </div>

        <div class="reg-cta mt-14 reveal overflow-hidden rounded-[2rem] border border-sky-200 bg-gradient-to-r from-sky-100 to-cyan-100 p-8 dark:border-sky-500/30 dark:from-sky-500/10 dark:to-cyan-500/10 sm:p-10">
            <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white sm:text-3xl">Start Your Seller Registration Today</h3>
            <p class="mt-3 max-w-2xl text-sm text-slate-700 dark:text-slate-300 sm:text-base">Join nextepSellers and begin the onboarding process. It only takes a few minutes to create your account and start Step 1.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('register') }}" class="inline-flex rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold uppercase tracking-wide text-white hover:bg-black dark:bg-white dark:text-slate-900">
                    Create Seller Account
                </a>
                <a href="{{ route('login') }}" class="inline-flex rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    Already Registered? Login
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
