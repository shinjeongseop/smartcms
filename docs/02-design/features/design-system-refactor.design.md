# design-system-refactor Design Document

> **Summary**: smartcms UI/UX를 디자인 시스템 기반으로 단계적 리팩토링하기 위한 상세 설계
>
> **Project**: smartcms
> **Version**: 0.1
> **Author**: Codex
> **Date**: 2026-06-06
> **Status**: Draft
> **Planning Doc**: [design-system-refactor.plan.md](../../01-plan/features/design-system-refactor.plan.md)
> **Design System**: [design-system-refactor.design-system.md](./design-system-refactor.design-system.md)

### Pipeline References

| Phase | Document | Status |
|-------|----------|--------|
| Phase 1 | Schema Definition | N/A |
| Phase 2 | Coding Conventions | Partial |
| Phase 3 | Mockup | N/A |
| Phase 4 | API Spec | N/A |
| Phase 5 | Design System | In Progress |

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

## Design Anchor

| Category | Tokens |
|----------|--------|
| **Colors** | primary `#2563eb`, accent `#0ea5e9`, admin primary `#696cff`, bg `#f5f7fb`, admin bg `#f5f5f9`, text `#172033` |
| **Typography** | `"Noto Sans KR", "Pretendard", sans-serif`, base `14px`, paragraph `15px`, hero `clamp(32px, 5vw, 56px)` |
| **Spacing** | 4/8/12/16/24/32/48 scale |
| **Radius** | input `10px`, card `16px`, hero `24px`, pill `999px` |
| **Tone** | Clean Builder, Calm Professional, Community Portal, Focused Admin |
| **Layout** | Public site header/footer, admin sidebar/topbar/workspace, board skin cards/tables |

---

## 1. Overview

### 1.1 Design Goals

- 기능 로직을 유지하면서 UI 레이어를 제품형 CMS 수준으로 정리한다.
- CSS 토큰과 컴포넌트 기준을 먼저 고정하고 화면별 리팩토링을 진행한다.
- Bootstrap은 기본 component/utility로 활용하고 smartcms 고유 UI는 `sc-*` class로 통일한다.
- 홈, 게시판, 회원, 관리자 화면이 동일한 디자인 언어를 갖게 한다.
- 관리자 화면은 public site와 분리된 sidebar/topbar dashboard UX를 유지한다.

### 1.2 Design Principles

- **Token First**: 색상, 여백, radius, shadow를 토큰으로 먼저 정의한다.
- **One Source Per Layout**: 사이트 레이아웃은 `common/ui/navigation.php`, 관리자 레이아웃은 `admin/common.php`에서만 관리한다.
- **No Inline Style**: 모든 스타일은 CSS 파일에만 둔다.
- **Preserve PHP Logic**: 인증, 권한, 게시판 CRUD 로직은 건드리지 않는다.
- **Incremental Refactor**: 한 번에 전부 갈아엎지 않고 화면군 단위로 리팩토링한다.
- **Verify Every Step**: 각 단계마다 PHP lint, inline style scan, CSS link check를 수행한다.

---

## 2. Architecture Options

### 2.0 Architecture Comparison

| Criteria | Option A: Minimal | Option B: Clean | Option C: Pragmatic |
|----------|:-:|:-:|:-:|
| **Approach** | 현재 `common.css`를 유지하고 일부 class만 개선 | CSS를 즉시 다중 파일로 분리하고 helper까지 대규모 재구성 | `common.css` 내부 질서를 먼저 정리하고 화면군별로 단계 적용 |
| **New Files** | 0-1 | 6-8 | 1-3 |
| **Modified Files** | 4-8 | 20+ | 10-16 |
| **Complexity** | Low | High | Medium |
| **Maintainability** | Medium | High | High |
| **Effort** | Low | High | Medium |
| **Risk** | Medium, 구린 느낌이 남을 수 있음 | High, 많은 파일 동시 변경 | Low-Medium, 단계별 검증 가능 |
| **Recommendation** | 빠른 hotfix | 장기 완전 재구성 | **Recommended** |

**Selected**: Option C — **Rationale**: 현재 코드가 PHP 기반이고 이미 live 배포 중이므로, CSS 물리 분리보다 토큰/컴포넌트/화면군 순서로 안정화하는 것이 안전하다. 시각적 품질을 높이면서도 기능 회귀 위험을 관리할 수 있다.

### 2.1 Component Diagram

