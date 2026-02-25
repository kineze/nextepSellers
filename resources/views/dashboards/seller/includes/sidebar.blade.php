<div class="sticky top-24 rounded-2xl border border-slate-200/70 bg-white/80 p-4 shadow-sm backdrop-blur dark:border-slate-800/70 dark:bg-slate-900/70">
  <p class="px-2 text-[0.65rem] font-semibold uppercase tracking-[0.25em] text-blue-600 dark:text-blue-300">Seller Panel</p>

  <nav class="mt-4 space-y-1">
    <a
      href="{{ route('sellerDashboard') }}"
      class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('sellerDashboard') ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800' }}"
    >
      <i class="fas fa-chart-line w-4"></i>
      Dashboard
    </a>

    <a
      href="{{ route('sellerProducts') }}"
      class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('sellerProducts') || request()->routeIs('sellerInventory') ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800' }}"
    >
      <i class="fas fa-box-open w-4"></i>
      Products
    </a>

    <a
      href="{{ route('sellerOrders') }}"
      class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition {{ request()->routeIs('sellerOrders') ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800' }}"
    >
      <i class="fas fa-cart-shopping w-4"></i>
      My Orders
    </a>

    <seller-cart-nav-link :href='@json(route("sellerOrders"))'></seller-cart-nav-link>
  </nav>
</div>
