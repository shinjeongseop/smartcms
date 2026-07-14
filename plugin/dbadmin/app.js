const DBAdmin = (() => {
  const state = {
    currentDb: '',
    currentTable: '',
    page: 1,
    pageSize: 20,
    searchCol: '__all__',
    searchKeyword: '',
    orderCol: '',
    orderDir: 'DESC',
  };

  const els = {
    dbSelect: document.getElementById('dbSelect'),
    tableList: document.getElementById('tableList'),
    currentDbLabel: document.getElementById('currentDbLabel'),
    currentTableLabel: document.getElementById('currentTableLabel'),
    btnRefreshTables: document.getElementById('btnRefreshTables'),
    btnAddRow: document.getElementById('btnAddRow'),
    btnExportCsv: document.getElementById('btnExportCsv'),
    btnImportCsv: document.getElementById('btnImportCsv'),
    dataArea: document.getElementById('dataArea'),
    structureArea: document.getElementById('structureArea'),
    searchColSelect: document.getElementById('searchColSelect'),
    searchKeyword: document.getElementById('searchKeyword'),
    btnSearchFilter: document.getElementById('btnSearchFilter'),
    btnClearFilter: document.getElementById('btnClearFilter'),
    pageSize: document.getElementById('pageSize'),
    sqlResultWrap: document.getElementById('sqlResultWrap'),
    sqlMessage: document.getElementById('sqlMessage'),
    sqlResultThead: document.querySelector('#sqlResultTable thead'),
    sqlResultTbody: document.querySelector('#sqlResultTable tbody'),
    commonModalContent: document.getElementById('commonModalContent'),
    commonModalDialog: document.getElementById('commonModalDialog'),
    messageModalBody: document.getElementById('messageModalBody'),
  };

  const commonModal = UI.createModal(document.getElementById('commonModal'));
  const messageModal = UI.createModal(document.getElementById('messageModal'));

  function showMessage(message) {
    els.messageModalBody.innerHTML = message;
    messageModal.open();
  }

  async function loadDatabases() {
    try {
      const data = await Common.get(`${APP_CONFIG.dbadminUrl}/dbs.php`);
      els.dbSelect.innerHTML = data.databases.map((db) => `<option value="${Common.escapeHtml(db)}">${Common.escapeHtml(db)}</option>`).join('');

      if (data.databases.length > 0) {
        state.currentDb = data.databases[0];
        els.dbSelect.value = state.currentDb;
        await loadTables();
      }
    } catch (err) {
      showMessage(err.message);
    }
  }

  async function loadTables() {
    if (!state.currentDb) return;

    try {
      const data = await Common.get(`${APP_CONFIG.dbadminUrl}/dbs.php?mode=tables&db=${encodeURIComponent(state.currentDb)}`);
      renderTables(data.tables);
      updateHeader();
    } catch (err) {
      try {
        const data = await Common.get(`${APP_CONFIG.dbadminUrl}/tables.php?db=${encodeURIComponent(state.currentDb)}`);
        renderTables(data.tables);
        updateHeader();
      } catch (e) {
        showMessage(e.message);
      }
    }
  }

  function renderTables(tables) {
    if (!tables.length) {
      els.tableList.innerHTML = `<div class="is-muted">테이블이 없습니다.</div>`;
      return;
    }

    els.tableList.innerHTML = tables
      .map(
        (t) => `
            <div class="table-list__item ${state.currentTable === t.name ? 'table-list__item--active' : ''}">
              <div class="table-list__row">
                <button type="button" class="button button--link table-list__main btn-select-table" data-table="${Common.escapeHtml(t.name)}">
                  ${Common.escapeHtml(t.name)}
                  <span class="table-list__meta">Rows: ${t.rows} / ${Common.escapeHtml(t.engine || '')}</span>
                </button>
              </div>
            </div>
        `,
      )
      .join('');

    els.tableList.querySelectorAll('.btn-select-table').forEach((btn) => {
      btn.addEventListener('click', async () => {
        state.currentTable = btn.dataset.table;
        state.page = 1;
        state.searchKeyword = '';
        state.searchCol = '__all__';
        state.orderCol = '';
        state.orderDir = 'DESC';
        els.searchKeyword.value = '';
        els.searchColSelect.innerHTML = '<option value="__all__">전체 컬럼</option>';
        renderTables(tables);
        updateHeader();
        enableTableActions(true);
        updateExportUrl();
        await loadColumns();
        await loadList();
        await loadStructure();
      });
    });

  }

  function updateHeader() {
    els.currentDbLabel.textContent = `DB: ${state.currentDb || '-'}`;
    els.currentTableLabel.textContent = `TABLE: ${state.currentTable || '-'}`;
  }

  function enableTableActions(enabled) {
    els.btnAddRow.disabled = !enabled;
    if (enabled) {
      els.btnExportCsv.classList.remove('is-hidden');
      els.btnImportCsv.classList.remove('is-hidden');
    } else {
      els.btnExportCsv.classList.add('is-hidden');
      els.btnImportCsv.classList.add('is-hidden');
    }
  }

  function updateExportUrl() {
    const params = new URLSearchParams({
      db: state.currentDb, table: state.currentTable,
      search_col: state.searchCol, search_keyword: state.searchKeyword,
      order_col: state.orderCol, order_dir: state.orderDir,
    });
    els.btnExportCsv.href = `${APP_CONFIG.dbadminUrl}/export.php?${params}`;
  }

  async function loadColumns() {
    try {
      const data = await Common.get(
        `${APP_CONFIG.dbadminUrl}/dbs.php?mode=columns&db=${encodeURIComponent(state.currentDb)}&table=${encodeURIComponent(state.currentTable)}`
      );
      els.searchColSelect.innerHTML = '<option value="__all__">전체 컬럼</option>'
        + data.columns.map((c) => `<option value="${Common.escapeHtml(c.field)}">${Common.escapeHtml(c.field)}</option>`).join('');
    } catch (_) {}
  }

  async function loadList() {
    if (!state.currentDb || !state.currentTable) return;

    try {
      state.pageSize = parseInt(els.pageSize.value, 10) || 50;

      const params = new URLSearchParams({
        db: state.currentDb, table: state.currentTable,
        page: state.page, page_size: state.pageSize,
        search_col: state.searchCol, search_keyword: state.searchKeyword,
        order_col: state.orderCol, order_dir: state.orderDir,
      });
      const html = await Common.fetchText(`${APP_CONFIG.dbadminUrl}/list.php?${params}`);
      els.dataArea.innerHTML = html;
      bindListEvents();
      updateExportUrl();
    } catch (err) {
      showMessage(err.message);
    }
  }

  async function loadStructure() {
    if (!state.currentDb || !state.currentTable) return;

    try {
      const html = await Common.fetchText(`${APP_CONFIG.dbadminUrl}/structure.php?db=${encodeURIComponent(state.currentDb)}&table=${encodeURIComponent(state.currentTable)}`);
      els.structureArea.innerHTML = html;
      bindStructureEvents();
    } catch (err) {
      showMessage(err.message);
    }
  }

  function bindListEvents() {
    const btnPrev = document.getElementById('btnPrevPage');
    const btnNext = document.getElementById('btnNextPage');

    if (btnPrev) {
      btnPrev.addEventListener('click', async () => {
        if (state.page > 1) {
          state.page--;
          await loadList();
        }
      });
    }

    if (btnNext) {
      btnNext.addEventListener('click', async () => {
        state.page++;
        await loadList();
      });
    }

    document.querySelectorAll('.btn-edit-row').forEach((btn) => {
      btn.addEventListener('click', async () => { await openUpdateModal(btn.dataset.pk); });
    });

    document.querySelectorAll('.btn-delete-row').forEach((btn) => {
      btn.addEventListener('click', async () => { await deleteRow(btn.dataset.pk); });
    });

    document.querySelectorAll('.sort-header').forEach((a) => {
      a.addEventListener('click', async (e) => {
        e.preventDefault();
        state.orderCol = a.dataset.col;
        state.orderDir = a.dataset.dir;
        state.page = 1;
        await loadList();
      });
    });
  }

  async function doSearch() {
    state.searchCol = els.searchColSelect.value;
    state.searchKeyword = els.searchKeyword.value.trim();
    state.page = 1;
    await loadList();
  }

  function openImportModal() {
    els.commonModalDialog.className = 'modal__dialog';
    els.commonModalContent.innerHTML = `
      <div class="modal__header">
        <h2 class="modal__title">CSV 가져오기 - ${Common.escapeHtml(state.currentTable)}</h2>
        <button type="button" class="icon-btn" data-modal-close aria-label="닫기">×</button>
      </div>
      <form id="importForm" enctype="multipart/form-data">
        <div class="modal__body">
          <p class="help-text">첫 번째 행은 컬럼명이어야 합니다. AUTO_INCREMENT 컬럼은 자동 제외됩니다.</p>
          <input type="file" class="input" name="csv_file" accept=".csv" required>
        </div>
        <div class="modal__footer">
          <button type="button" class="button button--ghost" data-modal-close>닫기</button>
          <button type="submit" class="button button--primary">업로드</button>
        </div>
      </form>`;
    commonModal.open();

    document.getElementById('importForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      fd.append('db', state.currentDb);
      fd.append('table', state.currentTable);
      try {
        const res = await fetch(`${APP_CONFIG.dbadminUrl}/import.php`, { method: 'POST', body: fd });
        const json = await res.json();
        commonModal.close();
        await loadList();
        showMessage(json.message || (json.success ? '완료' : '실패'));
      } catch (err) { showMessage(err.message); }
    });
  }

  async function loadModal(url, dialogClass = 'modal__dialog--xl') {
    els.commonModalDialog.className = `modal__dialog ${dialogClass}`;
    els.commonModalContent.innerHTML = `<div class="modal__body text-center">로딩중...</div>`;
    commonModal.open();

    const html = await Common.fetchText(url);
    els.commonModalContent.innerHTML = html;
  }

  async function openWriteModal() {
    await loadModal(`${APP_CONFIG.dbadminUrl}/write.php?db=${encodeURIComponent(state.currentDb)}&table=${encodeURIComponent(state.currentTable)}`);
    bindRowForm();
  }

  async function openUpdateModal(pk) {
    await loadModal(`${APP_CONFIG.dbadminUrl}/update.php?db=${encodeURIComponent(state.currentDb)}&table=${encodeURIComponent(state.currentTable)}&pk=${encodeURIComponent(pk)}`);
    bindRowForm();
  }

  function bindRowForm() {
    const form = document.getElementById('rowForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const mode = form.dataset.mode;
      const formData = Common.serializeForm(form);
      const pkValue = form.dataset.pk || null;

      try {
        await Common.post(`${APP_CONFIG.dbadminUrl}/${mode === 'insert' ? 'save_row.php' : 'save_row.php'}`, {
          db: state.currentDb,
          table: state.currentTable,
          mode,
          form_data: formData,
          pk_value: pkValue,
        });
      } catch (err) {
        try {
          await Common.post(`${APP_CONFIG.dbadminUrl}/save_row.php`, {
            db: state.currentDb,
            table: state.currentTable,
            mode,
            form_data: formData,
            pk_value: pkValue,
          });
        } catch (e2) {
          showMessage(e2.message);
          return;
        }
      }

      commonModal.close();
      await loadList();
      await loadStructure();
      showMessage(mode === 'insert' ? '등록 완료' : '수정 완료');
    });
  }

  async function deleteRow(pk) {
    if (!confirm(`정말 삭제하시겠습니까?\nPK: ${pk}`)) return;

    try {
      await Common.post(`${APP_CONFIG.dbadminUrl}/delete.php`, {
        db: state.currentDb,
        table: state.currentTable,
        pk_value: pk,
      });

      await loadList();
      await loadStructure();
      showMessage('삭제 완료');
    } catch (err) {
      showMessage(err.message);
    }
  }

  function bindStructureEvents() {
    document.querySelectorAll('.fk-table-link').forEach((a) => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        const targetTable = a.dataset.table;
        const btn = els.tableList.querySelector(`.btn-select-table[data-table="${CSS.escape(targetTable)}"]`);
        if (btn) btn.click();
        else showMessage(`[${targetTable}] 테이블이 현재 DB에 없습니다.`);
      });
    });
  }

  function bindEvents() {
    els.dbSelect.addEventListener('change', async (e) => {
      state.currentDb = e.target.value;
      state.currentTable = '';
      state.page = 1;
      updateHeader();
      enableTableActions(false);
      els.dataArea.innerHTML = '';
      els.structureArea.innerHTML = '';
      await loadTables();
    });

    if (els.btnRefreshTables) {
      els.btnRefreshTables.addEventListener('click', loadTables);
    }
    els.btnAddRow.addEventListener('click', openWriteModal);
    els.btnImportCsv.addEventListener('click', openImportModal);

    els.btnSearchFilter.addEventListener('click', doSearch);
    els.btnClearFilter.addEventListener('click', async () => {
      els.searchKeyword.value = '';
      els.searchColSelect.value = '__all__';
      state.searchKeyword = '';
      state.searchCol = '__all__';
      state.page = 1;
      await loadList();
    });
    els.searchKeyword.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') doSearch();
    });

    els.pageSize.addEventListener('change', async () => {
      state.page = 1;
      await loadList();
    });

  }

  async function init() {
    bindEvents();
    UI.bindTabs();
    updateHeader();
    enableTableActions(false);
    await loadDatabases();
  }

  return { init };
})();

document.addEventListener('DOMContentLoaded', DBAdmin.init);
