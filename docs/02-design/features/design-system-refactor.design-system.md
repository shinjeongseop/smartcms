# smartcms Design System Draft

> **Feature**: design-system-refactor  
> **Project**: smartcms  
> **Author**: Codex  
> **Date**: 2026-06-06  
> **Status**: Draft  
> **Planning Doc**: `docs/01-plan/features/design-system-refactor.plan.md`

---

## 1. Design Direction

smartcms는 “경량 커뮤니티 CMS/빌더”로 보이게 한다. Bootstrap 기반의 안정감은 유지하되, 기본 Bootstrap 화면처럼 보이지 않도록 `sc-*` 네임스페이스의 제품형 디자인 토큰과 컴포넌트를 적용한다.

### 1.1 Visual Keywords

| Keyword | Meaning |
|---------|---------|
| **Clean Builder** | 관리와 제작이 쉬운 CMS 느낌 |
| **Calm Professional** | 지나치게 화려하지 않고 신뢰감 있는 업무형 UI |
| **Community Portal** | 홈/게시판은 읽기 쉬운 포털형 정보 배치 |
| **Focused Admin** | 관리자 화면은 데이터와 작업 버튼이 빠르게 보이는 구조 |

### 1.2 Avoid

- 보라색/파란색만 반복되는 평균적인 SaaS 화면
- Bootstrap 기본 카드와 버튼만 나열한 화면
- 모든 위젯이 같은 무게로 보이는 평면 레이아웃
- 페이지마다 다른 여백, 버튼, 배지, 카드 스타일
- 인라인 스타일

---

## 2. Token System

### 2.1 Color Tokens

| Token | Value | Usage |
|-------|-------|-------|
| `--sc-bg` | `#f5f7fb` | public site background |
| `--sc-admin-bg` | `#f5f5f9` | admin workspace background |
| `--sc-surface` | `#ffffff` | card, panel, form surface |
| `--sc-text` | `#172033` | primary text |
| `--sc-muted` | `#6b7280` | secondary text |
| `--sc-line` | `#e2e8f0` | border/divider |
| `--sc-primary` | `#2563eb` | primary action |
| `--sc-primary-dark` | `#1d4ed8` | hover/active |
| `--sc-accent` | `#0ea5e9` | highlights, icon gradients |
| `--sc-success` | `#16a34a` | success |
| `--sc-warning` | `#f59e0b` | warning |
| `--sc-danger` | `#dc2626` | destructive |
| `--sc-admin-primary` | `#696cff` | admin sidebar active |

### 2.2 Typography Tokens

| Token | Value | Usage |
|-------|-------|-------|
| `--sc-font-base` | `"Noto Sans KR", "Pretendard", sans-serif` | all text |
| `--sc-text-xs` | `12px` | meta, badge |
| `--sc-text-sm` | `14px` | base UI |
| `--sc-text-md` | `15px` | paragraph |
| `--sc-text-lg` | `18px` | card heading |
| `--sc-text-xl` | `22px` | section heading |
| `--sc-text-hero` | `clamp(32px, 5vw, 56px)` | home hero |

### 2.3 Spacing Tokens

| Token | Value | Usage |
|-------|-------|-------|
| `--sc-space-1` | `4px` | tiny gaps |
| `--sc-space-2` | `8px` | compact gaps |
| `--sc-space-3` | `12px` | form gaps |
| `--sc-space-4` | `16px` | card internal gaps |
| `--sc-space-5` | `24px` | section gaps |
| `--sc-space-6` | `32px` | layout gaps |
| `--sc-space-7` | `48px` | hero and page spacing |

### 2.4 Radius Tokens

| Token | Value | Usage |
|-------|-------|-------|
| `--sc-radius-sm` | `10px` | input, small buttons |
| `--sc-radius-md` | `16px` | cards |
| `--sc-radius-lg` | `24px` | hero, large panels |
| `--sc-radius-pill` | `999px` | pills, badges |

