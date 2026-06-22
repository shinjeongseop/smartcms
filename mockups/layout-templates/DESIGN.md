# SmartCMS Layout Template Rules

이 폴더는 SmartCMS 신규 화면을 빠르게 검토하기 위한 정적 레이아웃 목업 기준이다.

## Common

- HTML5 시멘틱 태그를 우선한다: `header`, `nav`, `main`, `section`, `article`, `aside`, `footer`.
- 레이아웃과 시각 스타일은 Tailwind CSS 유틸리티로 작성한다.
- 인라인 스타일과 HTML 내부 이벤트 핸들러를 사용하지 않는다.
- JavaScript는 메뉴, 탭, 검색 등 목업 확인에 필요한 최소 상호작용만 담당한다.
- 카드 반경은 `rounded-lg` 이하로 유지하고 카드 안에 카드를 중첩하지 않는다.
- 아이콘은 Lucide를 사용하며 아이콘 단독 버튼에는 `title`과 스크린리더 레이블을 제공한다.
- 모바일을 기본으로 설계하고 `sm`, `md`, `lg`, `xl` 순서로 확장한다.

## Public Home

- 순서: 유틸리티 메뉴, 브랜드/검색, 주 메뉴, 핵심 소개, 공지, 본문/사이드바, footer.
- 본문은 데스크톱에서 콘텐츠와 사이드바 2열, 모바일에서 1열로 배치한다.
- 더미 데이터는 공지, 최신글, 게시판 요약, 인기글, 갤러리를 실제 서비스 수준으로 채운다.

## Admin

- 순서: 사이드바, 상단바, 페이지 제목, 통계, 운영 데이터, 관리 테이블, footer.
- 데스크톱 사이드바는 고정 폭, 모바일 사이드바는 오프캔버스로 동작한다.
- 관리자 화면은 장식보다 빠른 탐색, 비교, 반복 작업을 우선한다.
- 테이블은 필요한 최소 폭을 유지하고 모바일에서는 테이블 영역 안에서만 가로 스크롤을 허용한다.

## Tokens

- Primary: `brand-500` (`#03c75a`)
- Primary hover: `brand-600`
- Canvas: `slate-50` 또는 `slate-100`
- Surface: `white`
- Border: `slate-200`
- Text: `slate-950`, `slate-700`, `slate-500`
- Danger: `rose-700`
- Warning: `amber-700`
