<aside id="sidebar" class="sidebar-modern fixed top-0 left-0 h-full overflow-y-auto w-60 bg-white dark:bg-slate-900 shadow-xl
              transition-all duration-300 z-[100] overflow-hidden
              transform -translate-x-full lg:translate-x-0">

      <!-- Top Brand -->
      <div class="sidebar-brand flex items-center justify-center px-3 h-20">
        <a href="{{ route('setDashboard') }}" class="relative flex items-center gap-2">
          <!-- Expanded: Light Logo -->
          <img
            src="/assets/img/nextep-logo.webp"
            alt="Nextep"
            class="sidebar-logo sidebar-logo-full block h-12 w-auto dark:hidden transition-opacity duration-200"
            loading="lazy"
          />
          <!-- Expanded: Dark Logo -->
          <img
            src="/assets/img/nextep-logo-dark.webp"
            alt="Nextep"
            class="sidebar-logo sidebar-logo-full hidden dark:block h-12 w-auto transition-opacity duration-200"
            loading="lazy"
          />
          <!-- Mini: Icon -->
          <img
            src="/assets/img/nextep-icon.webp"
            alt="Nextep Icon"
            class="sidebar-logo sidebar-logo-icon hidden h-9 w-9 transition-opacity duration-200"
            loading="lazy"
          />
          <span class="sidebar-badge sidebar-label">Ops</span>
        </a>
      </div>

      <!-- Nav -->
      <nav class="mt-4 space-y-2 px-3">

        @can('Manage Inventory')
        <!-- Call Center -->
        <div class="relative sidebar-dropdown"
            data-subtitle="Fulfilment"
            data-links='[
              {"label":"Products","href":"{{ url('admin/product-list') }}"},
              {"label":"suppliers","href":"{{ url('admin/supplier-list') }}"}
              
            ]'>

          <button class="dropdown-toggle  dark:border-zinc-600 w-full flex items-center gap-3 p-2 dark:hover:bg-zinc-950 transition-all">
            <div class="sidebar-icon-box">
              <i class="fas fa-boxes-stacked" aria-hidden="true"></i>
              <span class="sr-only">Fulfilment</span>
            </div>
            <span class="sidebar-label flex-1 text-sm font-medium text-zinc-700 dark:text-white text-left">
              Fulfilment
            </span>
            <i class="fas fa-chevron-down text-xs text-zinc-500 sidebar-label"></i>
          </button>

          <div class="expanded-only hidden py-1 ml-4 space-y-1">
            <a href="{{ url('admin/product-list') }}" class="block px-4 py-2 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md">
              Products
            </a>
            <a href="{{ url('admin/supplier-list') }}" class="block px-4 py-2 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md">
              suppliers
            </a>
          </div>
        </div>
        @endcan

        @can('Manage Settings')
        <!-- Users -->
        <div class="relative sidebar-dropdown"
            data-subtitle="Settings"
            data-links='[
              {"label":"Roles & Permissions","href":"{{ url('/roles-and-permission') }}"},
              {"label":"System Users","href":"{{ url('system-users') }}"}
            ]'>

          <button class="dropdown-toggle  dark:border-stone-600 w-full flex items-center gap-3 p-2 dark:hover:bg-slate-800 transition-all">
            <div class="sidebar-icon-box">
              <i class="fas fa-cog" aria-hidden="true"></i>
              <span class="sr-only">Settings</span>
            </div>
            <span class="sidebar-label flex-1 text-sm font-medium text-stone-700 dark:text-white text-left">
              Settings
            </span>
            <i class="fas fa-chevron-down text-xs text-stone-500 sidebar-label"></i>
          </button>

          <div class="expanded-only hidden py-1 ml-4 space-y-1">
            <a href="{{ url('/roles-and-permission') }}" class="block px-4 py-2 text-xs font-semibold text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-slate-700 rounded-md">
              Roles & Permissions
            </a>
            <a href="{{ url('system-users') }}" class="block px-4 py-2 text-xs font-semibold text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-slate-700 rounded-md">
              System Users
            </a>
          </div>
        </div>
        @endcan

      </nav>
</aside>

<!-- Shared Sub Sidebar (Mini Mode) -->
<div id="subSidebar"
     class="fixed left-20 w-56 bg-white dark:bg-slate-800 shadow-2xl border border-stone-200 dark:border-slate-700
            hidden flex-col z-[200] overflow-y-auto rounded-lg transition-all duration-200">
  <div class="px-4 py-3 border-b border-stone-200 dark:border-slate-700 flex items-center justify-between">
    <h3 id="subSidebarTitle" class="text-sm font-semibold uppercase text-stone-700 dark:text-white"></h3>
    <button id="closeSubSidebar" class="text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 text-xs">
      <i class="fas fa-times"></i>
    </button>
  </div>
  <div id="subSidebarLinks" class="py-2 max-h-[calc(100vh-60px)] overflow-y-auto"></div>
</div>
