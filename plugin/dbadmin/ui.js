const UI = (() => {
  function createModal(root) {
    function close() {
      root.hidden = true;
      root.classList.remove('modal--open');
      document.body.classList.remove('modal-open');
    }

    function open() {
      root.hidden = false;
      root.classList.add('modal--open');
      document.body.classList.add('modal-open');
      const focusable = root.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
      if (focusable) focusable.focus();
    }

    root.addEventListener('click', (event) => {
      if (event.target === root || event.target.closest('[data-modal-close]')) {
        close();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !root.hidden) close();
    });

    return { open, close };
  }

  function getModalRoot(id) {
    return typeof id === 'string' ? document.getElementById(id) : id;
  }

  function openModal(id) {
    const root = getModalRoot(id);
    if (!root) return null;
    const modal = createModal(root);
    modal.open();
    return modal;
  }

  function closeModal(id) {
    const root = getModalRoot(id);
    if (!root) return;
    root.hidden = true;
    root.classList.remove('modal--open');
    document.body.classList.remove('modal-open');
  }

  function setModalContent(id, html, size = 'default') {
    const root = getModalRoot(id);
    if (!root) return;
    const dialog = root.querySelector('.modal__dialog');
    const content = root.querySelector('.modal__content');
    if (dialog) {
      dialog.className = 'modal__dialog';
      if (size === 'lg') dialog.classList.add('modal__dialog--lg');
      if (size === 'xl') dialog.classList.add('modal__dialog--xl');
    }
    if (content) content.innerHTML = html;
  }

  function bindTabs(root = document) {
    root.querySelectorAll('[data-tab-target]').forEach((button) => {
      button.addEventListener('click', () => {
        const targetId = button.dataset.tabTarget;
        const tabs = button.closest('.tabs');
        if (tabs) {
          tabs.querySelectorAll('[data-tab-target]').forEach((item) => {
            item.classList.toggle('tabs__button--active', item === button);
          });
        }
        root.querySelectorAll('.tab-panel').forEach((panel) => {
          panel.classList.toggle('tab-panel--active', panel.id === targetId);
        });
      });
    });
  }

  function toggleHidden(el, hidden) {
    if (!el) return;
    el.classList.toggle('is-hidden', hidden);
  }

  return { createModal, openModal, closeModal, setModalContent, bindTabs, toggleHidden };
})();
