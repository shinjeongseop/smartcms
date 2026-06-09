# smartcms 2.0 Design System

> Version: 2.0
> Philosophy: **Bootstrap-Native**. 부트스트랩 5의 기본 유틸리티와 컴포넌트를 100% 활용하여 커스텀 CSS를 최소화합니다.

## Overview

smartcms 2.0은 부트스트랩 5의 강력한 유틸리티 엔진 위에 구축되었습니다.
디자인은 **네이버 포털의 명료함**과 **모던한 카드 레이아웃**을 결합하여 가독성과 사용성을 최우선으로 합니다.

### Core Principles
1. **Utility-First**: 모든 레이아웃과 간격은 `p-*`, `m-*`, `d-flex`, `gap-*` 등 부트스트랩 유틸리티를 우선 사용합니다.
2. **Component-Driven**: 부트스트랩의 `card`, `list-group`, `modal`, `navbar` 등을 기본 상태로 사용하고 변수로만 톤을 조정합니다.
3. **Typography-Centric**: Pretendard 서체를 사용하여 한글 가독성을 극대화합니다.
4. **Clean & High Contrast**: 명확한 테두리(`border`)와 은은한 그림자(`shadow-sm`)를 조합하여 계층을 분리합니다.

## Color Tokens (via Bootstrap Overrides)

모든 색상은 `common/css/common.css`의 CSS 변수를 통해 부트스트랩 기본값을 덮어씁니다.

| Variable | Value | Role |
|---|---|---|
| `--bs-primary` | `#03c75a` | 브랜드 메인 컬러 (네이버 그린), 핵심 CTA |
| `--bs-body-bg` | `#f6f7f9` | 페이지 전체 배경색 (연한 회색) |
| `--bs-body-color` | `#444444` | 본문 텍스트 컬러 |
| `--bs-emphasis-color`| `#111111` | 제목 및 강조 텍스트 컬러 |
| `--bs-secondary-color`| `#767676` | 보조 텍스트, 메타 정보 |
| `--bs-border-color` | `#e6e5e0` | 기본 테두리 색상 |

## Typography

- **Body Font**: `Pretendard`, `Noto Sans KR`, sans-serif
- **Code Font**: `JetBrains Mono`, `Fira Code`, monospace

| Level | Size | Weight | Utility |
|---|---|---|---|
| Display | 2.5rem~ | 700 | `.display-*` |
| Heading L | 1.75rem | 700 | `h1`, `.h1` |
| Heading M | 1.5rem | 700 | `h2`, `.h2` |
| Heading S | 1.25rem | 600 | `h3`, `.h3` |
| Body | 1rem | 400 | `body` |
| Small | 0.875rem | 400 | `.small`, `.text-sm` |

## Layout Rules

- **Grid**: Bootstrap 12-column grid (`.row`, `.col-*`)
- **Spacing**: `spacer` 단위 (1rem = 16px) 기반 유틸리티 사용
- **Containers**: 기본 `.container-xxl` (최대 1320px) 사용
- **Gutter**: 기본 `g-4` (1.5rem) 사용

## Component Guidelines

### Cards (`.card`)
- `border-0` + `shadow-sm` 조합을 기본으로 합니다.
- 내부 패딩은 기본 `p-4` (모바일 `p-3`)를 권장합니다.

### Buttons (`.btn`)
- 주요 액션: `.btn-primary` (Green)
- 보조 액션: `.btn-secondary` (Gray) 또는 `.btn-light`
- 크기: `.btn-sm`, `.btn-lg` 유틸리티 활용

### Forms (`.form-control`)
- 레이블은 `.form-label`과 `.fw-semibold`를 조합합니다.
- 입력창은 `.form-control-lg`를 선호하여 시원한 느낌을 줍니다.

## Do & Don't

### ✅ Do
- 부트스트랩 유틸리티를 3개 이상 조합하여 레이아웃 구성 (`d-flex align-items-center justify-content-between gap-3`)
- 시맨틱 태그(`header`, `main`, `footer`, `aside`, `section`) 사용
- 반응형 클래스 적극 활용 (`d-none d-md-block`, `col-lg-8`)

### ❌ Don't
- 인라인 스타일 (`style="..."`) 사용
- 새로운 CSS 클래스 이름 생성 (예: `.my-custom-box`)
- 부트스트랩 JS 없이 무리하게 인터랙션 구현
- 한 페이지에 여러 개의 Primary 버튼 배치

---
*Last Updated: 2026-06-09*
