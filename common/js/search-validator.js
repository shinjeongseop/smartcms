document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll('form[role="search"], form[data-search-min-length]');

  forms.forEach((form) => {
    if (!(form instanceof HTMLFormElement)) {
      return;
    }

    const input = form.querySelector('input[type="search"]');
    if (!(input instanceof HTMLInputElement)) {
      return;
    }

    const minLength = Number(form.dataset.searchMinLength || input.dataset.searchMinLength || 2);
    const emptyMessage = form.dataset.searchEmptyMessage || '검색어를 입력하세요.';
    const minMessage = form.dataset.searchMinMessage || `검색어는 ${minLength}글자 이상 입력하세요.`;

    form.addEventListener('submit', (event) => {
      const value = input.value.trim();

      if (value.length === 0) {
        event.preventDefault();
        window.alert(emptyMessage);
        input.focus();
        return;
      }

      if (value.length < minLength) {
        event.preventDefault();
        window.alert(minMessage);
        input.focus();
      }
    });
  });
});
