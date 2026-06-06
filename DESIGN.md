# smartcms Design System

> Version: alpha
> Source: Cursor-design-analysis
> Rule: 새 페이지, 스킨, 관리자 화면, 컴포넌트는 이 문서를 우선 기준으로 디자인한다.

## Overview

smartcms의 디자인 방향은 조용하고 자신감 있는 개발자 도구 브랜드다. 전형적인 어두운 IDE 톤 대신 따뜻한 크림 캔버스 `#f7f7f4` 위에 웜 블랙 잉크 `#26251e`를 사용한다. 주요 액션 전압은 Cursor Orange `#f54e00` 하나만 사용하며, primary CTA와 브랜드 워드마크에 제한적으로 적용한다.

디스플레이 타이포그래피는 굵게 밀어붙이지 않고 400 weight와 음수 letter-spacing으로 편집지 같은 차분한 인상을 만든다. 코드 표면은 항상 `JetBrains Mono`를 사용한다. 그림자 대신 1px hairline과 흰 카드 대비로 깊이를 만든다.

## Design Principles

- Warm cream canvas, never pure white page floor.
- Ink is warm near-black, never pure black.
- Cursor Orange is the only brand CTA color and must be used scarcely.
- Display headings use weight 400 with negative letter-spacing.
- No drop shadows. Use hairlines and white-on-cream contrast.
- Cards use 1px borders, 12px radius, generous rhythm.
- Code surfaces always use JetBrains Mono.
- Timeline pastel colors are only for in-product AI action timelines.
- Section rhythm defaults to 80px vertical spacing.

## Tokens

### Colors

| Token | Value | Use |
|---|---:|---|
| `--sc-primary` | `#f54e00` | Primary CTA, wordmark |
| `--sc-primary-dark` | `#d04200` | Active/pressed CTA |
| `--sc-text` | `#26251e` | Display, strong text |
| `--sc-body` | `#5a5852` | Body copy |
| `--sc-muted` | `#807d72` | Subtitles, secondary metadata |
| `--sc-muted-soft` | `#a09c92` | Disabled/quiet metadata |
| `--sc-bg` | `#f7f7f4` | Page canvas |
| `--sc-surface-soft` | `#fafaf7` | Soft pane background |
| `--sc-surface` | `#ffffff` | Cards |
| `--sc-surface-strong` | `#e6e5e0` | Badge/tag background |
| `--sc-line` | `#e6e5e0` | Default hairline |
| `--sc-line-soft` | `#efeee8` | Soft divider |
| `--sc-line-strong` | `#cfcdc4` | Strong outline |
| `--sc-success` | `#1f8a65` | Confirmation |
| `--sc-danger` | `#cf2d56` | Validation/error |

### AI Timeline Colors

These are scoped to in-product AI action timelines only.

| Stage | Value |
|---|---:|
| Thinking | `#dfa88f` |
| Grep | `#9fc9a2` |
| Read | `#9fbbe0` |
| Edit | `#c0a8dd` |
| Done | `#c08532` |

## Typography

CursorGothic is the target typeface. Because it is licensed, implementation uses this fallback:

```css
"CursorGothic", "Inter", "Noto Sans KR", system-ui, "Helvetica Neue", Helvetica, Arial, sans-serif
```

Code fallback:

```css
"JetBrains Mono", "Fira Code", Consolas, monospace
```

| Token | Size | Weight | Line Height | Letter Spacing | Use |
|---|---:|---:|---:|---:|---|
| Display mega | 72px | 400 | 1.1 | -2.16px | Home hero |
| Display lg | 36px | 400 | 1.2 | -0.72px | Section heads |
| Display md | 26px | 400 | 1.25 | -0.325px | Sub-section heads |
| Display sm | 22px | 400 | 1.3 | -0.11px | Card group titles |
| Title md | 18px | 600 | 1.4 | 0 | Component titles |
| Title sm | 16px | 600 | 1.4 | 0 | List labels |
| Body md | 16px | 400 | 1.5 | 0 | Body |
| Body sm | 14px | 400 | 1.5 | 0 | Footer, metadata |
| Caption | 13px | 400 | 1.4 | 0 | Captions |
| Caption uppercase | 11px | 600 | 1.4 | 0.88px | Labels, pills |
| Code | 13px | 400 | 1.5 | 0 | Code blocks |
| Button | 14px | 500 | 1.0 | 0 | Buttons |

## Layout

- Container max width: about `1200px`.
- Section padding: `80px`.
- Grid: editorial 12-column spirit, with 2-up or 3-up card layouts on desktop.
- Mobile collapses to one column below `640px`.
- Top navigation height: `64px`.

## Radius

| Token | Value | Use |
|---|---:|---|
| none | 0px | Reserved |
| xs | 4px | Inline tags |
| sm | 6px | Compact rows |
| md | 8px | Buttons, inputs |
| lg | 12px | Cards, panes |
| xl | 16px | Rare large cards |
| pill | 9999px | Pills, badges |

## Components

### Top Navigation

Use cream canvas background, ink text, 64px height, no shadow. Brand wordmark uses Cursor Orange. Menus are compact 14px / 500. Active state is orange text, not a filled pill unless necessary.

### Buttons

- Primary: orange background, white text, 8px radius, 40px target height.
- Primary active: `#d04200`.
- Secondary: white surface, ink text, 1px strong hairline.
- Tertiary: transparent ink text.
- Download/strong action: ink background, cream text.

### Cards

Cards are white on cream canvas with 1px hairline, 12px radius, no shadow. Use 24px padding for normal cards and 32px for pricing/large cards.

### Forms

Inputs use white surface, ink text, 1px strong hairline, 8px radius, 44px height. Focus uses orange border only, not glow.

### Tables and Lists

Use hairlines, no drop shadows. Hover may use `#fafaf7`. Avoid heavy filled table headers.

### Hero

Hero uses cream canvas and editorial typography. Avoid dark gradient hero by default. CTA color must remain orange and scarce.

### Footer

Footer uses cream background and body text. Use link columns when the site grows. No shadow.

## Do

- Use `common/css/common.css` design tokens rather than inline hex.
- Keep primary CTAs orange.
- Use `sc-*` classes for product components.
- Prefer Bootstrap structure but override visual tone through `common.css`.
- Keep display typography at weight 400.
- Use hairline borders instead of shadows.

## Don't

- Do not use inline style attributes.
- Do not introduce a second CTA color.
- Do not use blue/purple gradients as the product identity.
- Do not add drop shadows for cards.
- Do not use timeline pastel colors outside AI timeline UI.
- Do not make display headings bold.

## Implementation Notes

- CSS source of truth: `common/css/common.css`.
- Project design source of truth: `DESIGN.md`.
- New PHP pages must include common layout helpers and inherit `common.css`.
- Any page-specific CSS must follow these tokens and must not redefine the brand palette.
