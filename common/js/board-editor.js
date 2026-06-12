document.addEventListener('DOMContentLoaded', () => {
  const editorInputs = document.querySelectorAll('[data-board-editor="jodit"]');
  if (!editorInputs.length) {
    return;
  }

  editorInputs.forEach((textarea) => {
    if (!(textarea instanceof HTMLTextAreaElement)) {
      return;
    }

    const form = textarea.closest('form');
    if (!form) {
      return;
    }

    const modeInputs = form.querySelectorAll('input[name="content_mode_select"]');
    const hiddenMode = form.querySelector('input[name="content_mode"]');
    const initialMode = hiddenMode && hiddenMode.value === 'text' ? 'text' : 'editor';
    let editor = null;

    const syncMode = (mode) => {
      const nextMode = mode === 'text' ? 'text' : 'editor';
      if (hiddenMode) {
        hiddenMode.value = nextMode;
      }

      modeInputs.forEach((input) => {
        if (input instanceof HTMLInputElement) {
          input.checked = input.value === nextMode;
        }
      });
    };

    const destroyEditor = () => {
      if (!editor) {
        return;
      }

      textarea.value = editor.value || textarea.value;
      if (typeof editor.destruct === 'function') {
        editor.destruct();
      } else if (typeof editor.destroy === 'function') {
        editor.destroy();
      }
      textarea.classList.remove('d-none');
      textarea.style.display = '';
      editor = null;
    };

    const plainText = (html) => {
      const probe = document.createElement('div');
      probe.innerHTML = String(html || '');
      return (probe.textContent || '').replace(/\u00a0/g, ' ').trim();
    };

    const createEditor = () => {
      if (editor || !window.Jodit) {
        return;
      }

      editor = window.Jodit.make(textarea, {
        height: 360,
        toolbarAdaptive: false,
        buttons: [
          'bold', 'italic', 'underline', '|',
          'ul', 'ol', '|',
          'link', '|',
          'undo', 'redo', '|',
          'source'
        ]
      });
    };

    const setMode = (mode, updateTextarea = true) => {
      const nextMode = mode === 'text' ? 'text' : 'editor';
      syncMode(nextMode);

      if (nextMode === 'editor') {
        if (updateTextarea && editor) {
          textarea.value = editor.value || textarea.value;
        }
        createEditor();
      } else {
        destroyEditor();
        textarea.required = true;
        textarea.classList.remove('d-none');
      }

      textarea.required = nextMode === 'text';
    };

    modeInputs.forEach((input) => {
      if (!(input instanceof HTMLInputElement)) {
        return;
      }

      input.addEventListener('change', () => {
        setMode(input.value === 'text' ? 'text' : 'editor');
      });
    });

    form.addEventListener('submit', (event) => {
      if (editor) {
        const value = editor.value || '';
        if (plainText(value) === '') {
          event.preventDefault();
          textarea.focus();
          return;
        }

        textarea.value = value;
      }
    });

    setMode(initialMode, false);
  });
});
