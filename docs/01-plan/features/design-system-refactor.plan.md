# design-system-refactor Planning Document

> **Summary**: smartcms 전체 UI/UX를 모던한 디자인 시스템 기반으로 재정리한다.
>
> **Project**: smartcms
> **Version**: 0.1
> **Author**: Codex
> **Date**: 2026-06-06
> **Status**: Draft

---

## Executive Summary

| Perspective | Content |
|-------------|---------|
| **Problem** | 현재 smartcms는 기능은 CMS 형태로 확장됐지만, 화면별 UI 밀도, 여백, 카드, 버튼, 폼, 관리자 레이아웃의 시각 언어가 통일되지 않아 제품 완성도가 낮아 보인다. |
| **Solution** | CSS를 디자인 토큰과 컴포넌트 중심으로 재구성하고, 사이트/게시판/회원/관리자 화면을 하나의 디자인 시스템 기준으로 순차 리팩토링한다. |
| **Function/UX Effect** | 사용자는 홈, 게시판, 회원, 관리자 화면에서 동일한 탐색 구조와 예측 가능한 버튼/폼/카드 패턴을 경험한다. 관리자는 Sneat 계열의 사이드바/탑바/카드형 업무 화면으로 더 빠르게 기능을 찾는다. |
| **Core Value** | smartcms를 단순 PHP 기능 모음이 아니라 재사용 가능한 경량 CMS/빌더 제품처럼 보이게 만들고, 이후 테마/스킨 확장의 기반을 만든다. |

---

## Context Anchor

| Key | Value |
|-----|-------|
| **WHY** | 기능 대비 화면 완성도가 낮아 보이는 문제를 해결해 smartcms를 제품형 CMS로 보이게 한다. |
| **WHO** | 사이트 방문자, 게시판 사용자, 회원, 관리자, 그리고 향후 smartcms를 다른 프로젝트에 이식할 개발자. |
| **RISK** | CSS/HTML을 무리하게 바꾸면 기존 게시판, 회원, 관리자 기능이 깨지거나 Bootstrap과 커스텀 CSS가 충돌할 수 있다. |
| **SUCCESS** | 공통 디자인 토큰 문서화, CSS 역할 분리, 사이트/게시판/회원/관리자 주요 화면의 일관된 header/footer/card/form/table 패턴 적용, 주요 페이지 CSS 200 응답 확인. |
| **SCOPE** | 1차 디자인 시스템 수립, 2차 공통 컴포넌트 정리, 3차 사이트/게시판 리디자인, 4차 관리자 리디자인, 5차 회원/설치 화면 정리. |

---

## 1. Overview

### 1.1 Purpose

smartcms의 전체 UI/UX를 디자인 시스템 기반으로 재정리한다. 현재 코어 기능은 유지하되, 화면 레이어를 정돈해서 홈, 게시판, 회원, 관리자 화면이 하나의 제품처럼 보이도록 한다.

### 1.2 Background

현재 smartcms는 설치 마법사, 회원, 권한, 게시판, 관리자, DB 관리 기능까지 갖추고 있다. 그러나 기능 추가 과정에서 Bootstrap 클래스, `sc-*` 커스텀 클래스, 기존 `smartcms-*` 클래스가 섞였고, 화면별 시각적 무게가 다르게 느껴진다. 특히 홈과 관리자 화면은 구조는 잡혔지만 디자인 품질이 충분히 모던하지 않다.

### 1.3 Related Documents

- Existing core plan: `docs/01-plan/features/smartcms-core.plan.md`
- Existing core design: `docs/02-design/features/smartcms-core.design.md`
- Design system draft: `docs/02-design/features/design-system-refactor.design-system.md`
- Live site: `https://smartcms.dothome.co.kr/`

---

## 2. Scope

### 2.1 In Scope

- [ ] 디자인 시스템 토큰 수립
- [ ] CSS 파일 역할 분리 계획 수립
- [ ] 공통 site header/footer 기준 정리
- [ ] 공통 admin header/footer 기준 정리
- [ ] 홈 위젯 UI 재설계
- [ ] 게시판 목록/보기/쓰기 스킨 리디자인
- [ ] 회원 로그인/회원가입/마이페이지 UI 정리
- [ ] 관리자 Sneat 계열 레이아웃 완성
- [ ] 공통 컴포넌트 helper 정리
- [ ] 주요 페이지 CSS 연결 검증

### 2.2 Out of Scope

- DB 스키마 변경
- 회원/권한/게시판 도메인 로직 변경
- API 기능 추가
- 새 JavaScript 프레임워크 도입
- Tailwind 도입
- 외부 유료 템플릿의 코드 복제

---

## 3. Requirements

