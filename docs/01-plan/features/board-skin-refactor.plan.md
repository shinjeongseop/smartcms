# 게시판 스킨 기능 및 리팩토링 PDCA Plan

> **Summary**: 게시판 스킨을 그누보드 스타일로 정리하고, 각 스킨의 독립성과 공통 UI 규칙을 유지한 채 구조를 리팩토링한다.
>
> **Project**: smartcms
>
> **Version**: 0.1
>
> **Author**: Codex
>
> **Date**: 2026-06-18
>
> **Status**: Draft

---

## 1. Overview

### 1.1 Purpose

게시판 스킨이 기능별로 늘어나면서 목록, 본문, 글쓰기, 댓글, 첨부, 이미지, 버튼 배치, 테이블 컬럼 규칙이 스킨마다 조금씩 달라지고 있다.
이 계획서는 SmartCMS의 게시판 스킨을 그누보드 스타일의 사용성을 유지하면서도, 스킨별 독립성과 공통 규칙을 동시에 만족하도록 재정리하기 위한 기준을 정의한다.

### 1.2 Background

현재 프로젝트는 기본, 테이블, 카드, 갤러리, Q&A, 공지, FAQ, 웹진, 유튜브 스킨을 운영하고 있다.
기능이 추가될수록 스킨별 구현이 분산되고, 일부 화면에서는 헤더/푸터 중복, 버튼 라벨 불일치, 이미지 출력 순서 차이, 썸네일 정책 혼재 같은 문제가 반복된다.
그누보드 스타일의 “익숙한 게시판 UX”를 유지하되, SmartCMS의 공통 레이아웃과 Bootstrap 네이티브 원칙을 지키는 방향으로 정리할 필요가 있다.

### 1.3 Related Documents

- Requirements: [board-thumbnail.plan.md](board-thumbnail.plan.md)
- Requirements: [board-bulk-actions.plan.md](board-bulk-actions.plan.md)
- Requirements: [board-comment-replies.plan.md](board-comment-replies.plan.md)
- References: [../../../DESIGN.md](../../../DESIGN.md)

---

## 2. Scope

### 2.1 In Scope

- [ ] 게시판 스킨별 공통 규칙 정의
- [ ] 기본/테이블/카드/갤러리/웹진/유튜브 스킨 구조 점검
- [ ] 스킨별 `list`, `view`, `form` 독립성 확보
- [ ] 그누보드 스타일의 버튼 라벨과 배치 통일
- [ ] 링크, 첨부, 이미지, 본문 출력 순서 규칙 정리
- [ ] 썸네일/본문/최신글 이미지 정책 분리
- [ ] 테이블형 게시판의 컬럼 폭과 정렬 기준 통일
- [ ] 공통 head/foot 레이아웃과 스킨 전용 CSS 분리 원칙 정리

### 2.2 Out of Scope

- 게시판 DB 스키마 전면 교체
- 첨부/댓글/이동/복사 로직의 신규 대규모 재설계
- 관리자 권한 모델 변경
- 프레임워크 전환 또는 JavaScript 프레임워크 도입

---

## 3. Requirements

### 3.1 Functional Requirements

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| FR-01 | 각 게시판 스킨은 서로 독립적으로 동작해야 한다. | High | Pending |
| FR-02 | 목록, 본문, 글쓰기 스킨은 공통 레이아웃 규칙을 따라야 한다. | High | Pending |
| FR-03 | 그누보드 스타일에 맞는 버튼 라벨과 액션 배치를 제공해야 한다. | High | Pending |
| FR-04 | 링크, 첨부, 이미지, 본문 출력 순서를 스킨별로 일관되게 유지해야 한다. | High | Pending |
| FR-05 | 테이블형 게시판의 체크박스, 번호, 작성자, 조회, 날짜 열은 고정 폭 기준을 가져야 한다. | Medium | Pending |
| FR-06 | 스킨 전용 CSS는 각 스킨 폴더의 `style.css`로 분리되어야 한다. | High | Pending |
| FR-07 | 본문 이미지와 리스트 썸네일 정책은 서로 다른 목적에 맞게 분리되어야 한다. | High | Pending |

