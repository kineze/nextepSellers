@php
  $mode = $mode ?? 'desktop';
  $wrapperClass = $mode === 'drawer'
      ? 'h-full overflow-y-auto rounded-none border-0 bg-white/95 p-4 shadow-none backdrop-blur dark:bg-slate-900/95'
      : 'max-h-[calc(100vh-6rem)] overflow-y-auto rounded-3xl border border-slate-200/70 bg-gradient-to-b from-white via-slate-50 to-white p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:from-slate-900 dark:via-slate-900 dark:to-slate-900';
@endphp

<div class="seller-sidebar-shell {{ $wrapperClass }}">
  <div class="flex items-center justify-between">
    <p class="seller-sidebar-label text-[0.65rem] font-semibold uppercase tracking-[0.25em] text-blue-600 dark:text-blue-300">Seller Panel</p>

    @if($mode === 'desktop')
      <button
        id="sellerSidebarDesktopToggle"
        type="button"
        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        title="Toggle sidebar size"
      >
        <i class="fas fa-angles-left text-xs"></i>
      </button>
    @endif
  </div>

  <nav class="mt-4 space-y-2">
    <a
      href="{{ route('sellerDashboard') }}"
      class="seller-sidebar-item group flex items-center gap-3 rounded-2xl border px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('sellerDashboard') ? 'border-blue-200 bg-blue-50 text-blue-800 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/15 dark:text-blue-200' : 'border-slate-200/70 text-slate-700 hover:-translate-y-0.5 hover:border-blue-200 hover:bg-white dark:border-slate-800 dark:text-slate-200 dark:hover:border-blue-500/30 dark:hover:bg-slate-800/60' }}"
    >
      <span class="seller-sidebar-icon inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-700 transition group-hover:bg-blue-100 group-hover:text-blue-700 dark:bg-slate-800 dark:text-slate-200 dark:group-hover:bg-blue-500/20 dark:group-hover:text-blue-200">
        <i class="fas fa-chart-line w-4"></i>
      </span>
      <span class="seller-sidebar-label">Dashboard</span>
    </a>

    <a
      href="{{ route('sellerProducts') }}"
      class="seller-sidebar-item group flex items-center gap-3 rounded-2xl border px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('sellerProducts') || request()->routeIs('sellerInventory') ? 'border-blue-200 bg-blue-50 text-blue-800 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/15 dark:text-blue-200' : 'border-slate-200/70 text-slate-700 hover:-translate-y-0.5 hover:border-blue-200 hover:bg-white dark:border-slate-800 dark:text-slate-200 dark:hover:border-blue-500/30 dark:hover:bg-slate-800/60' }}"
    >
      <span class="seller-sidebar-icon inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-700 transition group-hover:bg-blue-100 group-hover:text-blue-700 dark:bg-slate-800 dark:text-slate-200 dark:group-hover:bg-blue-500/20 dark:group-hover:text-blue-200">
        <i class="fas fa-box-open w-4"></i>
      </span>
      <span class="seller-sidebar-label">Products</span>
    </a>

    <a
      href="{{ route('sellerOrders') }}"
      class="seller-sidebar-item group flex items-center gap-3 rounded-2xl border px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs('sellerOrders') ? 'border-blue-200 bg-blue-50 text-blue-800 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/15 dark:text-blue-200' : 'border-slate-200/70 text-slate-700 hover:-translate-y-0.5 hover:border-blue-200 hover:bg-white dark:border-slate-800 dark:text-slate-200 dark:hover:border-blue-500/30 dark:hover:bg-slate-800/60' }}"
    >
      <span class="seller-sidebar-icon inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-700 transition group-hover:bg-blue-100 group-hover:text-blue-700 dark:bg-slate-800 dark:text-slate-200 dark:group-hover:bg-blue-500/20 dark:group-hover:text-blue-200">
        <i class="fas fa-cart-shopping w-4"></i>
      </span>
      <span class="seller-sidebar-label">My Orders</span>
    </a>

    <seller-cart-nav-link
      :href='@json(route("sellerCheckout"))'
      :is-active='@json(request()->routeIs("sellerCheckout"))'
    ></seller-cart-nav-link>
  </nav>
</div>