### 3.1 Functional Requirements

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| FR-01 | 디자인 토큰을 문서와 CSS 변수로 정의한다. | High | Pending |
| FR-02 | 모든 공개 페이지는 사이트 공통 header/footer를 사용한다. | High | Pending |
| FR-03 | 모든 관리자 보호 페이지는 관리자 공통 layout을 사용한다. | High | Pending |
| FR-04 | 게시판 스킨은 목록/상세/폼의 버튼, 배지, 카드, 테이블 스타일을 통일한다. | High | Pending |
| FR-05 | 관리자 화면은 Sneat 계열의 sidebar, topbar, card, table 패턴을 따른다. | High | Pending |
| FR-06 | 회원/인증 페이지는 공통 사이트 디자인과 어울리는 card/form 패턴을 사용한다. | Medium | Pending |
| FR-07 | 설치 화면은 제품 설정 wizard처럼 보이되 public site와 충돌하지 않는다. | Medium | Pending |
| FR-08 | 모든 정적 CSS/JS asset은 `base_url`과 독립된 root absolute path를 사용한다. | High | Done |

### 3.2 Non-Functional Requirements

| Category | Criteria | Measurement Method |
|----------|----------|-------------------|
| Maintainability | CSS 역할이 토큰/레이아웃/컴포넌트/페이지별로 구분된다. | 파일 구조 및 class usage review |
| Consistency | 버튼, 카드, 폼, 테이블, 배지, empty state가 동일 규칙을 따른다. | 주요 페이지 HTML/CSS 리뷰 |
| Responsiveness | 320px 이상 모바일에서 주요 화면이 1열로 자연스럽게 전환된다. | 코드 기준 media query + 필요 시 수동 확인 |
| Accessibility | 텍스트 대비, focus 상태, nav aria-label, form label을 유지한다. | HTML review |
| Performance | CSS는 불필요한 중복을 줄이고 CDN + 공통 CSS 구조를 유지한다. | file size/stat review |

---

## 4. Success Criteria

### 4.1 Definition of Done

- [ ] 디자인 시스템 문서가 생성되어 색상, 타이포, spacing, radius, shadow, 컴포넌트 규칙을 설명한다.
- [ ] CSS 구조가 디자인 시스템 기준으로 정리된다.
- [ ] 홈, 게시판 목록, 게시글 보기, 글쓰기, 로그인, 회원가입, 관리자 대시보드가 일관된 UI를 갖는다.
- [ ] 기존 PHP 기능과 라우팅이 유지된다.
- [ ] 모든 페이지는 인라인 스타일 없이 CSS 파일을 통해 스타일링된다.
- [ ] GitHub Actions 배포 후 주요 페이지 CSS 응답이 200으로 확인된다.

### 4.2 Quality Criteria

- [ ] `php -l` 전체 통과
- [ ] `git diff --check` 통과
- [ ] `rg "style=|<style>"`에서 인라인 스타일 없음
- [ ] 주요 CSS 파일 200 응답 확인
- [ ] 관리자 페이지 fatal error 없음

---

## 5. Risks and Mitigation

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Bootstrap과 커스텀 CSS 충돌 | High | Medium | Bootstrap은 utility와 basic component에 쓰고, 제품 고유 UI는 `sc-*` namespace로 한정한다. |
| CSS 전면 정리 중 기존 화면 깨짐 | High | Medium | 홈/게시판/관리자/회원 순서로 단계별 커밋하고 각 단계별 PHP 문법 및 CSS 연결을 확인한다. |
| 디자인이 또 평균적인 Bootstrap 화면처럼 보임 | Medium | Medium | 색상, shadow, card density, admin sidebar, hero typography를 토큰으로 명확히 고정한다. |
| 관리자와 사이트 UI가 서로 충돌 | Medium | Low | site layout과 admin layout을 별도 함수/섹션/class prefix로 분리한다. |
| 너무 큰 단일 커밋 | Medium | Medium | 디자인 시스템, 사이트, 게시판, 관리자, 회원 화면 단위로 분리 커밋한다. |

---

## 6. Impact Analysis

### 6.1 Changed Resources

| Resource | Type | Change Description |
|----------|------|--------------------|
| `common/css/common.css` | CSS | 토큰, 레이아웃, 컴포넌트, 페이지 스타일 정리 또는 분리 |
| `common/ui/navigation.php` | UI Helper | 사이트 header/footer 기준 정리 |
| `admin/common.php` | UI Helper | 관리자 sidebar/topbar/footer 기준 정리 |
| `common/ui/components.php` | UI Helper | alert, button, section head, card/table helper 확장 |
| `index.php` | Page | 홈 위젯 구조와 시각 계층 개선 |
| `skins/board/default/*` | Board Skin | 게시판 목록/상세/폼 UI 현대화 |
| `member/*` | Member Pages | 인증/회원 화면 카드/폼 UX 개선 |

### 6.2 Current Consumers

