# AGENTS.md instructions for D:\smartcms

## 기본 지침

- 모든 답변은 한국어로 한다.
- 작업 시작 전 항상 `git status --short --untracked-files=all` 및 `git branch --show-current`를 확인한다.
- 기본 브랜치는 `main`이다.
- 로컬 변경사항은 커밋 후 GitHub에 푸시한다.
- GitHub Actions 배포가 기본이며, 서버 직접 수정이나 FTP 직접 업로드는 하지 않는다.

## 디자인 지침

- 새 페이지, 스킨, 관리자 화면, 컴포넌트는 반드시 루트의 `DESIGN.md`를 먼저 확인하고 따른다.
- 디자인 기준은 Cursor editorial 시스템이다.
- Warm cream canvas `#f7f7f4`, warm ink `#26251e`, Cursor Orange `#f54e00`을 중심으로 한다.
- primary CTA와 브랜드 워드마크 외에는 오렌지를 남발하지 않는다.
- 카드와 패널은 그림자 없이 1px hairline과 흰 카드 대비로 표현한다.
- 디스플레이 타이포그래피는 400 weight를 기본으로 하며 과도한 bold를 피한다.
- 코드 표면은 `JetBrains Mono` 계열을 사용한다.
- AI timeline pastel 색상은 AI timeline UI 안에서만 사용한다.

## 금지사항

- 인라인 스타일 금지.
- 중복 CSS/JS 작성 금지.
- 새 브랜드 CTA 색상 추가 금지.
- 블루/퍼플 그라데이션을 제품 기본 아이덴티티로 사용 금지.
- 카드/패널에 drop shadow 추가 금지.

## 필수사항

- 공통 레이아웃은 `common/ui/layout.php`, `common/ui/navigation.php`, `admin/common.php`를 우선 사용한다.
- 공통 CSS는 `common/css/common.css`에 둔다.
- 페이지 전용 CSS가 필요하면 파일로 분리하되 `DESIGN.md` 토큰을 따른다.
- 모듈화, 재사용성, 유지보수성을 우선한다.
