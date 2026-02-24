<script>
document.addEventListener('DOMContentLoaded', () => {
  document.documentElement.classList.remove('theme-preload-dark');

  /* ------------------ Element Refs ------------------ */
  const sidebar          = document.getElementById('sidebar');
  const toggleBtn        = document.getElementById('toggleSidebar');   // external button
  const overlay          = document.getElementById('sidebarOverlay');  // mobile overlay
  const main             = document.getElementById('mainContent');

  const dropdownToggles  = Array.from(document.querySelectorAll('.dropdown-toggle'));
  const expandedBoxes    = Array.from(document.querySelectorAll('.expanded-only'));

  const subSidebar       = document.getElementById('subSidebar');
  const subTitle         = document.getElementById('subSidebarTitle');
  const subLinksWrap     = document.getElementById('subSidebarLinks');
  const closeSubBtn      = document.getElementById('closeSubSidebar');

  if (!sidebar || !toggleBtn) return;

  /* ------------------ State & Helpers ------------------ */
  const stateKey      = 'sbMini';
  const isMini        = () => sidebar.classList.contains('w-20');
  const isMobile      = () => window.matchMedia('(max-width:1023.5px)').matches;
  const isDrawerOpen  = () => !sidebar.classList.contains('-translate-x-full');

  // For sub-sidebar logic
  let hideTimer       = null;
  let currentSubKey   = null;

  /* ------------------ Desktop Mode (Mini / Expanded) ------------------ */
  function setDesktopMode(mini) {
    sidebar.classList.toggle('w-20', mini);
    sidebar.classList.toggle('w-60', !mini);
    sidebar.setAttribute('data-mini', mini ? 'true' : 'false');
    toggleBtn.setAttribute('data-mini', mini ? 'true' : 'false');

   if (main) {
      main.classList.remove('ml-20', 'ml-60');

      if (!isMobile()) {
        main.classList.add(mini ? 'ml-20' : 'ml-60');
      } else {
        main.classList.remove('ml-20', 'ml-60'); // Just to be safe
      }
    }
    if (!mini) hideSubSidebar();
    try { localStorage.setItem(stateKey, mini ? '1' : '0'); } catch {}
  }

  /* ------------------ Mobile Drawer ------------------ */
  function openDrawer() {
    sidebar.classList.remove('-translate-x-full');
    overlay?.classList.remove('hidden');
    document.body.classList.add('body-lock');
    toggleBtn.setAttribute('data-mini', 'false'); // arrow points "close" direction
  }
  function closeDrawer() {
    sidebar.classList.add('-translate-x-full');
    overlay?.classList.add('hidden');
    document.body.classList.remove('body-lock');
  }
  function toggleDrawer() {
    isDrawerOpen() ? closeDrawer() : openDrawer();
  }

  /* ------------------ Toggle Button Behavior ------------------ */
  toggleBtn.addEventListener('click', () => {
    if (isMobile()) {
      toggleDrawer();
    } else {
      setDesktopMode(!isMini());
    }
  });

  /* ------------------ Mobile Outside / Overlay Close ------------------ */
  overlay?.addEventListener('click', closeDrawer);
  document.addEventListener('click', (e) => {
    if (!isMobile() || !isDrawerOpen()) return;
    if (sidebar.contains(e.target) || toggleBtn.contains(e.target)) return;
    closeDrawer();
  });

  /* ------------------ ESC Key ------------------ */
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (isMobile() && isDrawerOpen()) closeDrawer();
      hideSubSidebar();
    }
  });

  /* ------------------ Dropdown Toggle (Expanded Desktop & Mobile) ------------------ */
  dropdownToggles.forEach(btn => {
    btn.addEventListener('click', (e) => {
      // Block only in desktop mini (hover-driven). Allow mobile & expanded.
      if (isMini() && !isMobile()) return;
      e.preventDefault();
      const box  = btn.closest('.sidebar-dropdown')?.querySelector('.expanded-only');
      const chev = btn.querySelector('.fa-chevron-down');
      if (box) box.classList.toggle('hidden');
      if (chev) chev.classList.toggle('rotate-180');
    });
  });

  /* ------------------ Sub-Sidebar (Flyout) Logic (Desktop Mini) ------------------ */
  function cancelHide() {
    if (hideTimer) {
      clearTimeout(hideTimer);
      hideTimer = null;
    }
  }
  function scheduleHide(delay = 160) {
    cancelHide();
    hideTimer = setTimeout(() => {
      hideSubSidebar();
      currentSubKey = null;
    }, delay);
  }

  function positionSubSidebar(wrapper) {
    const sidebarRect = sidebar.getBoundingClientRect();
    const wrapperRect = wrapper.getBoundingClientRect();
    const viewportH   = window.innerHeight;

    // Temporary reveal to measure
    subSidebar.style.visibility = 'hidden';
    subSidebar.classList.remove('hidden');
    const panelHeight = subSidebar.offsetHeight;
    subSidebar.classList.add('hidden');
    subSidebar.style.visibility = '';

    let top = wrapperRect.top;
    const margin = 8;
    if (top + panelHeight + margin > viewportH) {
      top = Math.max(margin, viewportH - panelHeight - margin);
    } else {
      top = Math.max(margin, top);
    }

    // Overlap by 1px to remove gap flicker
    subSidebar.style.left = (sidebarRect.width - 1) + 'px';
    subSidebar.style.top  = top + 'px';
  }

  function buildSubSidebar(wrapper) {
    const title = wrapper.getAttribute('data-subtitle') || 'Menu';
    let items = [];
    try { items = JSON.parse(wrapper.getAttribute('data-links') || '[]'); } catch {}

    if (subTitle) subTitle.textContent = title;
    if (subLinksWrap) {
      subLinksWrap.innerHTML = '';
      if (!items.length) {
        const empty = document.createElement('div');
        empty.className = 'px-4 py-2 text-sm text-stone-400';
        empty.textContent = 'No items';
        subLinksWrap.appendChild(empty);
      } else {
        items.forEach(obj => {
          const a = document.createElement('a');
          a.href = obj.href || '#';
          a.textContent = obj.label || 'Item';
            a.className = 'block px-4 py-2 text-lg font-bold uppercase text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-slate-700 rounded-md';
          subLinksWrap.appendChild(a);
        });
      }
    }
  }

  function showSubSidebar(wrapper) {
    if (!isMini() || isMobile() || !subSidebar) return;

    const key = wrapper.getAttribute('data-subtitle') || '';
    if (!subSidebar.classList.contains('hidden') && currentSubKey === key) {
      // Already showing this menu; just ensure position and keep open
      cancelHide();
      positionSubSidebar(wrapper);
      return;
    }
    currentSubKey = key;

    buildSubSidebar(wrapper);
    positionSubSidebar(wrapper);

    subSidebar.classList.remove('hidden');
    void subSidebar.offsetWidth; // reflow for animation
    subSidebar.classList.add('sub-open');
    cancelHide();
  }

  function hideSubSidebar() {
    if (subSidebar && !subSidebar.classList.contains('hidden')) {
      subSidebar.classList.add('hidden');
      subSidebar.classList.remove('sub-open');
    }
  }

  // Pointer-based stability
  function inCombined(el) {
    return (sidebar.contains(el) || subSidebar.contains(el));
  }

  // Hover trigger only on dropdown wrappers (so icons only show when relevant)
  const dropdownWrappers = Array.from(document.querySelectorAll('.sidebar-dropdown'));
  dropdownWrappers.forEach(wrapper => {
    wrapper.addEventListener('pointerenter', () => {
      if (!isMini() || isMobile()) return;
      showSubSidebar(wrapper);
    });
  });

  sidebar.addEventListener('pointerenter', () => {
    if (!isMini() || isMobile()) return;
    cancelHide();
  });

  sidebar.addEventListener('pointerleave', (e) => {
    if (!isMini() || isMobile()) return;
    if (inCombined(e.relatedTarget)) return;
    scheduleHide();
  });

  subSidebar?.addEventListener('pointerenter', () => {
    if (!isMini() || isMobile()) return;
    cancelHide();
  });

  subSidebar?.addEventListener('pointerleave', (e) => {
    if (!isMini() || isMobile()) return;
    if (inCombined(e.relatedTarget)) return;
    scheduleHide();
  });

  // Keep open when clicking inside subSidebar
  subSidebar?.addEventListener('click', () => {
    if (!isMini() || isMobile()) return;
    cancelHide();
  });

  // Close subSidebar on outside click (desktop only)
  document.addEventListener('click', (e) => {
    if (isMobile()) return;
    if (subSidebar?.classList.contains('hidden')) return;
    if (inCombined(e.target)) return;
    hideSubSidebar();
  });

  closeSubBtn?.addEventListener('click', hideSubSidebar);

  /* ------------------ Keyboard Focus (Desktop Mini) ------------------ */
  dropdownWrappers.forEach(wrapper => {
    const btn = wrapper.querySelector('.dropdown-toggle');
    if (!btn) return;
    btn.setAttribute('tabindex', '0');
    btn.addEventListener('focus', () => {
      if (isMini() && !isMobile()) showSubSidebar(wrapper);
    });
    btn.addEventListener('blur', () => {
      if (isMini() && !isMobile()) {
        scheduleHide(120);
      }
    });
  });

  /* ------------------ Initial Collapse for Inline Dropdowns ------------------ */
  expandedBoxes.forEach(b => b.classList.add('hidden'));

  /* ------------------ Init ------------------ */
  function init() {
    let saved = null;
    try { saved = localStorage.getItem(stateKey); } catch {}
    const wantMini = saved === '1';

    if (isMobile()) {
      // Mobile: act as drawer, start hidden
      closeDrawer();
      sidebar.classList.remove('w-20');
      sidebar.classList.add('w-60');
      sidebar.setAttribute('data-mini','false');
      toggleBtn.setAttribute('data-mini','false');
    } else {
      setDesktopMode(wantMini);
      // Ensure visible on desktop
      sidebar.classList.remove('-translate-x-full');
      overlay?.classList.add('hidden');
    }
  }

  /* ------------------ Responsive Re-eval ------------------ */
  let resizeTimer = null;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      if (isMobile()) {
        hideSubSidebar();
        closeDrawer();
        sidebar.classList.remove('w-20');
        sidebar.classList.add('w-60');
        sidebar.setAttribute('data-mini','false');
        toggleBtn.setAttribute('data-mini','false');
        if (main) {
          main.classList.remove('xl:ml-20');
          main.classList.add('xl:ml-60');
        }
      } else {
        let saved = null;
        try { saved = localStorage.getItem(stateKey); } catch {}
        setDesktopMode(saved === '1');
        sidebar.classList.remove('-translate-x-full');
        overlay?.classList.add('hidden');
        document.body.classList.remove('body-lock');
      }
    }, 140);
  });

  init();
});