| Resource | Operation | Code Path | Impact |
|----------|-----------|-----------|--------|
| `common/css/common.css` | READ | 모든 페이지 `head.php` / `foot.php` | Needs verification |
| `common/ui/navigation.php` | READ | 홈, 게시판, 회원 페이지 | Needs verification |
| `admin/common.php` | READ | 관리자 보호 페이지 | Needs verification |
| `common/ui/components.php` | READ | 설치, 회원, 관리자, 게시판 | Needs verification |
| `skins/board/default/*` | READ | `board/index.php`, `board/view/index.php`, `board/write/index.php`, `board/edit/index.php` | Needs verification |

### 6.3 Verification

- [ ] 모든 helper 중복 선언 여부 확인
- [ ] 공통 CSS 링크 200 확인
- [ ] 설치 CSS/JS 링크 200 확인
- [ ] 홈/게시판/회원/관리자 페이지 HTTP 200 확인
- [ ] 게시판 글 목록/보기/쓰기 기능 유지 확인

---

## 7. Architecture Considerations

### 7.1 Project Level Selection

| Level | Characteristics | Recommended For | Selected |
|-------|-----------------|-----------------|:--------:|
| **Starter** | Simple structure | Static sites, landing pages | ☐ |
| **Dynamic** | Feature-based modules, backend integration | PHP CMS with DB, auth, board, admin | ☑ |
| **Enterprise** | Strict layered system | Large-scale systems | ☐ |

### 7.2 Key Architectural Decisions

| Decision | Options | Selected | Rationale |
|----------|---------|----------|-----------|
| Styling Base | Bootstrap only / Custom only / Hybrid | Hybrid | Bootstrap provides stable component base, `sc-*` provides product identity. |
| CSS Organization | Single CSS / Split by role | Split by role after token lock | Easier maintenance and safer future theme support. |
| Component Namespace | Bootstrap classes / `smartcms-*` / `sc-*` | `sc-*` | Shorter, consistent, avoids legacy class confusion. |
| Admin Layout | Same as site / Dedicated admin | Dedicated admin | Admin UX needs sidebar/topbar/data cards unlike public pages. |
| Board Skin | Logic rewrite / Skin-only UI refactor | Skin-only UI refactor | Preserve board domain logic and reduce regression risk. |
| JavaScript | No new framework / React/Vue | No new framework | PHP CMS should remain portable and lightweight. |

### 7.3 Clean Architecture Approach

```text
Selected Level: Dynamic

UI Layer:
  common/ui/layout.php
  common/ui/navigation.php
  common/ui/components.php
  admin/common.php
  skins/board/default/

Style Layer:
  common/css/common.css
  future: tokens.css, base.css, layout.css, components.css, site.css, admin.css, board.css

Domain Layer:
  common/auth.php
  common/board.php
  common/settings.php
  common/database.php
```

---

## 8. Convention Prerequisites

### 8.1 Existing Project Conventions

- [ ] `CLAUDE.md` has coding conventions section
- [ ] `docs/01-plan/conventions.md` exists
- [ ] `CONVENTIONS.md` exists at project root
- [ ] ESLint configuration
- [ ] Prettier configuration
- [ ] TypeScript configuration

### 8.2 Conventions to Define/Verify

| Category | Current State | To Define | Priority |
|----------|---------------|-----------|:--------:|
| **CSS naming** | Mixed | `sc-*` namespace with role-based naming | High |
| **CSS files** | Mostly single common.css | Split plan and import order | High |
| **Components** | Partial helpers | Alert, button, card, section, table, empty state helpers | High |
| **Admin layout** | Common helper exists | One source only in `admin/common.php` | High |
| **Assets** | Fixed via `smartcms_asset_url()` | Keep asset paths independent of `base_url` | High |

### 8.3 Environment Variables Needed

| Variable | Purpose | Scope | To Be Created |
|----------|---------|-------|:-------------:|
| 없음 | 이번 UI 리팩토링은 환경변수 추가가 필요 없다. | - | ☐ |

### 8.4 Pipeline Integration

| Phase | Status | Document Location | Command |
|-------|:------:|-------------------|---------|
| Phase 2 (Convention) | Partial | This plan + design system doc | `$bkit-pdca design-system-refactor` |
| Phase 5 (Design System) | Starting | `docs/02-design/features/design-system-refactor.design-system.md` | current |
| Phase 6 (UI Integration) | Pending | implementation commits | after approval |

---

## 9. Next Steps

1. [ ] 디자인 시스템 문서 확정
2. [ ] CSS 파일 분리 여부 결정
3. [ ] 공통 컴포넌트 helper 확장 설계
4. [ ] 홈 화면 리디자인 구현
5. [ ] 게시판 스킨 리디자인 구현
6. [ ] 관리자 화면 리디자인 구현
7. [ ] 회원/설치 화면 정리
8. [ ] 주요 페이지 CSS/HTTP 검증

---

## Version History

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 0.1 | 2026-06-06 | Initial design refactor plan | Codex |