```text
Browser
  |
  v
common/ui/layout.php
  - Bootstrap / Icons / Fonts / common.css
  |
  +--> Public Site Layout
  |      common/ui/navigation.php
  |      index.php
  |      member/*
  |      board/*
  |      skins/board/default/*
  |
  +--> Admin Layout
         admin/common.php
         admin/dashboard
         admin/users
         admin/boards
         admin/pages
         admin/logs
         admin/database
         admin/settings
```

### 2.2 Data Flow

UI 리팩토링은 DB/API 데이터 흐름을 변경하지 않는다.

```text
Request
  -> existing auth/board/admin PHP logic
  -> render page template
  -> render common layout/helper
  -> load Bootstrap + common.css fixed asset URL
  -> display redesigned UI
```

### 2.3 Dependencies

| Component | Depends On | Purpose |
|-----------|------------|---------|
| `common/ui/layout.php` | `common/config.php` | fixed asset URL, CSS/JS loading |
| `common/ui/navigation.php` | `common/config.php` | public site header/footer |
| `admin/common.php` | `common/auth.php` | admin guard, admin layout |
| `common/ui/components.php` | `common/security.php` | alert, button, section helpers |
| `skins/board/default/*` | `common/board.php` variables | board UI rendering |
| `common/css/common.css` | Bootstrap loaded before it | product UI layer |

---

## 3. Data Model

### 3.1 Entity Definition

No data model changes.

### 3.2 Entity Relationships

No relationship changes.

### 3.3 Database Schema

No database schema changes.

---

## 4. API Specification

No API changes.

---

## 5. UI/UX Design

### 5.1 Screen Layout

#### Public Site

```text
┌────────────────────────────────────────────┐
│ Site Nav                                   │
├────────────────────────────────────────────┤
│ Page Hero / Main Content                   │
│ Cards / Tables / Forms                     │
├────────────────────────────────────────────┤
│ Site Footer                                │
└────────────────────────────────────────────┘
```

#### Admin

```text
┌──────────── Sidebar ───────────┬──────────── Workspace ────────────┐
│ Brand                          │ Topbar: Title + Profile           │
│ Dashboard                      ├───────────────────────────────────┤
│ Users                          │ Cards / Tables / Forms            │
│ Boards                         │                                   │
│ Pages                          │ Footer                            │
└────────────────────────────────┴───────────────────────────────────┘
```

### 5.2 User Flow

```text
Visitor -> Home -> Board -> Post View -> Login/Register if needed
Admin -> Admin Login -> Dashboard -> Manage Users/Boards/Pages/Logs/DB
Member -> Login -> My Page -> Password Change / Logout
```

### 5.3 Component List

| Component | Location | Responsibility |
|-----------|----------|----------------|
| `head.php` | 루트 | Load Bootstrap, fonts, fixed asset CSS, open `<body>` |
| `foot.php` | 루트 | Load Bootstrap bundle, page scripts, close document |
| `smartcms_site_header()` | `common/ui/navigation.php` | Public shell open + nav |
| `smartcms_site_footer()` | `common/ui/navigation.php` | Public shell close + footer |
| `smartcms_admin_page_header()` | `admin/common.php` | Admin shell open + sidebar + topbar |
| `smartcms_admin_footer()` | `admin/common.php` | Admin shell close + footer |
| `smartcms_alert()` | `common/ui/components.php` | Semantic alert |
| `smartcms_button()` | `common/ui/components.php` | Primary button helper |
| Board skin templates | `skins/board/default/*` | Board list/view/form UI |

### 5.4 Page UI Checklist

#### Home

- [ ] Site nav: brand, home, notice, free, qna, admin/install CTA
- [ ] Hero: eyebrow, title, subtitle, primary CTA, secondary CTA
- [ ] Notice strip: latest notice or empty notice state
- [ ] Latest posts widget: board badge, title, date
- [ ] Board widgets: free/qna/notice latest posts
- [ ] Member card: login/register or mypage/logout
- [ ] Popular posts: rank, title, view count
- [ ] Site footer

#### Board List

- [ ] Site nav with active board
- [ ] Board hero: board name, description
- [ ] Search form: keyword input, search button, reset link
- [ ] Write button when authorized
- [ ] Post list/table: notice badge, title, comment count, attachment badge, author, views, date
- [ ] Empty state when no posts
- [ ] Pagination
- [ ] Site footer

#### Board View

- [ ] Site nav with active board
- [ ] Post hero: title, author, view count, date
- [ ] Notice/secret badges
- [ ] Edit button when allowed
- [ ] Post content panel
- [ ] Attachment list
- [ ] Comment list
- [ ] Comment form when allowed
- [ ] Back to list button
- [ ] Site footer

#### Board Form

