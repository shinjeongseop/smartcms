# smartcms 2.0 Design System

> Version: 2.2 (Bootstrap Standard & Semantic)
> Philosophy: **Standard Bootstrap-Native**. 부트스트랩 5의 표준 컴포넌트 구조와 HTML5 시맨틱 태그를 100% 활용합니다. 임의의 배경색이나 테두리 제거를 지양하고 브라우저 기본 폼 스타일을 존중합니다.

## Overview

smartcms 2.0은 부트스트랩 5의 강력한 유틸리티 엔진 위에 구축되었습니다.
디자인은 **네이버 포털의 명료함**과 **모던한 카드 레이아웃**을 결합하며, 부트스트랩 표준 스타일(Standard Look & Feel)을 최우선으로 합니다.

### Core Principles

1. **Semantic First**: 단순한 `div` 나열 대신 `header`, `nav`, `main`, `section`, `article`, `aside`, `footer` 등을 사용하여 문서 구조를 선언적으로 표현합니다.
2. **Bootstrap Standard Style**: 폼 요소(Input, Select)는 부트스트랩 표준인 **흰색 배경과 1px 테두리**를 유지합니다.
3. **Utility-First Layout**: 모든 레이아웃과 간격은 `p-*`, `m-*`, `d-flex`, `gap-*` 등 부트스트랩 유틸리티를 사용합니다.
4. **Clean & High Contrast**: 페이지 배경은 `bg-light`로, 카드는 명확한 테두리(`border`)와 은은한 그림자(`shadow-sm`)를 가진 **흰색 배경**으로 구성합니다.

## Color Tokens (via Bootstrap Overrides)

모든 색상은 `common/css/common.css`의 CSS 변수를 통해 부트스트랩 기본값을 덮어씁니다.

| Variable               | Value     | Role                                     |
| ---------------------- | --------- | ---------------------------------------- |
| `--bs-primary`         | `#03c75a` | 브랜드 메인 컬러 (네이버 그린), 핵심 CTA |
| `--bs-body-bg`         | `#f8f9fa` | 페이지 전체 배경색 (Standard bg-light)   |
| `--bs-body-color`      | `#444444` | 본문 텍스트 컬러                         |
| `--bs-emphasis-color`  | `#111111` | 제목 및 강조 텍스트 컬러                 |
| `--bs-secondary-color` | `#767676` | 보조 텍스트, 메타 정보                   |
| `--bs-border-color`    | `#dee2e6` | 표준 테두리 색상                         |

## Typography

- **Body Font**: `Pretendard`
- **Code Font**: `JetBrains Mono`, `Fira Code`, monospace

| Level     | Size     | Weight | Utility              |
| --------- | -------- | ------ | -------------------- |
| Display   | 2.5rem~  | 700    | `.display-*`         |
| Heading L | 1.75rem  | 700    | `h1`, `.h1`          |
| Heading M | 1.5rem   | 700    | `h2`, `.h2`          |
| Heading S | 1.25rem  | 600    | `h3`, `.h3`          |
| Body      | 1rem     | 400    | `body`               |
| Small     | 0.875rem | 400    | `.small`, `.text-sm` |

## Layout Rules

- **Grid**: Bootstrap 12-column grid (`.row`, `.col-*`)
- **Spacing**: `spacer` 단위 (1rem = 16px) 기반 유틸리티 사용
- **Containers**: 기본 `.container-xxl` (최대 1320px) 사용
- **Gutter**: 기본 `g-4` (1.5rem) 사용

## Component Guidelines

### Cards (`.card`)

- **흰색 배경(`bg-white`)**, **표준 테두리(`border`)**, **은은한 그림자(`shadow-sm`)** 조합을 기본으로 합니다.
- 내부 패딩은 기본 `p-4` (모바일 `p-3`)를 권장합니다.

### Buttons (`.btn`)

- 주요 액션: `.btn-primary` (Green)
- 보조 액션: `.btn-secondary` (Gray) 또는 `.btn-light`
- 스타일: `rounded-pill`을 활용하여 부드러운 인상을 줍니다.

### Forms (`.form-control`)

- **부트스트랩 표준 스타일(흰색 배경 + 테두리)**을 기본으로 합니다. `bg-light`나 `border-0`를 지양합니다.
- 레이블은 `.form-label`과 `.fw-semibold`를 조합합니다.

## Do & Don't

### ✅ Do

- 부트스트랩 유틸리티를 3개 이상 조합하여 레이아웃 구성 (`d-flex align-items-center justify-content-between gap-3`)
- 시맨틱 태그(`header`, `main`, `footer`, `aside`, `section`) 사용
- 반응형 클래스 적극 활용 (`d-none d-md-block`, `col-lg-8`)

### ❌ Don't

- 인라인 스타일 (`style="..."`) 사용
- 폼 요소에 `bg-light`, `border-0` 등 비표준 스타일 강제 적용
- 한 페이지에 여러 개의 Primary 버튼 배치

---

_Last Updated: 2026-06-10_
