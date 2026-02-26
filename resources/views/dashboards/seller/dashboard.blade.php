@extends('layouts.seller.app')

@section('content')
@php
  $stats = $sellerStats ?? [];
  $levelLabel = !empty($stats['level_name'])
      ? $stats['level_name'] . (!empty($stats['level_no']) ? ' (L' . $stats['level_no'] . ')' : '')
      : 'Not Assigned';
  $nextLevelLabel = !empty($stats['next_level_name'])
      ? $stats['next_level_name'] . (!empty($stats['next_level_no']) ? ' (L' . $stats['next_level_no'] . ')' : '')
      : null;
@endphp
<div class="space-y-6">
  <div class="rounded-3xl border border-slate-200/70 bg-gradient-to-br from-cyan-50 via-white to-blue-50 p-7 shadow-sm backdrop-blur dark:border-slate-800/70 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
    <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-blue-600 dark:text-blue-300">Seller Dashboard</p>
    <h1 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">Welcome back, {{ auth()->user()->name }}</h1>
    <p class="mt-3 max-w-3xl text-sm text-slate-600 dark:text-slate-300">
      Manage your selling activities from one place. Track inventory readiness, monitor orders, and continue optimization.
    </p>

    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Current Level</p>
        <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">{{ $levelLabel }}</p>
      </div>

      <div class="rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Current Points</p>
        <p class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">{{ number_format((int) ($stats['current_points'] ?? 0)) }}</p>
      </div>

      <div class="rounded-2xl border border-amber-200/80 bg-amber-50/80 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Pending Points (Ongoing)</p>
        <p class="mt-2 text-2xl font-extrabold text-amber-900 dark:text-amber-100">{{ number_format((int) ($stats['pending_points'] ?? 0)) }}</p>
        <p class="mt-1 text-[11px] text-amber-700/80 dark:text-amber-200/80">From draft to shipped orders</p>
      </div>

      <div class="rounded-2xl border border-emerald-200/80 bg-emerald-50/80 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Projected Points</p>
        <p class="mt-2 text-2xl font-extrabold text-emerald-900 dark:text-emerald-100">{{ number_format((int) ($stats['projected_points'] ?? 0)) }}</p>
        @if($nextLevelLabel)
          <p class="mt-1 text-[11px] text-emerald-700/80 dark:text-emerald-200/80">
            {{ number_format((int) ($stats['points_to_next_level'] ?? 0)) }} more for {{ $nextLevelLabel }}
          </p>
        @else
          <p class="mt-1 text-[11px] text-emerald-700/80 dark:text-emerald-200/80">Top level reached</p>
        @endif
      </div>
    </div>

    @if(!empty($stats['next_level_points']))
      @php
        $projected = (int) ($stats['projected_points'] ?? 0);
        $target = max(1, (int) ($stats['next_level_points'] ?? 1));
        $progressPct = min(100, max(0, (int) floor(($projected / $target) * 100)));
      @endphp
      <div class="mt-3 rounded-2xl border border-slate-200/80 bg-white/90 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
        <div class="flex items-center justify-between text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
          <span>Projected Progress</span>
          <span>{{ $progressPct }}%</span>
        </div>
        <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
          <div
            class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 transition-all duration-300"
            style="width: {{ $progressPct }}%;"
          ></div>
        </div>
        <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
          {{ number_format($projected) }} / {{ number_format($target) }} points toward {{ $nextLevelLabel }}
        </p>
      </div>
    @endif

    <p class="mt-4 text-[11px] text-slate-500 dark:text-slate-400">
      Point conversion: LKR {{ number_format((float) ($stats['lkr_per_point'] ?? 100), 0) }} = 1 point
    </p>
  </div>

  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <a href="{{ route('sellerProducts') }}" class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:hover:border-blue-500/40">
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Products</p>
      <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">Product List</p>
      <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">Browse active catalog items as modern product cards.</p>
    </a>

    <a href="{{ route('sellerOrders') }}" class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:hover:border-blue-500/40">
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Orders</p>
      <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">My Orders</p>
      <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">Track order statuses and fulfillment progress.</p>
    </a>

    <a href="{{ route('home') }}" class="rounded-2xl border border-blue-200 bg-blue-50 p-5 transition hover:-translate-y-0.5 hover:shadow-sm dark:border-blue-500/30 dark:bg-blue-500/10">
      <p class="text-xs font-semibold uppercase tracking-wide text-blue-700 dark:text-blue-300">Site</p>
      <p class="mt-2 text-lg font-bold text-slate-900 dark:text-white">Back to Website</p>
      <p class="mt-2 text-xs text-slate-700 dark:text-slate-200">Return to platform content and growth resources.</p>
    </a>
  </div>
</div>
@endsection