### 2.5 Shadow Tokens

| Token | Value | Usage |
|-------|-------|-------|
| `--sc-shadow-sm` | `0 2px 10px rgba(15, 23, 42, 0.06)` | subtle card |
| `--sc-shadow-md` | `0 12px 32px rgba(15, 23, 42, 0.08)` | widget card |
| `--sc-shadow-lg` | `0 24px 70px rgba(15, 23, 42, 0.12)` | hero/floating |
| `--sc-admin-shadow` | `0 2px 18px rgba(67, 89, 113, 0.10)` | admin panels |

---

## 3. CSS Architecture

### 3.1 Target File Structure

```text
common/css/
  common.css          # temporary import entry during transition
  tokens.css          # CSS variables
  base.css            # reset, body, links, typography
  layout.css          # site/admin shells, grid utilities
  components.css      # buttons, alerts, cards, forms, tables, badges
  site.css            # home/member/public page sections
  admin.css           # admin sidebar/topbar/dashboard/table
  board.css           # board list/view/form/comment
```

### 3.2 Transition Rule

1. Implementation may keep `common.css` as the single loaded asset initially.
2. Internal sections must be reorganized in the same order as target files.
3. After visual stabilization, split into physical files and load via `@import` or multiple `<link>` tags.
4. Do not create inline style blocks.

### 3.3 Namespace Rule

| Prefix | Owner |
|--------|-------|
| `sc-` | smartcms product UI |
| `btn`, `badge`, `navbar`, `container` | Bootstrap |
| `bi` | Bootstrap Icons |
| `smartcms-*` | Legacy only, should be removed or migrated |

---

## 4. Layout System

### 4.1 Public Site Layout

```text
<main class="sc-page">
  <div class="sc-container sc-content-wrap">
    <nav class="sc-navbar">...</nav>
    page content
    <footer class="sc-footer">...</footer>
  </div>
</main>
```

Rules:

- Width: `min(1140px, 100%)`
- Mobile padding: `16px`
- Desktop padding: `20px`
- Header/nav appears on home, board, member pages
- Footer appears on home, board, member pages

### 4.2 Admin Layout

```text
<main class="sc-admin-page">
  <div class="sc-admin-layout">
    <aside class="sc-admin-sidebar">...</aside>
    <section class="sc-admin-workspace">
      <header class="sc-admin-topbar">...</header>
      <div class="sc-admin-content">...</div>
      <footer class="sc-admin-footer">...</footer>
    </section>
  </div>
</main>
```

Rules:

- Desktop: sidebar `280px`, content flexible
- Mobile: sidebar stacks on top
- Admin uses `--sc-admin-primary`
- Admin cards use `--sc-admin-shadow`

---

## 5. Component Rules

### 5.1 Buttons

Use Bootstrap buttons with smartcms spacing/radius conventions.

| Type | Class |
|------|-------|
| Primary | `btn btn-primary rounded-pill px-4` |
| Secondary | `btn btn-secondary rounded-pill px-4` |
| Small primary | `btn btn-primary btn-sm rounded-pill px-3` |
| Danger | `btn btn-danger rounded-pill px-4` |

Rules:

- Primary action: one per section.
- Secondary actions: neutral filled style.
- Destructive action: red/danger and separated from normal actions.

### 5.2 Cards and Panels

| Component | Class | Usage |
|-----------|-------|-------|
| Generic card | `sc-panel` | simple content |
| Widget card | `sc-widget-card` | home widgets |
| Admin card | `sc-admin-card` | admin data panels |
| Stat card | `sc-stat-card` | dashboard metrics |

Rules:

- Cards should not use heavy borders and heavy shadows together.
- Empty states should be a designed state, not plain text only.

### 5.3 Forms

