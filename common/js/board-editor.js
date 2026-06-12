(function () {
  document.addEventListener('DOMContentLoaded', function () {
    if (!window.Jodit) {
      return;
    }

    document.querySelectorAll('[data-ob-editor="jodit"], [data-board-editor="jodit"]').forEach(function (textarea) {
      if (!(textarea instanceof HTMLTextAreaElement)) {
        return;
      }

      if (textarea.dataset.obEditorReady === '1' || textarea.dataset.boardEditorReady === '1') {
        return;
      }

      textarea.dataset.obEditorReady = '1';
      textarea.dataset.boardEditorReady = '1';

      window.Jodit.make(textarea, {
        height: 420,
        toolbarAdaptive: false,
        readonly: false,
        uploader: {
          url: textarea.dataset.obUploadUrl || '',
          format: 'json',
          method: 'POST',
          filesVariableName: function () {
            return 'files';
          },
          prepareData: function (formData) {
            formData.append('code', textarea.dataset.obBoardCode || '');
            formData.append('csrf_token', textarea.dataset.obCsrf || '');
            return formData;
          },
          isSuccess: function (resp) {
            return !!(resp && resp.success);
          },
          getMessage: function (resp) {
            return resp && resp.message ? resp.message : '업로드에 실패했습니다.';
          },
          process: function (resp) {
            return {
              files: resp.files || [],
              path: resp.path || '',
              baseurl: resp.baseurl || '',
              error: resp.error || 0,
              message: resp.message || ''
            };
          },
          defaultHandlerSuccess: function (data) {
            (data.files || []).forEach(function (url) {
              this.selection.insertImage(url);
            }, this);
          }
        },
        buttons: [
          'bold', 'italic', 'underline', '|',
          'ul', 'ol', '|',
          'link', 'image', 'table', '|',
          'left', 'center', 'right', '|',
          'undo', 'redo', '|',
          'source'
        ]
      });
    });
  });
}());
