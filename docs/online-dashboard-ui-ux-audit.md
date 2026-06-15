# 온라인 대시보드 UI/UX 점검 결과

점검일: 2026-06-15  
대상: `https://oliva1.dothome.co.kr/online/`

## 확인 요약

- 온라인 대시보드는 로그인 후 `/online/`에서 정상 접근된다.
- 하위 메뉴는 매출등록, 매출분석, 출고입력, 출고조회, 출고조회(분할), 환불관리, 순위검색, 내 정보로 구성된다.
- 서버 화면은 `/common/css/common.css`, `/common/js/*.js`, `/online/dashboard.js`, `/smartgrid_module/smartgrid.css`를 사용한다.
- 현재 로컬 저장소 `D:\smartcms`에는 `/online/`, `/smartgrid_module/`, `/common/js/chartLib.js`, `/common/js/lookup-ui.js`, `/common/js/ledger-page.js`, `/online/dashboard.js` 원본이 없다.
- 서버의 `/common/css/common.css`는 "발주관리 시스템 app.css" 계열이고, 로컬 `common/css/common.css`는 SmartCMS 2.0 Bootstrap 테마라 서로 다른 파일이다.

## 현재 차단 사항

온라인 대시보드 원본이 Git 저장소에 없으므로, Git 기반 자동 배포 원칙에 맞춰 안전하게 수정할 수 없다.

특히 로컬 `common/css/common.css`를 수정해 푸시하면 서버 온라인 대시보드에서 사용하는 기존 `common.css`를 덮어쓸 수 있어, `/online/` 전체 스타일이 깨질 위험이 크다.

## 실제 화면에서 확인한 공통 문제

### 전체 레이아웃

- `body`에 온라인 화면 전용 클래스가 없어 화면 범위가 CSS에서 명확히 격리되지 않는다.
- 대시보드와 하위 페이지의 카드, 필터, 테이블 패딩이 화면마다 다르게 보인다.
- 페이지 제목, 필터 카드, 결과 카드 사이의 세로 간격이 일관되지 않다.
- 아이콘이 의미 없이 텍스트 앞에 반복되고, 일부 모바일 화면에서는 메뉴 아이콘 텍스트가 접힌 영역에 남아 보인다.

### 인라인 스타일

확인한 페이지별 인라인 스타일 수:

- `/online/shipout_qty/batch/`: 48개
- `/online/shipout_qty/detail/`: 354개
- `/online/shipout/`: 15개
- `/online/shipout_history_expand/`: 67개
- `/online/shipout_history_split/`: 67개
- `/online/refund/`: 60개
- `/online/naver/`: 2개
- `/online/mypage/`: 2개

프로젝트 지침상 인라인 스타일은 공통 CSS 또는 페이지 전용 CSS로 분리해야 한다.

### 필터 영역

- 날짜, 검색어, 셀렉트, 퀵필터 버튼 배치가 페이지마다 다르다.
- 모바일에서 필터 버튼 줄바꿈은 동작하지만, 입력 필드와 버튼의 정렬 기준이 일정하지 않다.
- "오늘", "어제", "이번주" 같은 퀵필터 버튼은 공통 컴포넌트로 묶는 것이 적합하다.

### 테이블과 SmartGrid

- 대부분의 업무 화면이 넓은 테이블에 의존하므로 가로 스크롤 자체는 필요하다.
- 다만 헤더 고정, 숫자 우측 정렬, 합계 행 강조, 빈 상태 메시지 스타일이 화면별로 다르다.
- SmartGrid 설정과 테이블 래퍼를 화면별로 중복하지 말고 공통화해야 한다.

### 대시보드 차트

- 대시보드 KPI 카드와 차트 카드가 기능적으로는 보이나, 카드 제목과 그래프 높이의 밀도가 화면마다 균일하지 않다.
- 모바일에서 차트보다 KPI 카드가 길게 이어져 핵심 지표 스캔은 가능하지만, 차트 영역으로 넘어가는 흐름이 다소 무겁다.

## 원본 확보 후 권장 작업 순서

1. 서버의 `/online/`, `/common/js/`, `/smartgrid_module/`, 온라인용 `/common/css/common.css` 원본을 Git 저장소에 추가한다.
2. 온라인 전용 레이아웃 클래스를 `body` 또는 최상위 래퍼에 부여한다. 예: `smartcms-online-page`.
3. 온라인 공통 CSS를 `common/css/online.css` 또는 기존 온라인 `common.css`의 명확한 섹션으로 분리한다.
4. 필터 카드, 페이지 헤더, 테이블 툴바, 빈 상태, 페이지네이션을 공통 PHP include 또는 공통 렌더 함수로 묶는다.
5. 인라인 스타일을 제거하고, 반복되는 너비/높이/차트 래퍼 규칙을 CSS 클래스로 이동한다.
6. SmartGrid 설정을 화면별 중복 정의 대신 공통 설정 파일에서 가져오게 한다.
7. 모바일 기준으로 메뉴 접힘, 필터 줄바꿈, 테이블 가로 스크롤, 카드 간격을 다시 검증한다.

## 우선 적용하면 좋은 공통 클래스 후보

- `.online-page`
- `.online-page-header`
- `.online-filter-card`
- `.online-filter-grid`
- `.online-toolbar`
- `.online-table-card`
- `.online-empty-state`
- `.online-chart-card`
- `.online-kpi-card`

## 검증 체크리스트

- `/online/`
- `/online/shipout_qty/batch/`
- `/online/shipout_qty/detail/`
- `/online/shipout/`
- `/online/shipout_history_expand/`
- `/online/shipout_history_split/`
- `/online/refund/`
- `/online/naver/`
- `/online/mypage/`

각 화면은 데스크톱 1280px, 모바일 390px 기준으로 확인한다.

