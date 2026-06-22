document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) {
    window.lucide.createIcons({ attrs: { 'aria-hidden': 'true' } });
  }

  const mobileNav = document.querySelector('[data-mobile-nav]');
  const mobileNavToggle = document.querySelector('[data-mobile-nav-toggle]');

  mobileNavToggle?.addEventListener('click', () => {
    const isHidden = mobileNav?.classList.toggle('hidden') ?? true;
    mobileNavToggle.setAttribute('aria-expanded', String(!isHidden));
  });

  const sidebar = document.querySelector('[data-admin-sidebar]');
  const overlay = document.querySelector('[data-admin-overlay]');
  const sidebarOpen = document.querySelector('[data-admin-sidebar-open]');
  const sidebarClose = document.querySelector('[data-admin-sidebar-close]');

  const setSidebar = (open) => {
    sidebar?.classList.toggle('-translate-x-full', !open);
    overlay?.classList.toggle('hidden', !open);
    document.body.classList.toggle('overflow-hidden', open);
    sidebarOpen?.setAttribute('aria-expanded', String(open));
  };

  sidebarOpen?.addEventListener('click', () => setSidebar(true));
  sidebarClose?.addEventListener('click', () => setSidebar(false));
  overlay?.addEventListener('click', () => setSidebar(false));

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setSidebar(false);
    }
  });

  const tabs = [...document.querySelectorAll('[data-tab-target]')];
  const panels = [...document.querySelectorAll('[data-tab-panel]')];

  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      const target = tab.dataset.tabTarget;
      tabs.forEach((item) => {
        const active = item === tab;
        item.setAttribute('aria-selected', String(active));
        item.classList.toggle('border-brand-500', active);
        item.classList.toggle('text-brand-700', active);
        item.classList.toggle('border-transparent', !active);
        item.classList.toggle('text-slate-500', !active);
      });
      panels.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.tabPanel !== target));
    });
  });

  const adminSearch = document.querySelector('[data-admin-search]');
  const memberRows = [...document.querySelectorAll('[data-member-row]')];
  const emptyState = document.querySelector('[data-member-empty]');

  adminSearch?.addEventListener('input', () => {
    const query = adminSearch.value.trim().toLowerCase();
    let visible = 0;
    memberRows.forEach((row) => {
      const match = row.textContent.toLowerCase().includes(query);
      row.classList.toggle('hidden', !match);
      visible += Number(match);
    });
    emptyState?.classList.toggle('hidden', visible > 0);
  });
});
