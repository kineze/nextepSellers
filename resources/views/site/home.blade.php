@extends('layouts.site.app')

@section('content')
<section class="grid gap-10 lg:grid-cols-2 lg:items-center">
    <div>
        <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Nextep Platform</p>
        <h1 class="mt-4 text-4xl font-semibold leading-tight text-slate-900 dark:text-white sm:text-5xl">
            Run your fulfillment operations with clarity.
        </h1>
        <p class="mt-4 text-base text-slate-600 dark:text-slate-300">
            Centralize inventory, manage orders, and keep your team aligned from a single dashboard.
        </p>
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
            <a href="{{ route('login') }}" class="rounded-xl bg-slate-900 px-6 py-3 text-xs font-semibold uppercase tracking-wide text-white shadow-lg shadow-slate-900/25 hover:bg-black dark:bg-white dark:text-slate-900">
                Sign In
            </a>
            <a href="{{ url('/system-users') }}" class="rounded-xl border border-slate-200 px-6 py-3 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-900">
                View Users
            </a>
        </div>
    </div>

    <div class="rounded-3xl border border-slate-200/70 bg-white/80 p-6 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Live Snapshot</p>
                <h2 class="mt-2 text-xl font-semibold text-slate-900 dark:text-white">Today at a glance</h2>
            </div>
            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200">
                Active
            </span>
        </div>
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Orders</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">248</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Fulfilled</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">92%</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Inventory</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">1,482</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Alerts</p>
                <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">3</p>
            </div>
        </div>
    </div>
</section>
@endsection
