# 게시판 스킨 기능 및 리팩토링 Design

> **Summary**: 게시판 스킨을 그누보드 스타일의 사용성으로 정리하되, 스킨별 `list/view/form` 독립성과 공통 레이아웃 규칙을 함께 유지하는 설계를 정의한다.
>
> **Project**: smartcms
>
> **Version**: 0.1
>
> **Author**: Codex
>
> **Date**: 2026-06-19
>
> **Status**: Draft
>
> **Planning Doc**: [board-skin-refactor.plan.md](../01-plan/features/board-skin-refactor.plan.md)

---

## 1. Overview

### 1.1 Design Goals

- 스킨별 `list/view/form` 템플릿은 독립적으로 유지한다.
- 공통화 대상은 `head/foot`, 공통 헬퍼, 공통 UI 규칙, 공통 CSS로 제한한다.
- 그누보드 스타일의 익숙한 게시판 흐름을 유지한다.
- 리스트/본문/글쓰기/댓글/첨부/이미지/링크의 출력 순서를 일관되게 정리한다.
- 특정 스킨만 필요한 스타일은 스킨 폴더의 `style.css`에만 둔다.
- 공통 뷰 하나로 합치는 방식은 사용하지 않는다.

### 1.2 Design Principles

- 스킨 독립성 우선
- 공통 규칙은 상위 레이어에서만 관리
- Bootstrap 네이티브 컴포넌트만 사용
- 시맨틱 태그 필수 사용
- 특정 스킨의 UI 변경이 다른 스킨으로 번지지 않도록 경계 분리

---

## 2. Architecture

### 2.1 Component Diagram

```text
Browser
  -> board router / controller
    -> board metadata loader
    -> skin resolver
    -> common helpers (board/image/auth)
    -> skin template (list/view/form)
    -> skin style.css
    -> common head.php / foot.php
```

### 2.2 Data Flow

```text
Request
  -> board key / post id / action validate
  -> board metadata + post/files/comments load
  -> skin template select
  -> common helper render
  -> skin-specific markup + common layout output
```

### 2.3 Dependencies

| Component | Depends On | Purpose |
|-----------|------------|---------|
| board router | `common/board.php` | 게시판 진입점과 스킨 선택 |
| skin template | board metadata, post/files/comments | 화면 출력 |
| common image helpers | `common/image.php` | 썸네일, 원본, 캐시 처리 |
| common head/foot | `head.php`, `foot.php` | 공통 레이아웃 |
| skin stylesheet | `skins/board/{skin}/style.css` | 스킨 전용 스타일 |

---

## 3. Data Model

### 3.1 Entity Definition

새 테이블은 추가하지 않는다. 기존 엔티티를 그대로 사용한다.

```php
// boards
[
  'id' => int,
  'board_key' => string,
  'board_name' => string,
  'skin' => string,
  'display_type' => string,
  'author_display_mode' => string,
  'board_list_level' => int,
  'board_view_level' => int,
  'board_write_level' => int,
  'board_manage_level' => int,
]

// board_posts
[
  'id' => int,
  'board_id' => int,
  'title' => string,
  'content' => string,
  'content_mode' => 'text'|'editor',
  'link_url' => string,
  'link_url_1' => string,
  'link_url_2' => string,
  'view_count' => int,
  'comment_count' => int,
  'attachment_count' => int,
  'is_notice' => int,
  'is_secret' => int,
]

// board_files
[
  'id' => int,
  'board_id' => int,
  'post_id' => int,
  'original_name' => string,
  'stored_name' => string,
  'file_path' => string,
  'file_size' => int,
  'mime_type' => string,
  'download_count' => int,
]

// board_comments
[
  'id' => int,
  'board_id' => int,
  'post_id' => int,
  'parent_id' => ?int,
  'content' => string,
  'is_hidden' => int,
]
```

### 3.2 Entity Relationships

```text
[Board] 1 ---- N [Post] 1 ---- N [File]
   |                  |
   |                  +---- N [Comment]
   |
   +---- N [Skin setting values]
```

실제 스킨 리팩토링은 데이터 구조를 바꾸지 않고, 기존 필드의 해석과 렌더링 규칙을 정리하는 방향으로 진행한다.

---

## 4. Server / Route Specification

새 API는 추가하지 않는다. 기존 게시판 라우트와 액션을 그대로 사용한다.

| Method | Path | Description | Auth |
|--------|------|-------------|------|
| GET | `/board/` | 게시판 목록 | 게시판 권한 |
| GET | `/board/view/` | 게시글 상세 | 게시판 권한 |
| GET | `/board/write/` | 글쓰기 폼 | 작성 권한 |
| GET | `/board/edit/` | 수정 폼 | 작성자/관리자 |
| POST | `/board/write/` | 게시글 저장 | 작성 권한 |
| POST | `/board/edit/` | 게시글 수정 | 작성자/관리자 |
| POST | `/board/action/` | bulk 삭제/이동/복사 | 관리자 권한 |