// add sidebar theme
document.addEventListener('DOMContentLoaded', () => {

  /* ===== Theme System Config ===== */
  const THEME_STORAGE_KEY = 'sidebarTheme';
  const sidebar = document.getElementById('sidebar');
  const cfg = (window.SidebarThemeConfig && window.SidebarThemeConfig.logos) || {};
  const lightLogo = cfg.light || '/assets/img/nextep-logo.webp';
  const darkLogo  = cfg.dark  || '/assets/img/nextep-logo-dark.webp';
  const iconLogo  = cfg.icon  || '/assets/img/nextep-icon.webp';


  // If you have alternate icon versions you can extend config.
  const themes = {
    'blue':       { bg:'#1d4ed8', light:false },
    'black':      { bg:'#111827', light:false },
    'dark-blue':  { bg:'#0f172a', light:false },
    'green':      { bg:'#047857', light:false },
    'light-blue': { bg:'#e0f2fe', light:true  },
  };

  /* ===== Elements ===== */
  const menuBtn    = document.getElementById('themeMenuBtn');
  const menu       = document.getElementById('themeMenu');
  const resetBtn   = document.getElementById('resetTheme');

  if (!sidebar || !menuBtn || !menu) return; // safety

  /* ===== Helpers ===== */
  const isMenuOpen = () => !menu.classList.contains('hidden');
  function openMenu() {
    menu.classList.remove('hidden');
    menuBtn.setAttribute('aria-expanded','true');
  }
  function closeMenu() {
    menu.classList.add('hidden');
    menuBtn.setAttribute('aria-expanded','false');
  }
  function toggleMenu() { isMenuOpen() ? closeMenu() : openMenu(); }

  function applyTheme(name, persist = true) {
    if (!name) {
      sidebar.removeAttribute('data-theme');
      restoreDefaultLogos();
      if (persist) localStorage.removeItem(THEME_STORAGE_KEY);
      highlightActive(null);
      return;
    }
    if (!themes[name]) return;
    sidebar.setAttribute('data-theme', name);

    // Decide which logo to show based on "light" property
    const meta = themes[name];
    swapLogos(meta.light);
    if (persist) {
      try { localStorage.setItem(THEME_STORAGE_KEY, name); } catch {}
    }
    highlightActive(name);
  }

  function swapLogos(isLightBackground) {
    // We have:
    //  - .sidebar-logo-full (two variants currently in DOM: light + dark)
    //  - .sidebar-logo-icon (icon)
    // Strategy: Hide both full logos, then inject correct one? Easier: toggle their display/opacity.
    const fullLight = Array.from(document.querySelectorAll('img.sidebar-logo-full.dark\\:hidden')); // original light
    const fullDark  = Array.from(document.querySelectorAll('img.sidebar-logo-full.dark\\:block'));  // original dark
    // Actually simpler: direct src swap on the first .sidebar-logo-full elements (both variants).
    const fullLogos = document.querySelectorAll('.sidebar-logo-full');

    fullLogos.forEach(img => {
      // Choose which file
      img.src = isLightBackground ? lightLogo : darkLogo;
      // Force show one, hide the other dark-mode specific classes won't matter because we unify them
      // Let dark-mode media queries remain for system dark if you prefer; else you could remove that logic.
    });
  }

  function restoreDefaultLogos() {
    // Reset to original logic: first has light version (non-dark), second has dark variant.
    const imgs = document.querySelectorAll('.sidebar-logo-full');
    if (imgs.length >= 1) imgs[0].src = lightLogo;
    if (imgs.length >= 2) imgs[1].src = darkLogo;
  }

  function highlightActive(name) {
    const swatches = menu.querySelectorAll('.theme-swatch');
    swatches.forEach(btn => {
      const t = btn.getAttribute('data-theme-choice');
      btn.classList.toggle('active', t === name);
    });
  }

  /* ===== Event Binding ===== */
  menuBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    toggleMenu();
  });

  document.addEventListener('click', (e) => {
    if (isMenuOpen() && !menu.contains(e.target) && e.target !== menuBtn)
      closeMenu();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isMenuOpen()) closeMenu();
  });

  menu.querySelectorAll('.theme-swatch').forEach(btn => {
    btn.addEventListener('click', () => {
      const choice = btn.getAttribute('data-theme-choice');
      applyTheme(choice, true);
      closeMenu();
    });
  });

  resetBtn?.addEventListener('click', () => {
    applyTheme(null, true);
    closeMenu();
  });

  /* ===== Initial Load ===== */
  (function initTheme() {
    let saved = null;
    try { saved = localStorage.getItem(THEME_STORAGE_KEY); } catch {}
    if (saved && themes[saved]) {
      applyTheme(saved, false);
    } else {
      highlightActive(null);
    }
  })();
});



