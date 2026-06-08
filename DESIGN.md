# smartcms Design System

> Version: 1.0
> Rule: 새 페이지, 스킨, 관리자 화면, 컴포넌트는 이 문서를 기준으로 디자인한다.

## Overview

smartcms는 **Bootstrap 5 위에 얇은 커스텀 레이어**를 올린 구조다.
Bootstrap의 유틸리티·그리드·컴포넌트를 최대한 활용하고,
브랜드 톤·타이포그래피·색상만 `common/css/common.css`의 CSS 변수로 덮어쓴다.

디자인 방향: **네이버 포털을 연상시키는 선명한 그린 액센트 + 흰 표면 + 정돈된 카드 레이아웃**.
그림자 대신 `1px hairline + 흰 카드 on 크림 배경`으로 계층을 만든다.

## Current Pages

프로젝트의 현재 화면 기준으로 문서를 유지한다.

- `/`: 홈
- `/board/`: 게시판 목록
- `/board/view/`: 게시글 보기
- `/board/write/`: 글쓰기
- `/board/edit/`: 글 수정
- `/board/download/`: 첨부 다운로드
- `/member/login/`: 회원 로그인
- `/member/register/`: 회원가입
- `/member/password/`: 비밀번호 재설정
- `/member/mypage/`: 마이페이지
- `/member/logout/`: 로그아웃
- `/admin/login/`: 관리자 로그인
- `/admin/dashboard/`: 관리자 대시보드
- `/admin/users/`: 회원 관리
- `/admin/boards/`: 게시판 관리
- `/admin/pages/`: 페이지 권한
- `/admin/logs/`: 접속 로그
- `/admin/database/`: DB 관리
- `/admin/settings/`: 환경 설정

공통 레이아웃은 `head.php` / `foot.php`를 기반으로 하고, 화면 성격에 따라 사이트 레이아웃과 관리자 레이아웃으로 나뉜다.

## Design Principles

1. **Clean canvas** — 페이지 바닥은 연한 그레이, 순백 `#ffffff`는 카드 표면에 쓴다.
2. **Readable ink** — 본문 텍스트는 다크 그레이, 순검정 사용을 피한다.
3. **Single CTA** — 액션 색상 `#03c75a` 하나만 사용. 두 번째 CTA 색상 도입 금지.
4. **No shadows** — 카드·패널에 drop shadow 금지. hairline border + 배경 대비로 깊이 표현.
5. **Bootstrap-first** — 레이아웃·간격은 Bootstrap 유틸리티 사용. 커스텀 CSS는 토큰 오버라이드에 집중.
6. **Editorial type** — 디스플레이 헤딩은 weight 600, letter-spacing 음수. 본문은 400.
7. **Generous rhythm** — 섹션 간 수직 여백 기본 48px, 메이저 섹션 64px.

## Color Tokens

| Token | Value | 용도 |
|---|---|---|
| `--sc-primary` | `#03c75a` | Primary CTA, 브랜드 워드마크, active 상태 |
| `--sc-primary-dark` | `#02a74b` | CTA hover/pressed |
| `--sc-text` | `#111111` | 제목·강조 텍스트 |
| `--sc-body` | `#444444` | 본문 |
| `--sc-muted` | `#767676` | 보조 메타·캡션 |
| `--sc-bg` | `#f6f7f9` | 페이지 캔버스 |
| `--sc-surface` | `#ffffff` | 카드·패널 표면 |
| `--sc-surface-soft` | `#fafaf7` | 테이블 hover, 보조 배경 |
| `--sc-line` | `#e6e5e0` | 기본 hairline |
| `--sc-line-strong` | `#cfcdc4` | 강조 hairline, 입력 테두리 |
| `--sc-success` | `#1f8a65` | 성공·확인 |
| `--sc-warning` | `#b07a2a` | 경고 |
| `--sc-danger` | `#cf2d56` | 오류·위험 |

### Bootstrap 오버라이드

Bootstrap의 `--bs-primary`, `--bs-body-bg` 등은 `common.css`에서 일괄 덮어쓴다.
페이지별 인라인 style 속성으로 색상 값을 직접 쓰지 않는다.

## Typography

기본 폰트 스택 (Bootstrap `font-family` 오버라이드):

```css
"Pretendard", "Noto Sans KR", system-ui, -apple-system, sans-serif
```

코드 폰트:

```css
"JetBrains Mono", "Fira Code", Consolas, monospace
```

