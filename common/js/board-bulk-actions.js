(function () {
  function getActionLabel(action) {
    return action === 'move' ? '이동' : '복사';
  }

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

  function getModal(form) {
    if (!(form instanceof HTMLFormElement) || !form.id) {
      return {};
    }

    var modalId = form.id + '_modal';
    return {
      element: document.getElementById(modalId),
      title: document.getElementById(modalId + '_title'),
      body: document.getElementById(modalId + '_body'),
      submit: document.getElementById(modalId + '_submit')
    };
  }

  function showBulkModal(form, action, targetBoardText, submitter) {
    if (!window.bootstrap || typeof window.bootstrap.Modal !== 'function') {
      return false;
    }

    var modalRefs = getModal(form);
    if (!(modalRefs.element instanceof HTMLElement) || !(modalRefs.title instanceof HTMLElement) || !(modalRefs.body instanceof HTMLElement) || !(modalRefs.submit instanceof HTMLButtonElement)) {
      return false;
    }

    var actionLabel = getActionLabel(action);
    var checkedCount = getFormItems(form).filter(function (item) {
      return item.checked;
    }).length;

    modalRefs.title.textContent = actionLabel + ' 확인';
    modalRefs.body.textContent = '선택한 글 ' + checkedCount + '개를 "' + targetBoardText + '"으로 ' + actionLabel + '하시겠습니까?';
    modalRefs.submit.textContent = actionLabel;
    modalRefs.submit.className = 'btn ' + (action === 'move' ? 'btn-primary' : 'btn-secondary') + ' fw-bold';
    modalRefs.submit.dataset.boardBulkAction = action;
    modalRefs.submit.dataset.boardBulkSubmitter = submitter ? '1' : '0';

    var modal = window.bootstrap.Modal.getOrCreateInstance(modalRefs.element);
    modal.show();

    modalRefs.submit.onclick = function () {
      form.dataset.boardBulkConfirming = action;
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

    return true;
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-board-bulk-form]').forEach(function (form) {
      if (!(form instanceof HTMLFormElement) || !form.id) {
        return;
      }

      var selectAll = form.querySelector('[data-board-bulk-select-all]');
      var items = getFormItems(form);

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

      form.addEventListener('submit', function (event) {
        var submitter = event.submitter;
        var action = submitter && submitter.value ? String(submitter.value) : '';
        var checkedItems = items.filter(function (item) {
          return item.checked;
        });

        if (action === 'move' || action === 'copy') {
          var pendingAction = String(form.dataset.boardBulkConfirming || '');
          if (pendingAction === action) {
            delete form.dataset.boardBulkConfirming;
            return;
          }
        }

        if (checkedItems.length === 0) {
          event.preventDefault();
          window.alert('선택한 글이 없습니다.');
          return;
        }

        if (action === 'move' || action === 'copy') {
          var targetBoard = form.querySelector('[name="target_board"]');
          if (!(targetBoard instanceof HTMLSelectElement) || targetBoard.value.trim() === '') {
            event.preventDefault();
            window.alert('대상 게시판을 선택하세요.');
            targetBoard && targetBoard.focus();
            return;
          }
        }

        if (action === 'delete' && !window.confirm('선택한 글을 삭제하시겠습니까?')) {
          event.preventDefault();
          return;
        }

        if (action === 'move' || action === 'copy') {
          event.preventDefault();
          var targetBoard = form.querySelector('[name="target_board"]');
          var targetBoardText = targetBoard instanceof HTMLSelectElement && targetBoard.selectedIndex >= 0
            ? targetBoard.options[targetBoard.selectedIndex].textContent.trim()
            : '';

          if (!targetBoardText) {
            targetBoardText = '대상 게시판';
          }

          if (showBulkModal(form, action, targetBoardText, submitter)) {
            return;
          }

          if (!window.confirm('선택한 글을 ' + (action === 'move' ? '이동' : '복사') + '하시겠습니까?')) {
            return;
          }

          form.dataset.boardBulkConfirming = action;
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
