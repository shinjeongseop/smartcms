(function () {
  function getFormItems(form) {
    if (!(form instanceof HTMLFormElement) || !form.id) {
      return [];
    }

    return Array.from(document.querySelectorAll('[data-board-bulk-item][form="' + form.id + '"]')).filter(function (item) {
      return item instanceof HTMLInputElement;
    });
  }

  function getActionInput(form) {
    if (!(form instanceof HTMLFormElement)) {
      return null;
    }

    var actionInput = form.querySelector('[data-board-bulk-action-input]');
    return actionInput instanceof HTMLInputElement ? actionInput : null;
  }

  function getSelectAll(form) {
    if (!(form instanceof HTMLFormElement) || !form.id) {
      return null;
    }

    var selectAll = document.querySelector('[data-board-bulk-select-all][form="' + form.id + '"]');
    if (selectAll instanceof HTMLInputElement) {
      return selectAll;
    }

    selectAll = form.querySelector('[data-board-bulk-select-all]');
    return selectAll instanceof HTMLInputElement ? selectAll : null;
  }

  function syncSelectAll(form) {
    var selectAll = getSelectAll(form);
    if (!(selectAll instanceof HTMLInputElement)) {
      return;
    }

    var items = getFormItems(form);
    var checkedCount = items.filter(function (item) {
      return item.checked;
    }).length;

    selectAll.checked = items.length > 0 && checkedCount === items.length;
    selectAll.indeterminate = checkedCount > 0 && checkedCount < items.length;
  }

  function getModalRefs(form) {
    if (!(form instanceof HTMLFormElement) || !form.id) {
      return {};
    }

    var modalId = form.id + '_modal';
    return {
      element: document.getElementById(modalId),
      title: document.getElementById(modalId + '_title'),
      body: document.getElementById(modalId + '_body'),
      target: document.getElementById(modalId + '_target'),
      submit: document.getElementById(modalId + '_submit')
    };
  }

  function getCheckedCount(form) {
    return getFormItems(form).filter(function (item) {
      return item.checked;
    }).length;
  }

  function submitBulkForm(form, action) {
    var actionInput = getActionInput(form);
    if (!(actionInput instanceof HTMLInputElement)) {
      return false;
    }

    actionInput.value = action;
    form.submit();
    return true;
  }

  function confirmDelete() {
    return window.confirm('선택한 글을 삭제하시겠습니까?');
  }

  function openBulkModal(form, action) {
    var modalRefs = getModalRefs(form);
    if (!(modalRefs.element instanceof HTMLElement) || !(modalRefs.title instanceof HTMLElement) || !(modalRefs.body instanceof HTMLElement) || !(modalRefs.target instanceof HTMLSelectElement) || !(modalRefs.submit instanceof HTMLButtonElement)) {
      return false;
    }

    if (!window.bootstrap || typeof window.bootstrap.Modal !== 'function') {
      return false;
    }

    var checkedCount = getCheckedCount(form);
    var actionLabel = action === 'move' ? '이동' : '복사';
    var actionInput = getActionInput(form);
    if (!(actionInput instanceof HTMLInputElement)) {
      return false;
    }

    modalRefs.title.textContent = actionLabel + ' 확인';
    modalRefs.body.textContent = '선택한 글 ' + checkedCount + '개를 ' + actionLabel + '할 대상 게시판을 선택해 주세요.';
    modalRefs.submit.textContent = actionLabel;
    modalRefs.submit.className = 'btn ' + (action === 'move' ? 'btn-primary' : 'btn-secondary') + ' fw-bold';
    modalRefs.target.value = '';

    var modal = window.bootstrap.Modal.getOrCreateInstance(modalRefs.element);

    modalRefs.element.addEventListener('shown.bs.modal', function focusTarget() {
      modalRefs.target.focus();
    }, { once: true });

    modalRefs.submit.onclick = function () {
      if (String(modalRefs.target.value || '').trim() === '') {
        window.alert('대상 게시판을 선택하세요.');
        modalRefs.target.focus();
        return;
      }

      actionInput.value = action;
      modal.hide();
      form.submit();
    };

    modalRefs.element.addEventListener('hidden.bs.modal', function clearHandler() {
      modalRefs.submit.onclick = null;
      modalRefs.element.removeEventListener('hidden.bs.modal', clearHandler);
    });

    modal.show();
    return true;
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-board-bulk-form]').forEach(function (form) {
      if (!(form instanceof HTMLFormElement) || !form.id) {
        return;
      }

      var selectAll = getSelectAll(form);
      var actionButtons = Array.from(form.querySelectorAll('[data-board-bulk-action]')).filter(function (button) {
        return button instanceof HTMLButtonElement;
      });

      if (selectAll instanceof HTMLInputElement) {
        selectAll.addEventListener('change', function () {
          getFormItems(form).forEach(function (item) {
            item.checked = selectAll.checked;
          });
          syncSelectAll(form);
        });
      }

      getFormItems(form).forEach(function (item) {
        item.addEventListener('change', function () {
          syncSelectAll(form);
        });
      });

      actionButtons.forEach(function (button) {
        button.addEventListener('click', function () {
          var action = String(button.getAttribute('data-board-bulk-action') || '');
          var checkedCount = getCheckedCount(form);

          if (checkedCount === 0) {
            window.alert('선택한 글이 없습니다.');
            return;
          }

          if (action === 'delete') {
            if (!confirmDelete()) {
              return;
            }

            if (!submitBulkForm(form, action)) {
              window.alert('삭제를 처리할 수 없습니다.');
            }
            return;
          }

          if (action === 'move' || action === 'copy') {
            if (openBulkModal(form, action)) {
              return;
            }

            window.alert('대상 게시판 선택 창을 열 수 없습니다.');
          }
        });
      });

      syncSelectAll(form);
    });
  });
}());
