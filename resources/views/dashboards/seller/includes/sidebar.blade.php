@php
  $mode = $mode ?? 'desktop';
  $wrapperClass = $mode === 'drawer'
      ? 'h-full overflow-y-auto rounded-none border-0 bg-white/95 px-2 py-3 shadow-none backdrop-blur dark:bg-slate-950'
      : 'relative max-h-[calc(100vh-6rem)] overflow-visible rounded-[1.35rem] border border-white/80 bg-white/85 px-2 py-3 shadow-xl shadow-slate-200/70 ring-1 ring-slate-900/5 backdrop-blur-xl dark:border-slate-700/70 dark:bg-slate-950 dark:shadow-black/20 dark:ring-white/5';
  $itemBase = 'seller-sidebar-item group flex items-center gap-2 rounded-xl border px-2 py-2 text-[0.78rem] font-semibold leading-none transition';
  $itemActive = 'border-blue-200 bg-blue-50 text-blue-800 shadow-sm shadow-blue-100/60 dark:border-blue-500/30 dark:bg-blue-500/15 dark:text-blue-200 dark:shadow-none';
  $itemIdle = 'border-transparent text-slate-600 hover:-translate-y-0.5 hover:border-blue-100 hover:bg-blue-50/70 hover:text-blue-700 dark:text-slate-300 dark:hover:border-blue-500/20 dark:hover:bg-slate-800/80 dark:hover:text-blue-200';
  $iconClass = 'seller-sidebar-icon inline-flex aspect-square h-7 w-7 min-h-7 min-w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[0.78rem] text-slate-600 transition group-hover:bg-white group-hover:text-blue-700 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-blue-500/20 dark:group-hover:text-blue-200';
@endphp

<div class="seller-sidebar-shell {{ $wrapperClass }}">
  <div class="flex items-center justify-between px-1">
    <div class="seller-sidebar-label">
      <p class="text-[0.58rem] font-bold uppercase tracking-[0.22em] text-blue-600 dark:text-blue-300">Seller</p>
      <p class="mt-0.5 text-[0.72rem] font-semibold text-slate-500 dark:text-slate-400">Workspace</p>
    </div>

    @if($mode === 'desktop')
      <button
        id="sellerSidebarDesktopToggle"
        type="button"
        class="seller-sidebar-floating-toggle absolute -right-3 top-5 z-20 inline-flex h-7 w-7 items-center justify-center rounded-full border border-blue-100 bg-white text-blue-600 shadow-lg shadow-blue-100/80 transition hover:-translate-y-0.5 hover:bg-blue-50 dark:border-blue-500/30 dark:bg-slate-900 dark:text-blue-300 dark:shadow-black/30 dark:hover:bg-slate-800"
        title="Toggle sidebar size"
      >
        <i class="fas fa-angles-left text-[0.68rem]"></i>
      </button>
    @endif
  </div>

  <nav class="mt-3 space-y-1.5">
    <a
      href="{{ route('sellerDashboard') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerDashboard') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconClass }}">
        <i class="fas fa-chart-line"></i>
      </span>
      <span class="seller-sidebar-label">Dashboard</span>
      <span class="seller-sidebar-tooltip">Dashboard</span>
    </a>

    <a
      href="{{ route('sellerProducts') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerProducts') || request()->routeIs('sellerInventory') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconClass }}">
        <i class="fas fa-box-open"></i>
      </span>
      <span class="seller-sidebar-label">Products</span>
      <span class="seller-sidebar-tooltip">Products</span>
    </a>

    <a
      href="{{ route('sellerOrders') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerOrders') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconClass }}">
        <i class="fas fa-cart-shopping"></i>
      </span>
      <span class="seller-sidebar-label">My Orders</span>
      <span class="seller-sidebar-tooltip">My Orders</span>
    </a>

    <a
      href="{{ route('sellerBulkOrders') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerBulkOrders') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconClass }}">
        <i class="fas fa-table-list"></i>
      </span>
      <span class="seller-sidebar-label">Bulk Orders</span>
      <span class="seller-sidebar-tooltip">Bulk Orders</span>
    </a>

    <a
      href="{{ route('sellerPayments') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerPayments') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconClass }}">
        <i class="fas fa-money-check-dollar"></i>
      </span>
      <span class="seller-sidebar-label">Payments</span>
      <span class="seller-sidebar-tooltip">Payments</span>
    </a>

    <a
      href="{{ route('sellerAffiliate') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerAffiliate') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconClass }}">
        <i class="fas fa-users"></i>
      </span>
      <span class="seller-sidebar-label">Affiliate</span>
      <span class="seller-sidebar-tooltip">Affiliate</span>
    </a>

    <a
      href="{{ route('sellerProfileManager') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerProfileManager') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconClass }}">
        <i class="fas fa-user-pen"></i>
      </span>
      <span class="seller-sidebar-label">Profile Manager</span>
      <span class="seller-sidebar-tooltip">Profile Manager</span>
    </a>
  </nav>
</div>
