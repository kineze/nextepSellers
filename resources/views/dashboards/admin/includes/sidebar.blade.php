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
        <!-- Inventory -->
        <div class="relative sidebar-dropdown"
            data-subtitle="Inventory"
            data-links='[
              {"label":"Product Manager","href":"{{ url('/products') }}"},
              {"label":"Suppliers","href":"{{ url('admin/supplier-list') }}"},
              {"label":"Category Manager","href":"{{ url('/categories') }}"}
            ]'>

          <button class="dropdown-toggle  dark:border-zinc-600 w-full flex items-center gap-3 p-2 dark:hover:bg-zinc-950 transition-all">
            <div class="sidebar-icon-box">
              <i class="fas fa-boxes-stacked" aria-hidden="true"></i>
              <span class="sr-only">Inventory</span>
            </div>
            <span class="sidebar-label flex-1 text-sm font-medium text-zinc-700 dark:text-white text-left">
              Inventory
            </span>
            <i class="fas fa-chevron-down text-xs text-zinc-500 sidebar-label"></i>
          </button>

          <div class="expanded-only hidden py-1 ml-4 space-y-1">
            <a href="{{ url('/products') }}" class="block px-4 py-2 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md">
              Product Manager
            </a>
            <a href="{{ url('admin/supplier-list') }}" class="block px-4 py-2 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md">
              Suppliers
            </a>
            <a href="{{ url('/categories') }}" class="block px-4 py-2 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-700 rounded-md">
              Category Manager
            </a>
          </div>
        </div>
        @endcan

          @can('Manage Sellers')
        <!-- Sellers -->
        <div class="relative sidebar-dropdown"
            data-subtitle="Sellers"
            data-links='[
              {"label":"Active Sellers","href":"{{ url('/active-sellers') }}"},
              {"label":"Seller Manager","href":"{{ url('/seller-registrations') }}"}
            ]'>

          <button class="dropdown-toggle dark:border-emerald-600 w-full flex items-center gap-3 p-2 dark:hover:bg-slate-800 transition-all">
            <div class="sidebar-icon-box">
              <i class="fas fa-store" aria-hidden="true"></i>
              <span class="sr-only">Sellers</span>
            </div>
            <span class="sidebar-label flex-1 text-sm font-medium text-emerald-700 dark:text-white text-left">
              Sellers
            </span>
            <i class="fas fa-chevron-down text-xs text-emerald-500 sidebar-label"></i>
          </button>

          <div class="expanded-only hidden py-1 ml-4 space-y-1">
            <a href="{{ url('/active-sellers') }}" class="block px-4 py-2 text-xs font-semibold text-emerald-600 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-slate-700 rounded-md">
              Active Sellers
            </a>
            <a href="{{ url('/seller-registrations') }}" class="block px-4 py-2 text-xs font-semibold text-emerald-600 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-slate-700 rounded-md">
              Seller Manager
            </a>
          </div>
        </div>
        @endcan

        @can('Manage Levels')
        <div class="relative sidebar-dropdown"
            data-subtitle="Levels"
            data-links='[
              {"label":"Level Manager","href":"{{ url('/levels') }}"}
            ]'>

          <button class="dropdown-toggle dark:border-cyan-600 w-full flex items-center gap-3 p-2 dark:hover:bg-slate-800 transition-all">
            <div class="sidebar-icon-box">
              <i class="fas fa-layer-group" aria-hidden="true"></i>
              <span class="sr-only">Levels</span>
            </div>
            <span class="sidebar-label flex-1 text-sm font-medium text-cyan-700 dark:text-white text-left">
              Levels
            </span>
            <i class="fas fa-chevron-down text-xs text-cyan-500 sidebar-label"></i>
          </button>

          <div class="expanded-only hidden py-1 ml-4 space-y-1">
            <a href="{{ url('/levels') }}" class="block px-4 py-2 text-xs font-semibold text-cyan-600 dark:text-cyan-300 hover:bg-cyan-100 dark:hover:bg-slate-700 rounded-md">
              Level Manager
            </a>
          </div>
        </div>
        @endcan

        @can('Manage System Configuration')
        <!-- System Configuration -->
        <div class="relative sidebar-dropdown"
            data-subtitle="System Configuration"
            data-links='[
              {"label":"Attribute Manager","href":"{{ url('/attributes') }}"}
            ]'>

          <button class="dropdown-toggle  dark:border-amber-600 w-full flex items-center gap-3 p-2 dark:hover:bg-slate-800 transition-all">
            <div class="sidebar-icon-box">
              <i class="fas fa-sliders" aria-hidden="true"></i>
              <span class="sr-only">System Configuration</span>
            </div>
            <span class="sidebar-label flex-1 text-sm font-medium text-amber-700 dark:text-white text-left">
              System Configuration
            </span>
            <i class="fas fa-chevron-down text-xs text-amber-500 sidebar-label"></i>
          </button>

        <div class="expanded-only hidden py-1 ml-4 space-y-1">
            <a href="{{ url('/attributes') }}" class="block px-4 py-2 text-xs font-semibold text-amber-600 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-slate-700 rounded-md">
              Attribute Manager
            </a>
          </div>
        </div>
        @endcan

        @can('Manage Settings')
        <!-- Settings -->
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