// add user dropdown

document.addEventListener('DOMContentLoaded', () => {
  const wrapper     = document.getElementById('userMenuWrapper');
  if (!wrapper) return;

  const trigger     = wrapper.querySelector('[dropdown-trigger]');
  const menu        = wrapper.querySelector('[dropdown-menu]');
  const focusableSel = 'a[href], button:not([disabled]), [role="menuitem"]';

  let closeTimer = null;

  function openMenu() {
    menu.classList.add('show');
    trigger.setAttribute('aria-expanded', 'true');

    // Focus first item optionally:
    const first = menu.querySelector(focusableSel);
    if (first) first.focus({ preventScroll: true });
  }

  function closeMenu() {
    menu.classList.remove('show');
    trigger.setAttribute('aria-expanded', 'false');
  }

  function isOpen() {
    return menu.classList.contains('show');
  }

  function toggleMenu() {
    isOpen() ? closeMenu() : openMenu();
  }

  /* Click trigger */
  trigger.addEventListener('click', (e) => {
    e.stopPropagation();
    toggleMenu();
  });

  /* Keyboard: Enter / Space / ArrowDown to open */
  trigger.addEventListener('keydown', (e) => {
    if (['Enter',' ' ,'ArrowDown'].includes(e.key)) {
      e.preventDefault();
      if (!isOpen()) openMenu(); else {
        // cycle to first item
        const first = menu.querySelector(focusableSel);
        if (first) first.focus();
      }
    }
  });

  /* Close on outside click */
  document.addEventListener('click', (e) => {
    if (!isOpen()) return;
    if (wrapper.contains(e.target)) return;
    closeMenu();
  });

  /* Esc to close */
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen()) {
      closeMenu();
      trigger.focus();
    }
  });

  /* Basic focus containment (optional light trap) */
  menu.addEventListener('keydown', (e) => {
    if (!isOpen()) return;
    if (e.key === 'Tab') {
      const items = Array.from(menu.querySelectorAll(focusableSel));
      if (!items.length) return;
      const first = items[0];
      const last  = items[items.length - 1];

      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }
    if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
      e.preventDefault();
      const items = Array.from(menu.querySelectorAll(focusableSel));
      const idx = items.indexOf(document.activeElement);
      if (idx === -1) return;
      let nextIdx = e.key === 'ArrowDown' ? idx + 1 : idx - 1;
      if (nextIdx < 0) nextIdx = items.length - 1;
      if (nextIdx >= items.length) nextIdx = 0;
      items[nextIdx].focus();
    }
  });

  /* Close when a menu item is clicked (except if you want to keep it) */
  menu.addEventListener('click', (e) => {
    const roleItem = e.target.closest('[role="menuitem"]');
    if (roleItem) {
      // Delay close if it's a form submit (logout)
      if (roleItem.tagName.toLowerCase() === 'button') {
        // Let form submit proceed; closing is natural as page reloads.
      }
      closeMenu();
    }
  });

  // trigger.addEventListener('mouseenter', () => {
  //   clearTimeout(closeTimer);
  //   openMenu();
  // });
  // wrapper.addEventListener('mouseleave', () => {
  //   clearTimeout(closeTimer);
  //   closeTimer = setTimeout(closeMenu, 180);
  // });
  
});

</script>
