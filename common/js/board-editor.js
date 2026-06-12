document.addEventListener('DOMContentLoaded', () => {
  const textareas = document.querySelectorAll('[data-board-editor="jodit"]');
  if (!textareas.length || !window.Jodit) {
    return;
  }

  textareas.forEach((textarea) => {
    if (!(textarea instanceof HTMLTextAreaElement)) {
      return;
    }

    if (textarea.dataset.boardEditorReady === '1') {
      return;
    }

    textarea.dataset.boardEditorReady = '1';

    window.Jodit.make(textarea, {
      height: 360,
      toolbarAdaptive: false,
      readonly: false,
      buttons: [
        'bold', 'italic', 'underline', '|',
        'ul', 'ol', '|',
        'link', '|',
        'undo', 'redo', '|',
        'source'
      ]
    });
  });
});
