# smartcms Admin Design System

> Version: 1.0
> Scope: `admin/` 하위 전체 화면

이 문서는 `admin/` 전용 디자인 기준이다. 루트 `DESIGN.md`의 공통 토큰과 규칙을 그대로 따르되, 관리자 콘솔에 맞게 조금 더 단단하고 밀도 높은 UI 규칙을 정의한다.
관리자 화면은 Bootstrap 기본 컴포넌트와 유틸리티만으로 구성한다. 새 관리자 전용 시각 컴포넌트 클래스는 추가하지 않는다.

## Overview

관리자 화면은 **정보 밀도 중심의 콘솔 UI**다.

- 배경은 연한 회색 캔버스
- 표면은 흰색 카드
- 액션은 브랜드 그린 하나만 사용
- 장식보다 가독성과 작업 속도 우선

관리자 공통 스타일은 Bootstrap 기본 스타일과 `common/css/common.css`의 최소 오버라이드가 담당한다.

## Entry Rules

- 관리자 페이지는 `head.php`에서 자동으로 `smartcms-admin-page` 또는 `smartcms-admin-auth` 클래스를 받는다.
- 관리자 로그인은 `smartcms-admin-auth`를 사용한다.
- `admin/css/admin.css`는 관리자 경로에서 자동으로 로드된다.
- 관리자 페이지는 루트 공통 규칙을 우선하고, 관리자 전용 예외만 이 문서에서 정의한다.

## Visual Principles

1. **Dense but calm** - 화면당 정보량은 높게 유지하되 시각적 소음은 줄인다.
2. **Single accent** - 액션 색상은 `--sc-primary` 하나만 사용한다.
3. **No shadow** - 관리자 카드와 패널은 그림자 없이 border로만 구분한다.
4. **Readable tables** - 표는 줄바꿈 없이 한 줄 유지가 기본이다.
5. **Fast scanning** - 상태, 권한, 시간, ID처럼 반복되는 값은 정렬과 간격을 맞춘다.

## Color Usage

관리자는 루트 토큰을 그대로 사용한다.

- `--sc-primary`: 저장, 확인, 활성 상태
- `--sc-text`: 제목, 강조 텍스트
- `--sc-body`: 본문
- `--sc-muted`: 메타 정보
- `--sc-bg`: 관리자 캔버스
- `--sc-surface`: 카드와 패널
- `--sc-line`: 기본 테두리
- `--sc-line-strong`: 입력, 활성 경계
- `--sc-danger`: 삭제, 초기화, 위험 작업

## Layout

- 사이드바는 데스크톱에서 고정 폭 `240px`
- 워크스페이스는 나머지 가변 폭
- 모바일에서는 사이드바를 접고 상단 메뉴로 전환
- 페이지 본문 패딩은 `24px` 전후로 유지
- 섹션 간 간격은 `16px ~ 24px`

## Components

### Page Head

- 관리자 페이지는 상단 제목과 사용자 정보를 명확히 보여준다.
- 페이지 타이틀은 `h1` 기준으로 표시한다.
- 보조 설명이 있으면 제목 아래에 짧게 둔다.
- 구조는 `container-fluid`, `row`, `col`, `d-flex`, `gap-*`, `card` 조합으로 만든다.

### Cards

- `card`, `card-body`, `border`, `shadow-none`, `rounded-*`를 사용한다.
- 카드 배경과 테두리는 Bootstrap 기본 스타일을 우선한다.
- 관리자 화면에서 카드 전용 커스텀 클래스는 새로 만들지 않는다.

### Tables

- 모든 관리자 테이블은 `table`, `table-hover`, `align-middle` 조합을 우선한다.
- 숫자, 상태, 시간, 권한은 한 줄로 빠르게 읽히게 한다.
- 긴 텍스트는 열 폭을 조정하거나 생략 전략을 먼저 검토한다.
- 표는 `table-responsive` 안에서만 가로 스크롤을 허용한다.

### Forms

- 입력 요소는 카드 안에서 촘촘하게 배치한다.
- `form-control-sm` / `form-select-sm`는 관리용 편집 폼에서 우선 사용한다.
- 레이블은 짧고 명확하게 쓴다.
- 저장 버튼은 한 섹션에 하나를 기준으로 한다.
- 레이아웃은 `row`, `col-*`, `g-*`만으로 나눈다.

### Buttons

- 기본 동작은 `btn-primary`
- 보조 동작은 `btn-outline-secondary`
- 위험 동작은 `btn-danger`
- 관리자 화면에서도 보라색, 파란색 등 다른 액션 색상은 쓰지 않는다
- 버튼은 `btn-sm`, 기본, `btn-lg`와 `rounded-pill` 정도만 사용한다.

### Auth Screen

- 관리자 로그인은 중앙 정렬된 단일 카드 레이아웃을 사용한다.
- 카드 폭은 `480px` 안팎으로 제한한다.
- 입력 필드는 넉넉한 높이로 제공한다.
- 로그인 화면도 관리자 콘솔 톤과 동일한 배경을 사용한다.
- 구조는 `container`, `row`, `col`, `card`, `form`, `btn`만으로 해결한다.

## Admin-Specific Classes

- 새 관리자 전용 시각 컴포넌트 클래스는 추가하지 않는다.
- 기존 레이아웃 보조 클래스는 점진적으로 Bootstrap 기본 조합으로 치환한다.

## Do

- 공통 토큰은 `common/css/common.css`의 `--sc-*` 변수를 그대로 사용한다.
- 관리자 전용 규칙은 Bootstrap 기본 클래스가 부족한 전역 보정에만 사용한다.
- 테이블과 리스트는 정보 우선 순서로 정렬한다.
- 위험 작업은 시각적으로 분리한다.
- 새로운 관리자 페이지는 `admin/DESIGN.md` 기준으로 먼저 설계한다.
- 시멘틱 태그를 필수로 사용한다. 관리자 화면 구조는 `header`, `main`, `section`, `aside`, `nav`, `footer`를 우선하고, 의미 없는 `div` 남용을 피한다.
- 새 관리자 화면은 Bootstrap 기본 컴포넌트와 유틸리티만으로 작성한다.

## Don't

- 인라인 스타일을 추가하지 않는다.
- 관리자 화면에 새로운 브랜드 컬러를 도입하지 않는다.
- 카드와 패널에 그림자를 다시 넣지 않는다.
- 동일한 관리자 스타일을 각 페이지에 중복 작성하지 않는다.
- 테이블 줄바꿈을 예외적으로 허용할 때는 이유를 문서화한다.
