@extends('layouts.seller.app')

@section('content')
<div class="space-y-6">
  <div class="rounded-3xl border border-slate-200/70 bg-white/80 p-7 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
    <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-blue-600 dark:text-blue-300">Seller Dashboard</p>
    <h1 class="mt-3 text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">Welcome back, {{ auth()->user()->name }}</h1>
    <p class="mt-3 max-w-3xl text-sm text-slate-600 dark:text-slate-300">
      Manage your selling activities from one place. Track inventory readiness, monitor orders, and continue optimization.
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
