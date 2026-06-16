(function () {
  function getFormItems(form) {
    if (!(form instanceof HTMLFormElement) || !form.id) {
      return [];
    }

    return Array.from(document.querySelectorAll('[data-board-bulk-item][form="' + form.id + '"]')).filter(function (item) {
      return item instanceof HTMLInputElement;
    });
  }

  function syncSelectAll(form) {
    var selectAll = form.querySelector('[data-board-bulk-select-all]');
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

  function openBulkModal(form, action, submitter) {
    var modalRefs = getModalRefs(form);
    if (!(modalRefs.element instanceof HTMLElement) || !(modalRefs.title instanceof HTMLElement) || !(modalRefs.body instanceof HTMLElement) || !(modalRefs.target instanceof HTMLSelectElement) || !(modalRefs.submit instanceof HTMLButtonElement)) {
      return false;
    }

    if (!window.bootstrap || typeof window.bootstrap.Modal !== 'function') {
      return false;
    }

    var actionLabel = action === 'move' ? '이동' : '복사';
    var checkedCount = getCheckedCount(form);

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

      form.dataset.boardBulkConfirmedAction = action;
      modal.hide();
      if (submitter && typeof form.requestSubmit === 'function') {
        form.requestSubmit(submitter);
        return;
      }
      form.submit();
    };

    modalRefs.element.addEventListener('hidden.bs.modal', function clearHandler() {
      modalRefs.submit.onclick = null;
      modalRefs.element.removeEventListener('hidden.bs.modal', clearHandler);
    });

    modal.show();
    return true;
  }

  function confirmDelete() {
    return window.confirm('선택한 글을 삭제하시겠습니까?');
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-board-bulk-form]').forEach(function (form) {
      if (!(form instanceof HTMLFormElement) || !form.id) {
        return;
      }

      var selectAll = form.querySelector('[data-board-bulk-select-all]');
      var items = getFormItems(form);
      var actionButtons = Array.from(form.querySelectorAll('[data-board-bulk-action]')).filter(function (button) {
        return button instanceof HTMLButtonElement;
      });

      if (selectAll instanceof HTMLInputElement) {
        selectAll.addEventListener('change', function () {
          items.forEach(function (item) {
            item.checked = selectAll.checked;
          });
          syncSelectAll(form);
        });
      }

      items.forEach(function (item) {
        item.addEventListener('change', function () {
          syncSelectAll(form);
        });
      });

      actionButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
          var action = String(button.value || '');
          var checkedCount = getCheckedCount(form);

          if (checkedCount === 0) {
            event.preventDefault();
            window.alert('선택한 글이 없습니다.');
            return;
          }

          if (action === 'delete') {
            event.preventDefault();
            if (!confirmDelete()) {
              return;
            }

            form.dataset.boardBulkConfirmedAction = action;
            if (typeof form.requestSubmit === 'function') {
              form.requestSubmit(button);
              return;
            }
            form.submit();
            return;
          }

          if (action === 'move' || action === 'copy') {
            event.preventDefault();
            if (openBulkModal(form, action, button)) {
              return;
            }

            if (!window.confirm('선택한 글을 ' + (action === 'move' ? '이동' : '복사') + '하시겠습니까?')) {
              return;
            }

            form.dataset.boardBulkConfirmedAction = action;
            if (typeof form.requestSubmit === 'function') {
              form.requestSubmit(button);
              return;
            }
            form.submit();
          }
        });
      });

      form.addEventListener('submit', function (event) {
        var submitter = event.submitter;
        var action = submitter && submitter.value ? String(submitter.value) : '';
        var checkedCount = getCheckedCount(form);

        if (checkedCount === 0) {
          event.preventDefault();
          window.alert('선택한 글이 없습니다.');
          return;
        }

        if (action === 'move' || action === 'copy') {
          if (String(form.dataset.boardBulkConfirmedAction || '') === action) {
            delete form.dataset.boardBulkConfirmedAction;
            return;
          }

          event.preventDefault();
          if (!openBulkModal(form, action, submitter instanceof HTMLButtonElement ? submitter : null)) {
            if (!window.confirm('선택한 글을 ' + (action === 'move' ? '이동' : '복사') + '하시겠습니까?')) {
              return;
            }
            form.dataset.boardBulkConfirmedAction = action;
            if (submitter && typeof form.requestSubmit === 'function') {
              form.requestSubmit(submitter);
              return;
            }
            form.submit();
          }
          return;
        }

        if (action === 'delete') {
          if (String(form.dataset.boardBulkConfirmedAction || '') === action) {
            delete form.dataset.boardBulkConfirmedAction;
            return;
          }

          event.preventDefault();
          if (!confirmDelete()) {
            return;
          }

          form.dataset.boardBulkConfirmedAction = action;
          if (submitter && typeof form.requestSubmit === 'function') {
            form.requestSubmit(submitter);
            return;
          }
          form.submit();
        }
      });

      syncSelectAll(form);
    });
  });
}());
