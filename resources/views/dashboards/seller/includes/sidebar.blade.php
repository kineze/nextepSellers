@php
  $mode = $mode ?? 'desktop';
  $wrapperClass = $mode === 'drawer'
      ? 'min-h-0 flex-1 overflow-y-auto rounded-none border-0 bg-black px-2 pb-8 pt-3 shadow-none backdrop-blur'
      : 'relative h-[calc(100vh-6rem)] overflow-visible rounded-[1.35rem] border border-slate-800 bg-black px-2 pb-8 pt-3 shadow-xl shadow-black/30 ring-1 ring-white/10 backdrop-blur-xl';
  $itemBase = 'seller-sidebar-item group flex items-center gap-2 rounded-xl border px-2 py-2 text-[0.78rem] font-semibold leading-none transition';
  $itemActive = 'border-white/10 bg-white text-black shadow-sm shadow-black/20';
  $itemIdle = 'border-transparent text-slate-300 hover:-translate-y-0.5 hover:border-white/10 hover:bg-white/10 hover:text-white';
  $iconBase = 'seller-sidebar-icon inline-flex aspect-square h-7 w-7 min-h-7 min-w-7 shrink-0 items-center justify-center rounded-lg text-[0.78rem] transition';
  $iconActive = 'bg-slate-100 text-black';
  $iconIdle = 'bg-white/10 text-slate-300 group-hover:bg-white group-hover:text-black';
@endphp

<div class="seller-sidebar-shell {{ $wrapperClass }}">
  <div class="seller-sidebar-header flex items-center justify-between gap-3 px-1">
    <div class="seller-sidebar-label inline-flex items-baseline gap-2">
      <p class="text-sm font-black uppercase tracking-[0.18em] text-slate-100">Seller</p>
      <p class="text-xs font-semibold uppercase tracking-wide text-slate-200">Workspace</p>
    </div>

    @if($mode === 'desktop')
      <button
        id="sellerSidebarDesktopToggle"
        type="button"
        class="seller-sidebar-floating-toggle inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-slate-200 shadow-sm transition hover:-translate-y-0.5 hover:bg-white hover:text-black"
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
      <span class="{{ $iconBase }} {{ request()->routeIs('sellerDashboard') ? $iconActive : $iconIdle }}">
        <i class="fas fa-chart-line"></i>
      </span>
      <span class="seller-sidebar-label">Dashboard</span>
      <span class="seller-sidebar-tooltip">Dashboard</span>
    </a>

    <a
      href="{{ route('sellerProducts') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerProducts') || request()->routeIs('sellerProducts.show') || request()->routeIs('sellerInventory') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconBase }} {{ request()->routeIs('sellerProducts') || request()->routeIs('sellerProducts.show') || request()->routeIs('sellerInventory') ? $iconActive : $iconIdle }}">
        <i class="fas fa-box-open"></i>
      </span>
      <span class="seller-sidebar-label">Products</span>
      <span class="seller-sidebar-tooltip">Products</span>
    </a>

    <a
      href="{{ route('sellerOrders') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerOrders') || request()->routeIs('sellerOrderShow') || request()->routeIs('sellerOrderCreate') || request()->routeIs('sellerBulkOrders') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconBase }} {{ request()->routeIs('sellerOrders') || request()->routeIs('sellerOrderShow') || request()->routeIs('sellerOrderCreate') || request()->routeIs('sellerBulkOrders') ? $iconActive : $iconIdle }}">
        <i class="fas fa-cart-shopping"></i>
      </span>
      <span class="seller-sidebar-label">Orders</span>
      <span class="seller-sidebar-tooltip">Orders</span>
    </a>

    <a
      href="{{ route('sellerPayments') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerPayments') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconBase }} {{ request()->routeIs('sellerPayments') ? $iconActive : $iconIdle }}">
        <i class="fas fa-money-check-dollar"></i>
      </span>
      <span class="seller-sidebar-label">Payments & Invoices</span>
      <span class="seller-sidebar-tooltip">Payments & Invoices</span>
    </a>

    <a
      href="{{ route('sellerSalesTargets') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerSalesTargets') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconBase }} {{ request()->routeIs('sellerSalesTargets') ? $iconActive : $iconIdle }}">
        <i class="fas fa-bullseye"></i>
      </span>
      <span class="seller-sidebar-label">Sales Targets</span>
      <span class="seller-sidebar-tooltip">Sales Targets</span>
    </a>

    <a
      href="{{ route('sellerAffiliate') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerAffiliate') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconBase }} {{ request()->routeIs('sellerAffiliate') ? $iconActive : $iconIdle }}">
        <i class="fas fa-users"></i>
      </span>
      <span class="seller-sidebar-label">Affiliate</span>
      <span class="seller-sidebar-tooltip">Affiliate</span>
    </a>

    <a
      href="{{ route('sellerProfileManager') }}"
      class="{{ $itemBase }} {{ request()->routeIs('sellerProfileManager') ? $itemActive : $itemIdle }}"
    >
      <span class="{{ $iconBase }} {{ request()->routeIs('sellerProfileManager') ? $iconActive : $iconIdle }}">
        <i class="fas fa-user-pen"></i>
      </span>
      <span class="seller-sidebar-label">Profile</span>
      <span class="seller-sidebar-tooltip">Profile</span>
    </a>

    <button
      type="button"
      data-seller-new-order-open
      class="seller-sidebar-new-order group mt-4 flex w-full items-center gap-3 rounded-2xl border border-cyan-300/25 bg-gradient-to-br from-sky-950 via-blue-800 to-cyan-700 px-3 py-3 text-left text-white shadow-lg shadow-cyan-950/30 ring-1 ring-cyan-200/20 transition hover:-translate-y-0.5 hover:from-sky-900 hover:via-blue-700 hover:to-cyan-600"
    >
      <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 text-white shadow-sm ring-1 ring-white/20">
        <i class="fas fa-plus"></i>
      </span>
      <span class="seller-sidebar-label min-w-0">
        <span class="block text-sm font-black leading-tight">New Order</span>
        <span class="mt-0.5 block text-[11px] font-semibold uppercase tracking-wide text-cyan-100/80">Single or bulk upload</span>
      </span>
      <span class="seller-sidebar-tooltip">New Order</span>
    </button>
  </nav>
</div>