| 역할 | 크기 | Weight | Letter-spacing | 용도 |
|---|---|---|---|---|
| Display | clamp(36px, 5vw, 64px) | 700 | -0.03em | 홈 히어로 H1 |
| Heading L | 28px | 700 | -0.02em | 페이지 제목 |
| Heading M | 22px | 700 | -0.02em | 섹션·위젯 제목 |
| Heading S | 18px | 600 | -0.01em | 카드 제목 |
| Body | 16px | 400 | 0 | 본문 |
| Body SM | 14px | 400 | 0 | 보조 텍스트·버튼 |
| Caption | 13px | 400 | 0 | 캡션·날짜 |
| Label | 11px | 600 | 0.06em | 배지·eyebrow (uppercase) |

## Layout

- **Container**: `min(1240px, 100%)`, 좌우 padding `24px` (모바일 `16px`)
- **Navbar height**: `56px`
- **Navbar bottom margin**: `32px`
- **섹션 수직 간격**: 기본 `48px`, 주요 섹션 `64px`
- **홈 레이아웃**: 좌 콘텐츠 `1fr` + 우 사이드바 `320px` (태블릿 이하 단열)
- **관리자 레이아웃**: 사이드바 `240px` + 워크스페이스 `1fr`
- **Breakpoints** (Bootstrap 기준 사용):
  - `≥ 992px` (lg): 2열 레이아웃 활성
  - `< 992px` (md 이하): 단열, 관리자 사이드바 상단 고정 해제
  - `< 768px` (sm 이하): 모바일, 패딩·폰트 축소
  - `< 480px` (xs): 통계 그리드 단열

## Border Radius

| Token | Value | 용도 |
|---|---|---|
| `--sc-radius-sm` | `6px` | 버튼, 입력, 배지, 소형 요소 |
| `--sc-radius` | `10px` | 카드, 패널, 위젯 기본 |
| `--sc-radius-lg` | `14px` | 대형 카드, 히어로 |
| `--sc-radius-pill` | `9999px` | Pill 버튼, 배지 |

## Components

### Navbar

- Bootstrap `navbar navbar-expand-lg` 기반
- 배경: `--sc-surface` (흰색), 하단 hairline `--sc-line`
- 높이 `56px`, 그림자 없음
- 브랜드 아이콘: `--sc-primary` 배경 + 흰 아이콘
- 브랜드 텍스트: `--sc-primary` 컬러, weight 700
- 메뉴 링크: `--sc-text` → hover/active `--sc-primary` (배경 없음)
- CTA 버튼: `btn btn-primary btn-sm`

### Buttons

Bootstrap `btn` 기반, CSS 변수로 색상만 오버라이드.

| 종류 | 클래스 | 용도 |
|---|---|---|
| Primary | `btn btn-primary` | 주요 액션 (저장, 확인, 글쓰기) |
| Secondary | `btn btn-outline-secondary` | 보조 액션 (취소, 목록으로) |
| Danger | `btn btn-outline-danger` | 파괴적 액션 (삭제, 숨김) |

- 기본 radius: `--sc-radius-sm` (6px)
- Pill 스타일: `rounded-pill` 추가 (CTA, 헤더 버튼)
- 높이 기준: `36px` (sm), `40px` (md, 기본), `44px` (lg)

### Cards & Panels

Bootstrap `card` 기반 + `.sc-panel` 오버라이드.

- 배경: `--sc-surface` (흰색)
- 테두리: `1px solid --sc-line`
- radius: `--sc-radius` (10px)
- padding: `24px`
- 그림자: **없음**
- hover: border-color → `--sc-line-strong` (선택 사항)

### Forms

Bootstrap `form-control` / `form-select` 기반.

- 배경: `--sc-surface`
- 테두리: `1px solid --sc-line-strong`
- radius: `--sc-radius-sm` (6px)
- 높이: `40px` (기본)
- focus: border-color `--sc-primary`, box-shadow 없음
- label: `14px / weight 600 / --sc-text`

### Tables

Bootstrap `table table-hover` 기반.

- 헤더: `12px / uppercase / weight 600 / --sc-muted`
- 행 경계: hairline `--sc-line`
- hover: `--sc-surface-soft`
- 그림자·zebra 금지

### Alerts

`.sc-alert` 클래스 사용 (Bootstrap `alert` 미사용).

| 타입 | 배경 | 텍스트 | 테두리 |
|---|---|---|---|
| info | `--sc-surface` | `--sc-body` | `--sc-line` |
| success | `#f0fdf4` | `--sc-success` | `#bbf7d0` |
| error | `#fff1f2` | `--sc-danger` | `#fecdd3` |
| warning | `#fffbeb` | `--sc-warning` | `#fde68a` |

