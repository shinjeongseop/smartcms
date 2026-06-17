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
    sqlEditor: document.getElementById('sqlEditor'),
    btnRunSql: document.getElementById('btnRunSql'),
    btnClearSql: document.getElementById('btnClearSql'),
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
                <div class="cluster">
                  <button type="button" class="button button--xs button--warning btn-rename-table" data-table="${Common.escapeHtml(t.name)}">rename</button>
                  <button type="button" class="button button--xs button--danger btn-drop-table" data-table="${Common.escapeHtml(t.name)}">drop</button>
                </div>
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

    els.tableList.querySelectorAll('.btn-rename-table').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        renameTable(btn.dataset.table);
      });
    });

    els.tableList.querySelectorAll('.btn-drop-table').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        dropTable(btn.dataset.table);
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

  async function runSql() {
    const sql = document.getElementById('sqlEditor').value.trim();

    if (!sql) {
      showMessage('SQL을 입력하세요.');
      return;
    }

    try {
      const result = await Common.post(`${APP_CONFIG.dbadminUrl}/sql.php`, {
        db: state.currentDb,
        sql,
      });

      renderSqlResult(result);
    } catch (error) {
      showMessage(error.message);
    }
  }

  function renderSqlResult(result) {
    const wrap = document.getElementById('sqlResultWrap');
    const message = document.getElementById('sqlMessage');
    const thead = document.querySelector('#sqlResultTable thead');
    const tbody = document.querySelector('#sqlResultTable tbody');

    wrap.classList.remove('is-hidden');
    thead.innerHTML = '';
    tbody.innerHTML = '';

    if (!result) {
      message.className = 'alert alert--danger';
      message.innerHTML = '응답 데이터가 없습니다.';
      return;
    }

    // SELECT 결과
    if (result.type === 'select') {
      const columns = Array.isArray(result.columns) ? result.columns : [];
      const rows = Array.isArray(result.rows) ? result.rows : [];

      message.className = 'alert alert--success';
      message.innerHTML = `조회 완료 (${rows.length}건)`;

      if (columns.length === 0) {
        thead.innerHTML = '<tr><th>결과 없음</th></tr>';
        tbody.innerHTML = '<tr><td>조회된 컬럼이 없습니다.</td></tr>';
        return;
      }

      thead.innerHTML = `
            <tr>
                ${columns.map((col) => `<th>${Common.escapeHtml(col)}</th>`).join('')}
            </tr>
        `;

      if (rows.length === 0) {
        tbody.innerHTML = `
                <tr>
                    <td colspan="${columns.length}" class="text-center is-muted">조회 결과가 없습니다.</td>
                </tr>
            `;
        return;
      }

      tbody.innerHTML = rows
        .map(
          (row) => `
            <tr>
                ${columns.map((col) => `<td class="cell-pre">${Common.formatCell(row[col] ?? '')}</td>`).join('')}
            </tr>
        `,
        )
        .join('');

      return;
    }

    // 실행 결과 (INSERT/UPDATE/DELETE/CREATE/ALTER 등)
    if (result.type === 'execute') {
      const affectedRows = Number(result.affected_rows ?? 0);

      message.className = 'alert alert--info';
      message.innerHTML = `
            실행 완료<br>
            영향 행 수: <strong>${affectedRows}</strong>
        `;

      thead.innerHTML = '<tr><th>실행 결과</th></tr>';
      tbody.innerHTML = `
            <tr>
                <td>SQL이 정상 실행되었습니다.</td>
            </tr>
        `;
      return;
    }

    // 기타 예외 응답
      message.className = 'alert alert--warning';
    message.innerHTML = '알 수 없는 응답 형식입니다.';
    thead.innerHTML = '<tr><th>응답</th></tr>';
    tbody.innerHTML = `
        <tr>
            <td><pre>${Common.escapeHtml(JSON.stringify(result, null, 2))}</pre></td>
        </tr>
    `;
  }

  // ─── Table DDL ───────────────────────────────────────────────
  async function createTable() {
    if (!state.currentDb) { showMessage('DB를 먼저 선택하세요.'); return; }
    const name = prompt('생성할 테이블명을 입력하세요:');
    if (!name) return;
    const comment = prompt('테이블 코멘트 (선택사항, 건너뛰려면 확인):') ?? '';
    try {
      await Common.post(`${APP_CONFIG.dbadminUrl}/table_ddl.php`, {
        action: 'create', db: state.currentDb, table: name, engine: 'InnoDB', comment,
      });
      await loadTables();
      showMessage(`[${name}] 테이블이 생성되었습니다.`);
    } catch (err) { showMessage(err.message); }
  }

  async function renameTable(tableName) {
    const newName = prompt(`[${tableName}] 테이블의 새 이름을 입력하세요:`, tableName);
    if (!newName || newName === tableName) return;
    try {
      await Common.post(`${APP_CONFIG.dbadminUrl}/table_ddl.php`, {
        action: 'rename', db: state.currentDb, table: tableName, new_name: newName,
      });
      if (state.currentTable === tableName) {
        state.currentTable = newName;
        updateHeader();
      }
      await loadTables();
    } catch (err) { showMessage(err.message); }
  }

  async function dropTable(tableName) {
    if (!confirm(`[${tableName}] 테이블을 삭제합니까?\n이 작업은 되돌릴 수 없습니다.`)) return;
    try {
      await Common.post(`${APP_CONFIG.dbadminUrl}/table_ddl.php`, {
        action: 'drop', db: state.currentDb, table: tableName,
      });
      if (state.currentTable === tableName) {
        state.currentTable = '';
        els.dataArea.innerHTML = '';
        els.structureArea.innerHTML = '';
        updateHeader();
        enableTableActions(false);
      }
      await loadTables();
      showMessage(`[${tableName}] 테이블이 삭제되었습니다.`);
    } catch (err) { showMessage(err.message); }
  }

  // ─── Column DDL ──────────────────────────────────────────────
  const COLUMN_TYPES = ['INT','BIGINT','TINYINT','SMALLINT','VARCHAR(255)','CHAR(32)',
    'TEXT','MEDIUMTEXT','LONGTEXT','DATE','DATETIME','TIMESTAMP','FLOAT','DOUBLE',
    'DECIMAL(10,2)','BOOLEAN','JSON','BLOB'];

  function buildColumnModal({
    title,
    colName = '',
    colType = 'VARCHAR(255)',
    isNull = true,
    defaultVal = '',
    comment = '',
    onUpdateCurrentTimestamp = false,
  }) {
    return `
      <div class="modal__header">
        <h2 class="modal__title">${Common.escapeHtml(title)}</h2>
        <button type="button" class="icon-btn" data-modal-close aria-label="닫기">×</button>
      </div>
      <form id="columnForm">
        <div class="modal__body">
          <div class="form-grid">
            <div class="field form-grid__full">
              <label class="field__label">컬럼명</label>
              <input type="text" class="input" name="column" value="${Common.escapeHtml(colName)}" required>
            </div>
            <div class="field form-grid__full">
              <label class="field__label">타입</label>
              <select class="select" name="type">
                ${COLUMN_TYPES.map((t) => `<option value="${t}" ${colType.toUpperCase().startsWith(t.split('(')[0].toUpperCase()) ? 'selected' : ''}>${t}</option>`).join('')}
              </select>
              <input type="text" class="input" name="type_override"
                placeholder="직접 입력 (예: VARCHAR(100), DECIMAL(8,2)) - 비우면 위 선택값 사용"
                value="${Common.escapeHtml(COLUMN_TYPES.some((t) => t.toUpperCase() === colType.toUpperCase()) ? '' : colType)}">
            </div>
            <div class="field">
              <label class="field__label">NULL 허용</label>
              <select class="select" name="null_allow">
                <option value="1" ${isNull ? 'selected' : ''}>YES</option>
                <option value="0" ${!isNull ? 'selected' : ''}>NO</option>
              </select>
            </div>
            <div class="field">
              <label class="field__label">DEFAULT</label>
              <input type="text" class="input" name="default" value="${Common.escapeHtml(defaultVal)}" placeholder="비워두면 NULL, ON UPDATE 사용 시 CURRENT_TIMESTAMP 권장">
            </div>
            <div class="field form-grid__full">
              <label class="check-row">
                <input type="checkbox" name="on_update_current_timestamp" value="1" ${onUpdateCurrentTimestamp ? 'checked' : ''}>
                <span class="field__label">ON UPDATE CURRENT_TIMESTAMP</span>
              </label>
              <div class="help-text">TIMESTAMP / DATETIME 컬럼의 수정 시각 자동 갱신에 사용합니다.</div>
            </div>
            <div class="field form-grid__full">
              <label class="field__label">COMMENT</label>
              <input type="text" class="input" name="comment" value="${Common.escapeHtml(comment)}">
            </div>
          </div>
        </div>
        <div class="modal__footer">
          <button type="button" class="button button--ghost" data-modal-close>닫기</button>
          <button type="submit" class="button button--primary">저장</button>
        </div>
      </form>`;
  }

  function bindColumnForm(action, db, table, originalColumn = null) {
    const form = document.getElementById('columnForm');
    if (!form) return;
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = Common.serializeForm(form);
      const finalType = fd.type_override?.trim() || fd.type;
      const payload = {
        action, db, table,
        column: action === 'add' ? fd.column : originalColumn,
        type: finalType,
        null_allow: fd.null_allow === '1',
        default: fd.default || null,
        on_update_current_timestamp: fd.on_update_current_timestamp === '1',
        comment: fd.comment || '',
      };
      if (action === 'modify') payload.new_name = fd.column;
      try {
        await Common.post(`${APP_CONFIG.dbadminUrl}/column_ddl.php`, payload);
        commonModal.close();
        await loadStructure();
        showMessage(action === 'add' ? '컬럼 추가 완료' : '컬럼 수정 완료');
      } catch (err) { showMessage(err.message); }
    });
  }

  async function openAddColumnModal(db, table) {
    els.commonModalDialog.className = 'modal__dialog modal__dialog--lg';
    els.commonModalContent.innerHTML = buildColumnModal({ title: '컬럼 추가' });
    commonModal.open();
    bindColumnForm('add', db, table);
  }

  async function openModifyColumnModal(db, table, column, colType, isNull, defaultVal, extra, comment) {
    els.commonModalDialog.className = 'modal__dialog modal__dialog--lg';
    els.commonModalContent.innerHTML = buildColumnModal({
      title: `컬럼 수정 — ${column}`,
      colName: column, colType, isNull: isNull === 'YES',
      defaultVal,
      comment,
      onUpdateCurrentTimestamp: /on update current_timestamp/i.test(extra || ''),
    });
    commonModal.open();
    bindColumnForm('modify', db, table, column);
  }

  async function dropColumn(db, table, column) {
    if (!confirm(`[${table}] 테이블의 [${column}] 컬럼을 삭제합니까?\n이 작업은 되돌릴 수 없습니다.`)) return;
    try {
      await Common.post(`${APP_CONFIG.dbadminUrl}/column_ddl.php`, {
        action: 'drop', db, table, column,
      });
      await loadStructure();
      showMessage(`[${column}] 컬럼이 삭제되었습니다.`);
    } catch (err) { showMessage(err.message); }
  }

  function bindStructureEvents() {
    // ── 컬럼 ──
    const btnAdd = document.getElementById('btnAddColumn');
    if (btnAdd) btnAdd.addEventListener('click', () => openAddColumnModal(btnAdd.dataset.db, btnAdd.dataset.table));

    document.querySelectorAll('.btn-modify-column').forEach((btn) => {
      btn.addEventListener('click', () => openModifyColumnModal(
        btn.dataset.db, btn.dataset.table, btn.dataset.column,
        btn.dataset.type, btn.dataset.null, btn.dataset.default, btn.dataset.extra, btn.dataset.comment,
      ));
    });
    document.querySelectorAll('.btn-drop-column').forEach((btn) => {
      btn.addEventListener('click', () => dropColumn(btn.dataset.db, btn.dataset.table, btn.dataset.column));
    });

    // ── 인덱스 ──
    const btnAddIdx = document.getElementById('btnAddIndex');
    if (btnAddIdx) {
      btnAddIdx.addEventListener('click', () => {
        const cols = JSON.parse(btnAddIdx.dataset.columns || '[]');
        openAddIndexModal(btnAddIdx.dataset.db, btnAddIdx.dataset.table, cols);
      });
    }
    document.querySelectorAll('.btn-drop-index').forEach((btn) => {
      btn.addEventListener('click', () => dropIndex(btn.dataset.db, btn.dataset.table, btn.dataset.index));
    });

    // ── 외래키 테이블 링크 ──
    document.querySelectorAll('.fk-table-link').forEach((a) => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        const targetTable = a.dataset.table;
        const btn = els.tableList.querySelector(`.btn-select-table[data-table="${CSS.escape(targetTable)}"]`);
        if (btn) btn.click();
        else showMessage(`[${targetTable}] 테이블이 현재 DB에 없습니다.`);
      });
    });

    // ── 외래키 추가/삭제 ──
    const btnAddFk = document.getElementById('btnAddFk');
    if (btnAddFk) {
      btnAddFk.addEventListener('click', () => {
        const cols = JSON.parse(btnAddFk.dataset.columns || '[]');
        openAddFkModal(btnAddFk.dataset.db, btnAddFk.dataset.table, cols);
      });
    }
    document.querySelectorAll('.btn-drop-fk').forEach((btn) => {
      btn.addEventListener('click', () => dropFk(btn.dataset.db, btn.dataset.table, btn.dataset.constraint));
    });
  }

  // ── FK DDL ───────────────────────────────────────────────────
  async function openAddFkModal(db, table, localCols) {
    els.commonModalDialog.className = 'modal__dialog';
    els.commonModalContent.innerHTML = `
      <div class="modal__header">
        <h2 class="modal__title">외래키 추가</h2>
        <button type="button" class="icon-btn" data-modal-close aria-label="닫기">×</button>
      </div>
      <form id="fkForm">
        <div class="modal__body">
          <div class="form-grid">
            <div class="field form-grid__full">
              <label class="field__label">제약명 (Constraint Name)</label>
              <input type="text" class="input" name="constraint_name"
                placeholder="fk_${Common.escapeHtml(table)}_col" required>
            </div>
            <div class="field">
              <label class="field__label">이 테이블 컬럼</label>
              <select class="select" name="column" required>
                ${localCols.map((c) => `<option value="${Common.escapeHtml(c)}">${Common.escapeHtml(c)}</option>`).join('')}
              </select>
            </div>
            <div class="field">
              <label class="field__label">참조 테이블</label>
              <select class="select" name="ref_table" id="fkRefTable" required>
                <option value="">로딩중...</option>
              </select>
            </div>
            <div class="field">
              <label class="field__label">참조 컬럼</label>
              <select class="select" name="ref_column" id="fkRefColumn" required>
                <option value="">테이블 선택 후 로드</option>
              </select>
            </div>
            <div class="field">
              <label class="field__label">ON DELETE</label>
              <select class="select" name="on_delete">
                <option>RESTRICT</option><option>CASCADE</option>
                <option>SET NULL</option><option>NO ACTION</option>
              </select>
            </div>
            <div class="field">
              <label class="field__label">ON UPDATE</label>
              <select class="select" name="on_update">
                <option>RESTRICT</option><option>CASCADE</option>
                <option>SET NULL</option><option>NO ACTION</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal__footer">
          <button type="button" class="button button--ghost" data-modal-close>닫기</button>
          <button type="submit" class="button button--primary">추가</button>
        </div>
      </form>`;
    commonModal.open();

    // 참조 테이블 목록 로드
    try {
      const data = await Common.get(`${APP_CONFIG.dbadminUrl}/dbs.php?mode=tables&db=${encodeURIComponent(db)}`);
      const refTableSel = document.getElementById('fkRefTable');
      refTableSel.innerHTML = data.tables
        .map((t) => `<option value="${Common.escapeHtml(t.name)}">${Common.escapeHtml(t.name)}</option>`)
        .join('');
      await loadRefColumns(db, refTableSel.value);

      refTableSel.addEventListener('change', () => loadRefColumns(db, refTableSel.value));
    } catch (err) { showMessage(err.message); }

    document.getElementById('fkForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      try {
        await Common.post(`${APP_CONFIG.dbadminUrl}/fk_ddl.php`, {
          action: 'add', db, table,
          constraint_name: fd.get('constraint_name'),
          column: fd.get('column'),
          ref_table: fd.get('ref_table'),
          ref_column: fd.get('ref_column'),
          on_delete: fd.get('on_delete'),
          on_update: fd.get('on_update'),
        });
        commonModal.close();
        await loadStructure();
        showMessage('외래키 추가 완료');
      } catch (err) { showMessage(err.message); }
    });
  }

  async function loadRefColumns(db, refTable) {
    const sel = document.getElementById('fkRefColumn');
    if (!sel || !refTable) return;
    try {
      const data = await Common.get(
        `${APP_CONFIG.dbadminUrl}/dbs.php?mode=columns&db=${encodeURIComponent(db)}&table=${encodeURIComponent(refTable)}`
      );
      sel.innerHTML = data.columns
        .map((c) => `<option value="${Common.escapeHtml(c.field)}">${Common.escapeHtml(c.field)}</option>`)
        .join('');
    } catch (_) { sel.innerHTML = '<option value="">로드 실패</option>'; }
  }

  async function dropFk(db, table, constraintName) {
    if (!confirm(`[${constraintName}] 외래키를 삭제합니까?`)) return;
    try {
      await Common.post(`${APP_CONFIG.dbadminUrl}/fk_ddl.php`, {
        action: 'drop', db, table, constraint_name: constraintName,
      });
      await loadStructure();
      showMessage('외래키 삭제 완료');
    } catch (err) { showMessage(err.message); }
  }

  // ── 인덱스 DDL ────────────────────────────────────────────────
  function openAddIndexModal(db, table, allCols) {
    els.commonModalDialog.className = 'modal__dialog';
    els.commonModalContent.innerHTML = `
      <div class="modal__header">
        <h2 class="modal__title">인덱스 추가</h2>
        <button type="button" class="icon-btn" data-modal-close aria-label="닫기">×</button>
      </div>
      <form id="indexForm">
        <div class="modal__body stack">
          <div class="field">
            <label class="field__label">인덱스명</label>
            <input type="text" class="input" name="index_name" placeholder="idx_col1" required>
          </div>
          <div class="field">
            <label class="field__label">컬럼 선택 (복수 가능)</label>
            <div class="choice-box">
              ${allCols.map((c) => `
                <label class="check-row" for="idx_col_${Common.escapeHtml(c)}">
                  <input type="checkbox" name="columns" value="${Common.escapeHtml(c)}" id="idx_col_${Common.escapeHtml(c)}">
                  <span>${Common.escapeHtml(c)}</span>
                </label>`).join('')}
            </div>
          </div>
          <label class="check-row" for="idxUnique">
            <input type="checkbox" name="unique" id="idxUnique">
            <span>UNIQUE 인덱스</span>
          </label>
        </div>
        <div class="modal__footer">
          <button type="button" class="button button--ghost" data-modal-close>닫기</button>
          <button type="submit" class="button button--primary">생성</button>
        </div>
      </form>`;
    commonModal.open();

    document.getElementById('indexForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      const cols = fd.getAll('columns');
      if (!cols.length) { showMessage('컬럼을 하나 이상 선택하세요.'); return; }
      try {
        await Common.post(`${APP_CONFIG.dbadminUrl}/index_ddl.php`, {
          action: 'add', db, table,
          index_name: fd.get('index_name'),
          columns: cols,
          unique: fd.get('unique') === 'on',
        });
        commonModal.close();
        await loadStructure();
        showMessage('인덱스 생성 완료');
      } catch (err) { showMessage(err.message); }
    });
  }

  async function dropIndex(db, table, indexName) {
    if (!confirm(`[${indexName}] 인덱스를 삭제합니까?`)) return;
    try {
      await Common.post(`${APP_CONFIG.dbadminUrl}/index_ddl.php`, { action: 'drop', db, table, index_name: indexName });
      await loadStructure();
      showMessage('인덱스 삭제 완료');
    } catch (err) { showMessage(err.message); }
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

    document.getElementById('btnCreateTable').addEventListener('click', createTable);
    els.btnRefreshTables.addEventListener('click', loadTables);
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

    els.btnRunSql.addEventListener('click', runSql);
    els.btnClearSql.addEventListener('click', () => {
      els.sqlEditor.value = '';
      els.sqlResultWrap.classList.add('is-hidden');
      els.sqlResultThead.innerHTML = '';
      els.sqlResultTbody.innerHTML = '';
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