- [ ] Site nav with active board
- [ ] Form hero
- [ ] Title input
- [ ] Content textarea
- [ ] Notice checkbox when allowed
- [ ] Secret checkbox
- [ ] Attachment input when allowed
- [ ] Submit button
- [ ] Back button
- [ ] Hide/delete action separated when edit mode

#### Member Login/Register

- [ ] Site nav
- [ ] Centered auth card
- [ ] Email input
- [ ] Password input with correct autocomplete
- [ ] Name/company fields for register
- [ ] Submit button
- [ ] Link to counterpart page
- [ ] Site footer

#### Admin Dashboard

- [ ] Sidebar brand
- [ ] Sidebar nav with active state
- [ ] Topbar title
- [ ] Admin profile and logout
- [ ] Stat cards: users, boards, posts, comments
- [ ] Recent posts card
- [ ] Recent login card
- [ ] Recent audit card
- [ ] Admin footer

#### Admin Data Pages

- [ ] Sidebar/topbar/footer
- [ ] Page title
- [ ] Status alert area
- [ ] Form cards
- [ ] Table/list cards
- [ ] Compact action buttons
- [ ] Destructive actions visually separated

---

## 6. Error Handling

### 6.1 Error Code Definition

| Code | Message | Cause | Handling |
|------|---------|-------|----------|
| UI-01 | CSS not loaded | bad asset path or deployment missing file | Check rendered links and HTTP 200 |
| UI-02 | Duplicate helper function | helper declared in multiple files | Keep admin helpers in `admin/common.php` only |
| UI-03 | Layout broken on mobile | grid does not collapse | Add responsive media query |
| UI-04 | Bootstrap conflict | custom selector overrides unexpectedly | Prefix product CSS with `sc-*` and narrow selectors |

### 6.2 Error Response Format

Not applicable. Existing PHP page errors remain unchanged.

---

## 7. Security Considerations

- [ ] Do not change auth/permission logic.
- [ ] Preserve CSRF tokens in all forms.
- [ ] Preserve password field autocomplete rules.
- [ ] Do not expose `config.local.php` or secrets.
- [ ] Do not add inline scripts/styles.
- [ ] Keep admin pages protected by `smartcms_admin_user()`.

---

## 8. Test Plan

### 8.1 Test Scope

| Type | Target | Tool | Phase |
|------|--------|------|-------|
| L1: Static PHP | All PHP files | `php -l` | Do/Check |
| L1: Style policy | Inline style/script check | `rg "style=|<style>"` | Do/Check |
| L1: Git patch quality | Whitespace errors | `git diff --check` | Do/Check |
| L2: HTTP/CSS | Live page status and CSS links | `Invoke-WebRequest` | Check |
| L2: UI Checklist | Required elements by page | HTML response/code review | Check |

### 8.2 L1 Test Scenarios

| # | Test | Command | Expected |
|---|------|---------|----------|
| 1 | PHP syntax | `Get-ChildItem -Recurse -Filter *.php \| php -l` | No syntax errors |
| 2 | Inline style | `rg "style=|<style>" .` | No matches |
| 3 | Diff check | `git diff --check` | No errors |
| 4 | Duplicate admin helpers | `rg "function smartcms_admin_" .` | Only expected files |

### 8.3 L2 UI Action Test Scenarios

| # | Page | Action | Expected Result | Data Verification |
|---|------|--------|----------------|-------------------|
| 1 | Home | Load page | nav, hero, widgets visible | CSS `/common/css/common.css` 200 |
| 2 | Board list | Load `/board/?board=free` | list/search/write area visible | Board data renders or empty state |
| 3 | Login | Load `/member/login/` | auth card visible | form labels present |
| 4 | Admin login | Load `/admin/login/` | login form visible | CSS 200 |
| 5 | Admin dashboard | Load unauthenticated | no fatal error | redirect/login/authorized page |

### 8.4 L3 E2E Scenario Test Scenarios

| # | Scenario | Steps | Success Criteria |
|---|----------|-------|-----------------|
| 1 | Guest browsing | Home → Board list → Post/empty state | No fatal error, CSS loaded |
| 2 | Auth flow smoke | Login page → Register page | Both use public layout |
| 3 | Admin smoke | Admin login → Dashboard request | No duplicate helper fatal error |

### 8.5 Seed Data Requirements

No new seed data required. Existing empty states must be designed.

---

## 9. Clean Architecture

### 9.1 Layer Structure

