<?php
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/head.php';
?>

<main class="app-shell">
  <section class="app-layout">
    <aside class="sidebar">
      <section class="panel panel--full-height">
        <header class="panel__header cluster cluster--between">
          <strong>DB / TABLE</strong>
          <a href="<?= DBADMIN_URL ?>/logout.php" class="button button--sm button--ghost">로그아웃</a>
        </header>
        <div class="panel__body stack">
          <div class="field">
            <label class="field__label" for="dbSelect">DB 선택</label>
            <select id="dbSelect" class="select"></select>
          </div>
          <div class="section-head">
            <label class="field__label">테이블 목록</label>
            <div class="cluster">
              <button class="button button--sm button--success" id="btnCreateTable">+ 생성</button>
              <button class="button button--sm button--ghost" id="btnRefreshTables">새로고침</button>
            </div>
          </div>
          <div id="tableList" class="table-list"></div>
        </div>
      </section>
    </aside>

    <section class="workspace">
      <section class="panel">
        <header class="panel__header cluster cluster--between">
          <div class="current-labels">
            <strong id="currentDbLabel">DB: -</strong>
            <span aria-hidden="true">|</span>
            <strong id="currentTableLabel">TABLE: -</strong>
          </div>
          <div class="cluster">
            <button class="button button--primary button--sm" id="btnAddRow" disabled>행 추가</button>
            <a class="button button--success button--sm is-hidden" id="btnExportCsv" href="#">CSV 내보내기</a>
            <button class="button button--ghost button--sm is-hidden" id="btnImportCsv">CSV 가져오기</button>
          </div>
        </header>

        <div class="panel__body">
          <nav class="tabs" aria-label="DB 관리 탭">
            <button class="tabs__button tabs__button--active" data-tab-target="tab-data" type="button">데이터</button>
            <button class="tabs__button" data-tab-target="tab-structure" type="button">구조</button>
            <button class="tabs__button" data-tab-target="tab-sql" type="button">SQL 실행기</button>
          </nav>

          <section class="tab-panel tab-panel--active" id="tab-data">
            <div class="toolbar">
              <select id="searchColSelect" class="select select--compact">
                <option value="__all__">전체 컬럼</option>
              </select>
              <input type="text" id="searchKeyword" class="input search-input" placeholder="검색어">
              <button class="button button--ghost" id="btnSearchFilter">검색</button>
              <button class="button button--ghost" id="btnClearFilter">초기화</button>
              <div class="toolbar__end">
                <label class="field__label" for="pageSize">페이지당</label>
                <select id="pageSize" class="select page-size-select">
                  <option value="20" selected>20</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                  <option value="200">200</option>
                </select>
              </div>
            </div>
            <div id="dataArea"></div>
          </section>

          <section class="tab-panel" id="tab-structure">
            <div id="structureArea"></div>
          </section>

          <section class="tab-panel" id="tab-sql">
            <div class="field">
              <label class="field__label" for="sqlEditor">SQL</label>
              <textarea id="sqlEditor" class="textarea sql-editor" placeholder="SELECT * FROM table_name LIMIT 100;"></textarea>
            </div>
            <div class="cluster section-gap">
              <button class="button button--dark" id="btnRunSql">실행</button>
              <button class="button button--ghost" id="btnClearSql">지우기</button>
            </div>
            <div id="sqlResultWrap" class="is-hidden">
              <div id="sqlMessage" class="alert"></div>
              <div class="data-table-wrap">
                <table class="data-table" id="sqlResultTable">
                  <thead></thead><tbody></tbody>
                </table>
              </div>
            </div>
          </section>
        </div>
      </section>
    </section>
  </section>
</main>

<?php require_once __DIR__ . '/foot.php'; ?>