### 3.2 Non-Functional Requirements

| Category | Criteria | Measurement Method |
|----------|----------|-------------------|
| Consistency | 모든 스킨이 동일한 공통 규칙을 따른다 | UI 리뷰 및 코드 비교 |
| Maintainability | 스킨별 스타일이 공통 CSS에 과도하게 의존하지 않는다 | 파일 구조 점검 |
| Accessibility | Bootstrap 네이티브 컴포넌트와 시맨틱 태그를 유지한다 | 마크업 검토 |
| Performance | 스킨 리팩토링 후 렌더링 성능이 눈에 띄게 저하되지 않는다 | 실사용 확인 |

---

## 4. Success Criteria

### 4.1 Definition of Done

- [ ] 스킨별 화면 구조가 독립적이다
- [ ] 공통 레이아웃과 스킨 전용 스타일의 경계가 명확하다
- [ ] 리스트/본문/글쓰기 UI가 그누보드 스타일로 정리된다
- [ ] 이미지, 첨부, 링크 출력 규칙이 스킨 간 일관된다
- [ ] 주요 스킨에서 회귀 없이 동작한다

### 4.2 Quality Criteria

- [ ] 중복 include와 중복 스타일이 줄어든다
- [ ] 테이블형/카드형/갤러리형의 시각 톤이 일관된다
- [ ] 모바일에서도 레이아웃이 깨지지 않는다

---

## 5. Risks and Mitigation

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| 스킨 간 공통 규칙을 강하게 묶으면 개별 스킨의 개성이 사라질 수 있음 | Medium | Medium | 공통 규칙과 스킨별 예외를 분리하고, 예외는 스킨 폴더에서만 처리한다 |
| 기존 스킨을 한 번에 바꾸면 회귀가 발생할 수 있음 | High | High | default, gallery, youtube 순으로 우선 정리하고 단계적으로 확장한다 |
| 공통 CSS가 비대해질 수 있음 | Medium | Medium | 스킨 전용 CSS는 스킨 폴더에 두고 공통 CSS는 최소화한다 |
| 헤더/푸터와 본문 레이아웃 역할이 섞일 수 있음 | High | Medium | 레이아웃은 공통, 콘텐츠는 스킨이라는 책임 분리를 유지한다 |

---

## 6. Architecture Considerations

### 6.1 Project Level Selection

| Level | Characteristics | Selected |
|-------|-----------------|:--------:|
| **Starter** | Simple structure, static sites | - |
| **Dynamic** | Feature-based modules, BaaS integration | ✅ |
| **Enterprise** | Strict layer separation, microservices | - |

### 6.2 Key Architectural Decisions

| Decision | Options | Selected | Rationale |
|----------|---------|----------|-----------|
| Layout ownership | 공통 head/foot / 스킨 개별 include | 공통 head/foot + 스킨별 내용 | 유지보수성과 일관성을 확보하기 위함 |
| Styling | 공통 CSS / 스킨 전용 CSS | 공통 CSS + 스킨 폴더 `style.css` | 특정 스킨만의 스타일이 다른 화면에 번지지 않게 하기 위함 |
| Template structure | 공통 view 하나 / 스킨별 view 독립 | 스킨별 `list/view/form` 독립 | 그누보드 스타일의 확장성과 수정 용이성 확보 |
| Image policy | 리스트와 본문 동일 정책 / 용도별 분리 | 용도별 분리 | 썸네일 품질 저하와 본문 잘림 문제를 줄이기 위함 |

---

## 7. Next Steps

1. [ ] 스킨별 공통 규칙을 정리한 design 문서를 작성한다.
2. [ ] default, gallery, youtube 스킨부터 구조를 우선 점검한다.
3. [ ] 리스트/본문/글쓰기의 버튼, 링크, 첨부, 이미지 순서를 통일한다.
4. [ ] 공통 레이아웃과 스킨 전용 CSS의 책임 경계를 정리한다.

---

## Version History

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 0.1 | 2026-06-18 | Initial draft | Codex |
