<!DOCTYPE html>
<html lang="en">
@include('site.includes.headerlinks')
<body class="m-0 font-sans antialiased text-slate-600 dark:bg-slate-950 dark:text-white">
  <div id="app" class="flex min-h-screen flex-col bg-slate-50 dark:bg-slate-950">
    <header class="sticky top-0 z-[1100] border-b border-slate-200/80 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
      <div class="flex h-16 w-full items-center justify-between px-6">
        <a href="{{ route('sellerDashboard') }}" class="inline-flex items-center gap-3">
          <img src="{{ asset('assets/img/nextep-icon.webp') }}" alt="Nextep" class="h-8 w-8 rounded-lg object-cover">
          <div>
            <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-blue-600 dark:text-blue-300">Nextep</p>
            <p class="text-sm font-semibold text-slate-900 dark:text-white">Seller Dashboard</p>
          </div>
        </a>

        <div class="flex items-center gap-2">
          <a href="{{ route('sellerDashboard') }}" class="hidden rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wide md:inline-flex {{ request()->routeIs('sellerDashboard') ? 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-200' : 'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800' }}">
            Dashboard
          </a>
          <a href="{{ route('learningMaterials') }}" class="hidden rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 md:inline-flex">
            Learning
          </a>
          <a href="{{ url('/') }}" class="hidden rounded-lg px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800 md:inline-flex">
            Go to Site
          </a>

          <div class="hidden items-center rounded-full border border-slate-200 bg-slate-100 p-1 dark:border-slate-700 dark:bg-slate-800 sm:flex">
            <button type="button" data-theme-btn="light" class="rounded-full px-2 py-1 text-[12px] font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Light mode">
              <i class="fas fa-sun"></i>
            </button>
            <button type="button" data-theme-btn="dark" class="rounded-full px-2 py-1 text-[12px] font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Dark mode">
              <i class="fas fa-moon"></i>
            </button>
          </div>

          <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
            @csrf
            <button
              type="submit"
              class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
            >
              Logout
            </button>
          </form>

          <button
            id="sellerSidebarMobileOpen"
            type="button"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800 lg:hidden"
          >
            <i class="fas fa-bars"></i>
            Menu
          </button>
        </div>
      </div>
    </header>

    <main class="w-full flex-1 px-6 py-8">
      <div class="flex w-full gap-6">
        <aside id="sellerDesktopSidebar" class="hidden shrink-0 transition-all duration-300 lg:sticky lg:top-20 lg:block lg:w-72 lg:self-start">
          <div id="sellerDesktopSidebarShell">
            @include('dashboards.seller.includes.sidebar', ['mode' => 'desktop'])
          </div>
        </aside>

        <section class="min-w-0 w-full flex-1">
          @yield('content')
        </section>
      </div>
    </main>

    <div id="sellerSidebarMobileOverlay" class="fixed inset-0 z-[1198] hidden bg-black/40 lg:hidden"></div>
    <aside
      id="sellerMobileSidebar"
      class="fixed inset-y-0 left-0 z-[1199] w-72 -translate-x-full border-r border-slate-200 bg-white/95 shadow-2xl transition-transform duration-300 dark:border-slate-800 dark:bg-slate-900/95 lg:hidden"
    >
      <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3 dark:border-slate-800">
        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-600 dark:text-blue-300">Seller Menu</p>
        <button
          id="sellerSidebarMobileClose"
          type="button"
          class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
        >
          <i class="fas fa-xmark"></i>
        </button>
      </div>
      <div class="px-4 pt-3">
        <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-slate-700 dark:bg-slate-800">
          <button type="button" data-theme-btn="light" class="flex-1 rounded-lg px-2 py-2 text-xs font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Light mode">
            <i class="fas fa-sun"></i>
          </button>
          <button type="button" data-theme-btn="dark" class="flex-1 rounded-lg px-2 py-2 text-xs font-semibold text-slate-600 hover:bg-white dark:text-slate-300 dark:hover:bg-slate-700" aria-label="Dark mode">
            <i class="fas fa-moon"></i>
          </button>
        </div>
      </div>
      @include('dashboards.seller.includes.sidebar', ['mode' => 'drawer'])
    </aside>

    <seller-floating-cart :orders-url='@json(route("sellerCheckout"))'></seller-floating-cart>
  </div>

  <style>
    #sellerDesktopSidebar.is-compact {
      width: 3.55rem;
      z-index: 1200;
    }

    #sellerDesktopSidebar.is-compact #sellerDesktopSidebarShell .seller-sidebar-shell {
      padding-left: 0.25rem;
      padding-right: 0.25rem;
      border-radius: 1.15rem;
    }

    #sellerDesktopSidebar.is-compact #sellerDesktopSidebarShell .seller-sidebar-item {
      width: 2.2rem;
      height: 2.2rem;
      justify-content: center;
      gap: 0;
      padding: 0;
      margin-left: auto;
      margin-right: auto;
    }

    #sellerDesktopSidebar.is-compact #sellerDesktopSidebarShell .seller-sidebar-label {
      display: none;
    }

    #sellerDesktopSidebar #sellerDesktopSidebarShell .seller-sidebar-tooltip {
      display: none;
    }

    #sellerDesktopSidebar.is-compact #sellerDesktopSidebarShell .seller-sidebar-tooltip {
      position: absolute;
      left: calc(100% + 0.55rem);
      top: 50%;
      z-index: 1300;
      display: block;
      max-width: 10rem;
      transform: translateY(-50%) translateX(-0.25rem);
      border-radius: 0.6rem;
      border: 1px solid rgba(148, 163, 184, 0.28);
      background: rgba(15, 23, 42, 0.96);
      color: white;
      font-size: 0.68rem;
      font-weight: 700;
      line-height: 1;
      opacity: 0;
      padding: 0.45rem 0.55rem;
      pointer-events: none;
      white-space: nowrap;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.22);
      transition: opacity 0.16s ease, transform 0.16s ease;
    }

    #sellerDesktopSidebar.is-compact #sellerDesktopSidebarShell .seller-sidebar-item:hover .seller-sidebar-tooltip,
    #sellerDesktopSidebar.is-compact #sellerDesktopSidebarShell .seller-sidebar-item:focus-visible .seller-sidebar-tooltip {
      opacity: 1;
      transform: translateY(-50%) translateX(0);
    }

    #sellerDesktopSidebar.is-compact #sellerDesktopSidebarShell .seller-sidebar-floating-toggle {
      right: -0.6rem;
      top: 0.9rem;
    }

    #sellerDesktopSidebar.is-compact #sellerDesktopSidebarShell .seller-cart-link {
      width: 2.2rem;
      height: 2.2rem;
      justify-content: center;
      padding: 0;
      margin-left: auto;
      margin-right: auto;
      position: relative;
    }

    #sellerDesktopSidebar.is-compact #sellerDesktopSidebarShell .seller-cart-link-label {
      gap: 0;
    }

    #sellerDesktopSidebar.is-compact #sellerDesktopSidebarShell .seller-cart-link-count {
      position: absolute;
      right: 0.2rem;
      top: 0.2rem;
      min-width: 1.1rem;
      padding: 0.08rem 0.2rem;
      font-size: 0.62rem;
    }
  </style>

  <script>
    (() => {
      const compactKey = 'seller-sidebar-compact-v1';
      const themeKey = 'nextep-theme-pref';
      const getDesktopSidebar = () => document.getElementById('sellerDesktopSidebar');
      const getDesktopToggleIcon = () => document.querySelector('#sellerSidebarDesktopToggle i');
      const getMobileSidebar = () => document.getElementById('sellerMobileSidebar');
      const getMobileOverlay = () => document.getElementById('sellerSidebarMobileOverlay');
      const getThemeButtons = () => Array.from(document.querySelectorAll('[data-theme-btn]'));

      const applyCompact = (isCompact) => {
        const desktopSidebar = getDesktopSidebar();
        const desktopToggleIcon = getDesktopToggleIcon();
        if (!desktopSidebar) return;

        desktopSidebar.classList.toggle('is-compact', isCompact);
        if (desktopToggleIcon) {
          desktopToggleIcon.className = isCompact ? 'fas fa-angles-right text-xs' : 'fas fa-angles-left text-xs';
        }
      };

      const getSavedCompact = () => {
        try {
          return window.localStorage.getItem(compactKey) === '1';
        } catch (e) {
          return false;
        }
      };

      const setSavedCompact = (isCompact) => {
        try {
          window.localStorage.setItem(compactKey, isCompact ? '1' : '0');
        } catch (e) {
          // ignore localStorage failures
        }
      };

      const openMobile = () => {
        const mobileSidebar = getMobileSidebar();
        const mobileOverlay = getMobileOverlay();
        if (!mobileSidebar || !mobileOverlay) return;

        mobileSidebar.classList.remove('-translate-x-full');
        mobileOverlay.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
      };

      const closeMobile = () => {
        const mobileSidebar = getMobileSidebar();
        const mobileOverlay = getMobileOverlay();
        if (!mobileSidebar || !mobileOverlay) return;

        mobileSidebar.classList.add('-translate-x-full');
        mobileOverlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      };

      const initSidebarState = () => {
        applyCompact(getSavedCompact());
      };

      const applyTheme = (theme) => {
        const normalized = ['light', 'dark'].includes(theme) ? theme : 'light';
        document.documentElement.classList.remove('dark', 'theme-preload-dark');
        document.body.classList.remove('theme-light', 'theme-dark');

        if (normalized === 'dark') {
          document.documentElement.classList.add('dark');
          document.body.classList.add('theme-dark');
        } else {
          document.body.classList.add('theme-light');
        }

        getThemeButtons().forEach((btn) => {
          const isActive = btn.getAttribute('data-theme-btn') === normalized;
          btn.classList.toggle('bg-white', isActive);
          btn.classList.toggle('dark:bg-slate-700', isActive);
          btn.classList.toggle('text-slate-900', isActive);
          btn.classList.toggle('dark:text-white', isActive);
        });
      };

      const setTheme = (theme) => {
        const normalized = ['light', 'dark'].includes(theme) ? theme : 'light';
        applyTheme(normalized);
        try {
          window.localStorage.setItem(themeKey, normalized);
        } catch (e) {
          // ignore localStorage failures
        }
      };

      const initTheme = () => {
        let saved = 'light';
        try {
          saved = window.localStorage.getItem(themeKey) || 'light';
        } catch (e) {
          saved = 'light';
        }
        applyTheme(saved);
      };

      if (!window.__sellerSidebarEventsBound) {
        window.__sellerSidebarEventsBound = true;

        document.addEventListener('click', (event) => {
          const target = event.target;

          if (target.closest('#sellerSidebarDesktopToggle')) {
            const desktopSidebar = getDesktopSidebar();
            if (!desktopSidebar) return;

            const next = !desktopSidebar.classList.contains('is-compact');
            applyCompact(next);
            setSavedCompact(next);
            return;
          }

          if (target.closest('#sellerSidebarMobileOpen')) {
            openMobile();
            return;
          }

          if (target.closest('#sellerSidebarMobileClose') || target.closest('#sellerSidebarMobileOverlay')) {
            closeMobile();
            return;
          }

          const themeBtn = target.closest('[data-theme-btn]');
          if (themeBtn) {
            setTheme(themeBtn.getAttribute('data-theme-btn') || 'light');
            return;
          }
        });

        window.addEventListener('resize', () => {
          if (window.innerWidth >= 1024) {
            closeMobile();
          }
        });
      }

      initSidebarState();
      initTheme();
      window.setTimeout(initSidebarState, 300);
      window.setTimeout(initTheme, 300);
    })();
  </script>

  @include('site.includes.footerlinks')
  @stack('scripts')
</body>
</html>