### Auth Pages (로그인·회원가입·관리자 로그인)

- 전체 화면 flex 센터 정렬, 배경 `--sc-bg`
- 박스: `min(460px, 100%)`, padding `40px 36px`
- 제목: Heading L (28px / 700)
- 하단 링크: `13px`

### Admin Layout

- 사이드바 `240px` sticky, 배경 `--sc-surface`, 우측 hairline
- 워크스페이스: padding `28px`
- 상단바: 배경 `--sc-surface`, hairline, padding `12px 20px`
- 사이드바 active 링크: `--sc-primary` 배경, 흰 텍스트, radius `--sc-radius-sm`
- 통계 카드: Bootstrap `card` + `.sc-stat-card` (hover 시 border-color 강화)

### Board List

- 검색폼 + 테이블 + 페이지네이션 구조
- Bootstrap `table table-hover` 사용
- 페이지네이션: `.sc-pagination` + `.sc-page-link`

### Board Detail & Editor

- 게시글 보기, 글쓰기, 글 수정은 하나의 보드 컨텍스트를 공유한다.
- 상단에는 게시판명과 핵심 액션을 배치한다.
- 본문은 카드형 섹션으로 나누고, 첨부파일·댓글·관련 게시글을 분리한다.
- 편집 화면은 폼 중심, 보기 화면은 콘텐츠 중심으로 구성한다.

### Member Pages

- 로그인, 회원가입, 비밀번호 재설정, 마이페이지는 인증 중심 화면이다.
- 인증 화면은 단일 카드, 짧은 폼, 보조 링크 중심으로 유지한다.
- 회원 페이지는 사이트 공통 헤더와 푸터를 사용한다.

### Home Layout

- 히어로 → 공지 배너 → 요약 스트립 → 2열 메인 레이아웃 순서
- 히어로: 밝은 배경, 디스플레이 타이포, 그라디언트·다크배경 금지
- 요약 스트립: 3열 카드, 실질적 지표 (총 게시글, 총 회원, 오늘 방문)
- 위젯: `.sc-widget` = `.sc-panel` + hover border 강화

### Current Composition Rules

- 메인 콘텐츠는 의미에 맞는 `main`과 `section`을 우선한다.
- 보조 네비게이션은 `nav`, 관련 보조 정보는 `aside`로 분리한다.
- 페이지 상단은 `header`, 하단은 `footer`를 사용한다.
- 관리자 화면은 전용 문서 `admin/DESIGN.md`를 우선 참조한다.

## Do

- Bootstrap 유틸리티 클래스 적극 활용 (`gap-*`, `d-flex`, `mb-*` 등)
- CSS 변수 (`--sc-*`) 사용, 인라인 hex 금지
- `sc-*` 컴포넌트 클래스는 Bootstrap 클래스와 병기 (예: `class="card sc-panel"`)
- 디스플레이 heading weight 600~700, 본문 400
- 새 페이지는 반드시 `head.php` / `foot.php`를 공통 포함해서 사용
- 상단 공통 레이아웃은 `head.php`, 하단 공통 레이아웃은 `foot.php`에서 관리한다
- 시멘틱 태그를 필수로 사용한다. 문서 구조는 `header`, `main`, `section`, `aside`, `nav`, `footer`를 우선하고, 의미 없는 `div` 남용을 피한다

## Don't

- 인라인 `style=""` 속성으로 색상·폰트 직접 지정 금지
- `--sc-primary` 외 두 번째 브랜드 컬러 도입 금지
- 카드·패널에 `box-shadow` 추가 금지
- Bootstrap CSS 파일 직접 수정 금지 (CDN 사용, 오버라이드만)
- 파란색·보라색 계열을 UI 액션 색상으로 사용 금지
- `font-weight: 900` 남용 금지 (최대 700)
- 섹션 타이틀에 `font-weight: 400` 사용 금지 (너무 약함)

## File Structure

```
common/
  css/
    common.css      ← 유일한 커스텀 CSS, 토큰 + 컴포넌트 오버라이드
  ui/
    components.php  ← 알림, 버튼, 컨테이너 등 공용 함수
head.php            ← <head>, 사이트/관리자 상단 레이아웃
foot.php            ← 사이트/관리자 푸터, Bootstrap JS, </body>
DESIGN.md           ← 이 문서 (디자인 소스 오브 트루스)
```
