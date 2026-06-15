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

        if ((action === 'move' || action === 'copy') && !window.confirm('선택한 글을 ' + (action === 'move' ? '이동' : '복사') + '하시겠습니까?')) {
          event.preventDefault();
        }
      });

      syncSelectAll(form);
    });
  });
}());
