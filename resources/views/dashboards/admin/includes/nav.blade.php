<nav navbar-main class=" flex bg-green dark:bg-transparent flex-wrap items-center justify-between px-0   duration-250 ease-soft-in border-none backdrop-blur-xl   lg:flex-nowrap lg:justify-start sticky top-0 z-[990] " navbar-scroll="true">
  <div class="flex items-center justify-between w-full px-4 py-3  flex-wrap-inherit">

    <div class="flex pr-4 w-6/12 items-center">
      <button id="toggleSidebar"
              class="flex items-center justify-center h-9 w-9 rounded
                    bg-white dark:bg-slate-800 shadow dark:border-slate-700
                    text-stone-600 dark:text-stone-200 transition rotate-180 hover:bg-stone-100 dark:hover:bg-slate-700"
              aria-label="Toggle sidebar">
          <i class="fa-solid fa-bars-staggered"></i>
      </button>
    </div>

    <div class="flex items-center justify-end mt-2 grow sm:mt-0 sm:mr-6 md:mr-0 lg:flex lg:basis-auto" id="navbar">  
      <div class="flex items-center justify-end md:ml-auto md:pr-4">

       
      </div>
      <ul class="flex flex-row items-center justify-end pl-0 mb-0 list-none md-max:w-full">

        <li class="relative p-2">
          <button id="themeMenuBtn"
                  aria-haspopup="true"
                  aria-expanded="false"
                  class="px-2"
                  title="Change Sidebar Theme">
            <i class="fas fa-adjust"></i>
          </button>

          <!-- Dropdown -->
          <div id="themeMenu"
              class="hidden mt-2 w-64 rounded-lg overflow-hidden bg-white absolute right-0 dark:bg-slate-800 shadow-lg ring-1 ring-black/5 dark:ring-slate-700
                      animate-fade"
              role="menu"
              aria-label="Sidebar Theme Menu">
            <div class="p-2 grid grid-cols-2 gap-2">
              <!-- Each theme button -->
              <button data-theme-choice="blue" class="theme-swatch group">
                <span class="swatch-color bg-[#1d4ed8]"></span>
                <span class="swatch-label">Blue</span>
              </button>
              <button data-theme-choice="black" class="theme-swatch group">
                <span class="swatch-color bg-[#111827]"></span>
                <span class="swatch-label">Black</span>
              </button>
              <button data-theme-choice="dark-blue" class="theme-swatch group">
                <span class="swatch-color bg-[#0f172a]"></span>
                <span class="swatch-label">Dark Blue</span>
              </button>
              <button data-theme-choice="green" class="theme-swatch group">
                <span class="swatch-color bg-[#047857]"></span>
                <span class="swatch-label">Green</span>
              </button>
              <button data-theme-choice="light-blue" class="theme-swatch group col-span-2">
                <span class="swatch-color bg-[#e0f2fe] border border-sky-200"></span>
                <span class="swatch-label">Light Blue</span>
              </button>
            </div>
            <div class="px-2 pb-2">
              <button id="resetTheme"
                      class="w-full text-[11px] uppercase tracking-wide font-semibold px-2 py-1.5 rounded
                            bg-stone-100 dark:bg-slate-700 hover:bg-stone-200 dark:hover:bg-slate-600
                            text-stone-700 dark:text-stone-200">
                Reset Default
              </button>
            </div>
          </div>
        </li>

        <li class="flex items-center">
                <dark-mode-toggle></dark-mode-toggle>
        </li>
      <li class="flex items-center">
        <div class="relative" id="userMenuWrapper">
          <button dropdown-trigger
                  aria-haspopup="true"
                  aria-expanded="false"
                  type="button"
                  class="inline-block p-0 mr-4 cursor-pointer focus:outline-none"
          >
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
              <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
            @else
              <span class="inline-flex items-center rounded-md text-sm font-medium">
                {{ Auth::user()->name }}
                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
              </span>
            @endif
          </button>

          <!-- Dropdown -->
          <ul dropdown-menu
              class="user-menu origin-top-right absolute right-0 mt-2 min-w-44
                    rounded-lg bg-white dark:bg-black shadow-lg ring-1 ring-black/5
                    px-2 py-2 text-sm text-slate-600 dark:text-slate-200
                    opacity-0 scale-95 pointer-events-none transition
                    focus:outline-none"
              role="menu"
              aria-label="User Menu">
            <li>
              <a href="{{ route('profile.show') }}"
                class="menu-item font-bold"
                role="menuitem">
                {{ __('Profile') }}
              </a>
            </li>
            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
            <li>
              <a href="{{ route('api-tokens.index') }}"
                class="menu-item"
                role="menuitem">
                {{ __('API Tokens') }}
              </a>
            </li>
            @endif
            <li>
              <form id="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="menu-item font-bold w-full text-left"
                        role="menuitem">
                  {{ __('Log Out') }}
                </button>
              </form>
            </li>
          </ul>
        </div>
      </li>

      </ul>
    </div>
  </div>
</nav>
