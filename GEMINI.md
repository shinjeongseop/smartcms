# smartcms Project Instructions

이 파일은 smartcms 프로젝트의 핵심 개발 규칙과 아키텍처 가이드를 담고 있습니다. 모든 코드 생성 및 수정 시 이 규칙을 최우선으로 준수해야 합니다.

## 1. UI/UX 및 마크업 규칙

### 시맨틱 마크업 (Semantic Markup)
- 단순한 `div` 나열 대신 의미론적 태그를 적극 사용합니다.
- 핵심 레이아웃: `<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<aside>`, `<footer>`
- 텍스트/데이터: `<time>`, `<strong>`, `<small>`, `<h1>`~`<h6>`
- 테이블: `<table>` 내부에 `<thead>`, `<tbody>`, `<tfoot>`를 명시하고, `<th>`에 `scope="col|row"`를 사용합니다.

### 부트스트랩 5 표준 (Bootstrap 5 Standards)
- 부트스트랩 5의 **공식 컴포넌트 마크업 구조**를 그대로 따릅니다.
  - 예: 네비게이션은 반드시 `ul.navbar-nav > li.nav-item > a.nav-link` 구조를 사용합니다.
- 모든 스타일링과 레이아웃은 부트스트랩 **유틸리티 클래스**(`p-*`, `m-*`, `d-flex`, `gap-*`, `text-*`)를 우선적으로 사용합니다.
- **카드(`.card`) 및 주요 컨테이너에는 `border-0` 대신 기본 `border`를 사용하여 명확한 구분을 제공합니다.**
- **PHP UI 헬퍼 함수(`smartcms_button`, `smartcms_alert` 등)를 사용하지 않고, 직접 부트스트랩 HTML 마크업을 작성합니다.**
- 커스텀 CSS는 `common/css/common.css`에 정의된 변수를 수정하는 방식으로 최소화합니다.

### 접근성 (Accessibility)
- **스크린 리더 사용자는 배제합니다.** `aria-label`, `aria-hidden`, `visually-hidden` 등 보조 공학 기기를 위한 추가 마크업은 생략하거나 최소화합니다.
- 대신 시각적인 구조적 명료함(시맨틱)에 집중합니다.

## 2. 개발 원칙 (Engineering Standards)

### 로직 및 모듈화
- 비즈니스 로직과 뷰(View)의 분리를 지향합니다.
- 공통 기능은 `common/` 디렉토리의 라이브러리 함수를 사용하며, 중복 코드를 지양합니다.
- 데이터베이스 접근 시 반드시 `common/database.php`의 래퍼 함수를 사용합니다.

### 보안
- 모든 사용자 입력은 `smartcms_h()` (htmlspecialchars)로 이스케이프하여 출력합니다.
- POST 요청 처리 시 반드시 `smartcms_csrf_input()` 및 검증 로직을 포함합니다.

## 3. 파일 수정 규칙
- 기존 코드의 스타일(들여쓰기, 네이밍 컨벤션)을 엄격히 유지합니다.
- 한 번에 하나의 파일만 수정하며, 수정 후에는 반드시 전체적인 마크업 구조가 시맨틱한지 확인합니다.