| Layer | Responsibility | Location |
|-------|----------------|----------|
| Presentation | PHP templates and UI helpers | `index.php`, `member/`, `board/`, `admin/`, `common/ui/` |
| Style | Tokens, layout, components, page styles | `common/css/common.css` initially |
| Domain | Auth, board, settings logic | `common/auth.php`, `common/board.php`, `common/settings.php` |
| Infrastructure | DB and config | `common/database.php`, `common/config.php` |

### 9.2 Dependency Rules

```text
Page templates -> UI helpers -> config/security
Page templates -> domain helpers -> database
CSS -> no PHP dependency
Admin helpers -> auth/config only
Public helpers -> config only
```

### 9.3 File Import Rules

| From | Can Import | Cannot Import |
|------|------------|---------------|
| `common/ui/navigation.php` | `common/config.php` | `admin/common.php` |
| `admin/common.php` | `common/auth.php` | `common/ui/components.php` admin duplicate helpers |
| Board skins | variables from board page | DB directly |
| CSS | none | inline PHP/HTML |

### 9.4 This Feature's Layer Assignment

| Component | Layer | Location |
|-----------|-------|----------|
| Design tokens | Style | `common/css/common.css` |
| Site layout | Presentation | `common/ui/navigation.php` |
| Admin layout | Presentation | `admin/common.php` |
| UI helpers | Presentation | `common/ui/components.php` |
| Board templates | Presentation | `skins/board/default/` |

---

## 10. Coding Convention Reference

### 10.1 Naming Conventions

| Target | Rule | Example |
|--------|------|---------|
| CSS product class | `sc-{component}` | `sc-panel`, `sc-navbar` |
| CSS modifier | `sc-{component}--{state}` | `sc-alert--success` |
| PHP helper | `smartcms_{domain}_{action}` | `smartcms_site_header()` |
| Admin helper | `smartcms_admin_{action}` | `smartcms_admin_footer()` |
| Board helper | `smartcms_board_{action}` | `smartcms_board_recent_posts()` |

### 10.2 Import Order

```php
require_once __DIR__ . '/../../common/domain.php';
require_once __DIR__ . '/../../common/ui/layout.php';
require_once __DIR__ . '/../../common/ui/components.php';
require_once __DIR__ . '/../../common/ui/navigation.php';
```

### 10.3 Environment Variables

No new environment variables.

### 10.4 This Feature's Conventions

| Item | Convention Applied |
|------|-------------------|
| Component naming | `sc-*` CSS namespace |
| File organization | Keep `common.css` during transition; prepare split structure |
| State management | PHP-rendered state only |
| Error handling | Preserve existing PHP messages and alerts |

---

## 11. Implementation Guide

### 11.1 File Structure

```text
common/
  css/
    common.css
  ui/
    layout.php
    navigation.php
    components.php
admin/
  common.php
skins/
  board/
    default/
      boards.php
      list.php
      view.php
      form.php
member/
  login/index.php
  register/index.php
  mypage/index.php
  password/index.php
```

### 11.2 Implementation Order

1. [ ] Token lock in `common/css/common.css`
2. [ ] Public layout refinement in `common/ui/navigation.php`
3. [ ] Component helper cleanup in `common/ui/components.php`
4. [ ] Home redesign in `index.php`
5. [ ] Board skin redesign in `skins/board/default/*`
6. [ ] Admin UI completion in `admin/common.php` and admin pages
7. [ ] Member/auth page cleanup
8. [ ] Install page cleanup
9. [ ] Verification and deployment

### 11.3 Session Guide

#### Module Map

| Module | Scope Key | Description | Estimated Turns |
|--------|-----------|-------------|:---------------:|
| Design tokens and components | `module-1` | CSS token lock, base layout, shared components | 2-3 |
| Public site and home | `module-2` | Site nav/footer, home hero/widgets/member/popular sections | 2-3 |
| Board skin | `module-3` | Board list/view/form/comment UI | 2-3 |
| Admin UI | `module-4` | Sneat-style sidebar/topbar/dashboard/forms/tables | 2-3 |
| Member and install | `module-5` | Auth/member/install polish | 1-2 |
| Verification | `module-6` | lint, inline scan, HTTP/CSS checks, deployment | 1 |

#### Recommended Session Plan

| Session | Phase | Scope | Turns |
|---------|-------|-------|:-----:|
| Session 1 | Design | 전체 | 1 |
| Session 2 | Do | `--scope module-1,module-2` | 4-6 |
| Session 3 | Do | `--scope module-3` | 3-4 |
| Session 4 | Do | `--scope module-4` | 3-4 |
| Session 5 | Do + Check | `--scope module-5,module-6` | 3-4 |

---

## Version History

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 0.1 | 2026-06-06 | Initial design document | Codex |
