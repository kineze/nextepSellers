@extends('layouts.admin.app')

@section('content')

<div class="w-full p-3 mx-auto">
    @php
        $adminName = auth()->user()?->name ?: 'Admin';
    @endphp

    <div class="mb-4 rounded-2xl border border-slate-200/70 bg-white/85 p-5 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/80">
        <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-slate-400 dark:text-slate-500">Admin Dashboard</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl">
            Welcome back, {{ $adminName }}
        </h1>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-300">
            Here is your overview for managing sellers, orders, inventory, and platform operations.
        </p>
    </div>

    @can('View Reports')
        <dashboard></dashboard>
    @endcan

</div>

@endsection