| Component | Class |
|-----------|-------|
| Input | `sc-input` |
| Textarea | `sc-textarea` |
| Select | `sc-select` |
| Field wrapper | `sc-field` |
| 2-column form | `sc-form-2col` |

Rules:

- Every input needs a visible label.
- Password fields use appropriate `autocomplete`.
- Form submit is aligned with primary action.

### 5.4 Tables

| Component | Class |
|-----------|-------|
| Wrapper | `sc-table-wrap` |
| Table | `sc-table` |
| Link | `sc-table-link` |

Rules:

- Desktop: table layout.
- Mobile: either horizontal scroll or card list; board list should prefer card style later.
- Action buttons should be grouped and compact.

### 5.5 Badges

Use Bootstrap badges for semantic status.

| Meaning | Class |
|---------|-------|
| Notice | `badge text-bg-primary` |
| Attachment | `badge text-bg-secondary` |
| Success | `badge text-bg-success` |
| Warning | `badge text-bg-warning` |
| Danger | `badge text-bg-danger` |

---

## 6. Page-Specific Direction

### 6.1 Home

Priority:

1. Hero message
2. Notice strip
3. Latest posts
4. Board widgets
5. Member card
6. Popular posts

Design:

- Hero should be visually strong and not look like a plain Bootstrap card.
- Latest post area should be the most important content block.
- Empty board widget should include CTA where possible.

### 6.2 Board

Priority:

1. Board title and description
2. Search/write actions
3. Post list
4. Pagination

Design:

- List view must be readable.
- Notice/attachment/comment metadata should be visually compact.
- View page should separate title/meta/content/comments clearly.

### 6.3 Member

Priority:

1. Form clarity
2. Trust and safety
3. Smooth navigation back to member/login/register

Design:

- Auth pages should use centered card with subtle background.
- Member pages should use site header/footer, not isolated screens.

### 6.4 Admin

Priority:

1. Sidebar navigation clarity
2. Dashboard metrics
3. Data tables/forms
4. Quick admin actions

Design:

- Sneat-inspired sidebar/topbar pattern.
- Use cards for metrics and content groups.
- Keep action buttons compact.

---

## 7. Implementation Sequence

### Phase 1: Token Lock

- Update `:root` variables.
- Normalize typography and body background.
- Add missing tokens.

### Phase 2: Component Cleanup

- Normalize `sc-panel`, `sc-alert`, `sc-input`, `sc-table`, `sc-section-head`.
- Remove or migrate legacy `smartcms-*` classes.

### Phase 3: Public Site

- Refine `smartcms_site_header()` and `smartcms_site_footer()`.
- Redesign home widgets.
- Clean member pages.

### Phase 4: Board Skin

- Redesign list/view/form templates.
- Improve empty states and metadata.

### Phase 5: Admin

- Finalize sidebar/topbar.
- Redesign dashboard stat cards.
- Normalize admin forms and tables.

### Phase 6: Verification

- `php -l` all PHP files.
- `rg "style=|<style>"`.
- `git diff --check`.
- Check main CSS links on live site.

---

## 8. Acceptance Checklist

- [ ] Common CSS uses documented tokens.
- [ ] No inline styles.
- [ ] No duplicated UI helper functions.
- [ ] Home looks like a modern community CMS landing dashboard.
- [ ] Board list/view/form are visually coherent.
- [ ] Admin looks like a focused dashboard, not public site with extra menu.
- [ ] Member pages no longer feel detached.
- [ ] Live site CSS links return 200.
- [ ] Deployment succeeds.

---

## 9. Open Decisions

| Decision | Recommendation | Reason |
|----------|----------------|--------|
| Split CSS physically now or after first refactor? | After first refactor | Safer to stabilize selectors first. |
| Keep Bootstrap? | Yes | Speeds form/button/grid consistency. |
| Use external template code? | No | Reference visual structure only to avoid license/copy issues. |
| Preserve current PHP route structure? | Yes | Avoid unnecessary backend regression. |
