# SmartCMS Versioning

SmartCMS는 버전을 `MAJOR.MINOR.PATCH` 형식으로 관리한다.

## 기준

- `MAJOR`: 구조 변경이나 호환성에 영향을 주는 변경
- `MINOR`: 기능 추가
- `PATCH`: 버그 수정과 작은 개선

## 단일 원본

- 런타임 버전 원본은 [`common/version.php`](../../common/version.php)이다.
- 필요하면 `config.local.php` 또는 운영 설정에서 `smartcms.version`으로 덮어쓸 수 있다.

## 화면 표시

- 메인 화면 배지
- 관리자 푸터
- 필요 시 다른 공통 화면

## 릴리스 기록

- Git 커밋 메시지
- [`releases`](../../board/?board=releases) 게시판
- Git 태그 `vMAJOR.MINOR.PATCH`

## 변경 절차

1. `common/version.php`의 기본 버전을 올린다.
2. 화면 표시 문구가 그 값을 읽는지 확인한다.
3. Git 태그를 붙인다.
4. `releases` 게시판에 릴리스 노트를 남긴다.