### 동작 원칙

- 라우트는 공통이며, 화면만 스킨별 템플릿으로 바뀐다.
- action 처리 후에는 기존 PRG 규칙을 유지한다.
- 스킨별 마크업 차이는 `view.php`, `list.php`, `form.php`에서만 발생한다.

---

## 5. UI/UX Design

### 5.1 Screen Layout

#### 목록

- 상단 검색 영역
- 본문 목록 영역
- 하단 페이징
- 새글 버튼과 관리자 액션은 그누보드 스타일의 배치 규칙을 따른다

#### 상세

- 제목 영역
- 작성자/날짜/조회/댓글 메타 정보
- 링크
- 첨부파일
- 이미지
- 본문
- 하단 액션 버튼
- 댓글 영역

#### 글쓰기/수정

- 제목
- 링크 입력
- 첨부파일
- 본문 입력
- 저장/목록 버튼

### 5.2 Component List

| Component | Location | Responsibility |
|-----------|----------|----------------|
| BoardList | `skins/board/{skin}/list.php` | 목록 렌더링 |
| BoardView | `skins/board/{skin}/view.php` | 상세 렌더링 |
| BoardForm | `skins/board/{skin}/form.php` | 글쓰기/수정 렌더링 |
| Skin CSS | `skins/board/{skin}/style.css` | 스킨별 시각 규칙 |
| Common helpers | `common/board.php`, `common/image.php` | 데이터/이미지 처리 |
| Shared layout | `head.php`, `foot.php` | 공통 프레임 |

### 5.3 Styling Rules

- 테이블형 게시판은 번호/체크박스/작성자/조회/날짜 열 폭을 고정한다.
- 갤러리/웹진은 카드 간격과 이미지 비율을 스킨 전용 CSS에서 관리한다.
- 본문 이미지는 확대하지 않고, 원본보다 작게만 보이도록 제한한다.
- 링크, 첨부, 이미지 출력 순서는 스킨별로 일관되게 유지한다.

---

## 6. Error Handling

| Code | Message | Handling |
|------|---------|----------|
| 400 | 잘못된 요청입니다. | 폼 재입력 |
| 403 | 권한이 없습니다. | 접근 차단 |
| 404 | 게시글을 찾을 수 없습니다. | 404 화면 |
| 419 | CSRF 검증 실패 | 재시도 안내 |
| 500 | 서버 오류가 발생했습니다. | 공통 에러 페이지 |

추가 규칙:

- 스킨 파일이 없으면 기본 스킨으로 fallback 한다.
- 스킨 전용 CSS가 없으면 공통 규칙만 적용한다.
- 본문에 이미지가 없으면 이미지 섹션은 렌더링하지 않는다.

---

## 7. Security Considerations

- [ ] CSRF 검증 유지
- [ ] 게시판 권한 검증 유지
- [ ] 파일 경로 화이트리스트 기반 처리
- [ ] 본문 HTML 정제 유지
- [ ] 외부 링크는 `rel="noopener noreferrer"` 기본 적용
- [ ] 스킨 파일 경로는 입력값 직접 연결 금지

---

## 8. Test Plan

| Type | Target | Tool |
|------|--------|------|
| Manual | default/list/view/form | 브라우저 확인 |
| Manual | gallery/list/view | 브라우저 확인 |
| Manual | youtube/view | 브라우저 확인 |
| Manual | table형 컬럼 정렬 | 브라우저 확인 |
| Manual | 본문 이미지 / 첨부 / 링크 순서 | 브라우저 확인 |
| Unit | helper rendering functions | PHP lint / 함수 검토 |
| Regression | 스킨 독립성 | 화면별 비교 |

검증 포인트:

- 갤러리, 유튜브, 기본 게시판이 서로 다른 화면 구조를 유지하는지 확인
- 공통 레이아웃이 중복 렌더링되지 않는지 확인
- 스킨 전용 CSS가 다른 스킨에 영향을 주지 않는지 확인
- 본문 이미지가 확대되지 않는지 확인

---

## 9. Implementation Guide

### 9.1 Implementation Order

1. [ ] 스킨별 `list/view/form` 구조를 확정한다.
2. [ ] 공통 헬퍼와 공통 레이아웃 경계를 정리한다.
3. [ ] 각 스킨의 `style.css`를 독립적으로 맞춘다.
4. [ ] 링크, 첨부, 이미지, 본문 출력 순서를 통일한다.
5. [ ] 테이블형 컬럼 폭과 카드형 간격을 정리한다.
6. [ ] 기본 스킨, 갤러리, 유튜브부터 회귀 검증한다.

### 9.2 File Ownership

- 레이아웃: `head.php`, `foot.php`
- 공통 로직: `common/board.php`, `common/image.php`
- 게시판 화면: `skins/board/{skin}/list.php`, `view.php`, `form.php`
- 스킨 전용 스타일: `skins/board/{skin}/style.css`

---

## Version History

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 0.1 | 2026-06-19 | Initial draft | Codex |
