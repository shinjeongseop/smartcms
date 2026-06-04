# smartcms-core Do Guide

> **Feature**: smartcms-core
> **Phase**: Do
> **Date**: 2026-06-05
> **Plan**: [smartcms-core.plan.md](../../01-plan/features/smartcms-core.plan.md)
> **Design**: [smartcms-core.design.md](./smartcms-core.design.md)

---

## 1. 목표

`smartcms-core`의 첫 구현은 전체 CMS를 한 번에 완성하는 것이 아니라, 이후 게시판과 관리자 기능을 안전하게 붙일 수 있는 독립 코어를 세우는 것이다.

1차 구현 목표는 다음과 같다.

- 설치 마법사
- DB 연결과 테이블 생성
- 최초 level 10 관리자 생성
- 로그인/로그아웃/세션
- 관리자 접근 보호
- 회원 기본 페이지 골격
- 접속 로그 기반
- 페이지/게시판 권한 함수 기반

---

## 2. 구현 원칙

- 스마트보드 프로젝트와 결합하지 않는다.
- `D:\smartcms` 독립 프로젝트 안에서만 작업한다.
- API 키/외부 API 제공 기능은 구현하지 않는다.
- 테이블명은 기본적으로 접두사 없이 사용한다.
- 프로젝트 충돌 대응은 `table_prefix` 설정으로 처리한다.
- 인라인 스타일은 작성하지 않는다.
- 모든 CSS/JS는 파일로 분리한다.
- 화면은 `layout.php` 설정을 통해 head/footer를 렌더링한다.
- DB 저장소는 JSON 파일이 아니라 MySQL/MariaDB 테이블을 기준으로 한다.

---

## 3. 구현 순서

### 3.1 Bootstrap and Configuration

생성 대상:

```text
config.example.php
common/config.php
common/database.php
common/routes.php
common/ui/layout.php
common/ui/components.php
```

작업 내용:

- `config.example.php`에 기본 설정 구조를 정의한다.
- `config.local.php`가 있으면 우선 로드한다.
- `table_name($name)` 또는 모듈별 래퍼 함수로 `table_prefix`를 적용한다.
- DB 연결은 PDO 또는 mysqli 중 하나로 통일한다.
- `layout.php`는 프로젝트별 head/footer 설정을 담당한다.

완료 기준:

- 설정 파일 로드 가능
- DB 연결 함수 호출 가능
- table prefix 변환 가능
- 기본 head/footer 렌더링 가능

### 3.2 Install Wizard

생성 대상:

```text
install/index.php
install/check.php
install/schema.php
install/create_admin.php
install/finish.php
install/style.css
install/app.js
```

작업 내용:

- `install.lock`이 있으면 설치 페이지 접근을 차단한다.
- 이미 level 10 관리자 계정이 있으면 설치 접근을 차단한다.
- PHP 버전, 필수 확장, 쓰기 권한을 확인한다.
- DB 연결 정보를 입력받고 연결을 검증한다.
- `table_prefix`를 입력받는다.
- 전체 스키마를 생성한다.
- 최초 level 10 관리자 계정을 생성한다.
- 설치 완료 후 `install.lock`을 생성한다.

완료 기준:

- 빈 DB에서 설치 완료 가능
- 관리자 계정 생성 가능
- 재설치 차단 가능
- 설치 과정에서 비밀번호 원문 저장 없음

### 3.3 Schema

생성 대상:

```text
common/schema.php
```

필수 테이블:

```text
users
page_permissions
board_permissions
boards
board_posts
board_comments
board_files
board_audit_logs
login_logs
access_logs
```

작업 내용:

- 설계 문서의 DDL을 기준으로 생성 함수를 작성한다.
- 각 테이블 생성 함수는 중복 실행 가능해야 한다.
- `table_prefix`를 모든 테이블 생성에 반영한다.

완료 기준:

- 설치 마법사에서 전체 스키마 생성 가능
- 같은 설치 과정을 재실행해도 테이블 생성 오류 없음

### 3.4 Auth Core

생성 대상:

```text
common/auth/password.php
common/auth/session.php
common/auth/user.php
common/auth/guard.php
common/auth/level.php
common/auth/permission.php
common/auth/module.php
```

작업 내용:

- `auth_current_user()`
- `auth_is_logged_in()`
- `auth_login()`
- `auth_logout()`
- `auth_register()`
- `auth_require_login()`
- `auth_require_level()`
- `auth_require_admin()`
- `auth_can_page()`
- `auth_can_board()`

완료 기준:

- 로그인 성공 시 세션 생성
- 로그인 실패 시 로그 기록
- 로그아웃 시 세션 제거
- level 기준 관리자 접근 제어 가능
- 권한 부족 시 403 처리와 접속 로그 기록

### 3.5 Logging

생성 대상:

```text
common/log/login.php
common/log/access.php
admin/access-logs/index.php
admin/access-logs/list.php
admin/access-logs/summary.php
admin/access-logs/style.css
admin/access-logs/app.js
```

작업 내용:

- 로그인 성공/실패 기록
- 로그아웃 기록
- 관리자 접근 기록
- 권한 거부 기록
- 접속 로그 목록 조회
- 기간, 회원, IP 해시, 접근 유형 필터

완료 기준:

- 로그인 성공/실패가 `login_logs`에 저장됨
- 권한 거부가 `access_logs`에 저장됨
- 관리자에서 접속 로그 조회 가능

### 3.6 Member Pages

생성 대상:

```text
member/login/index.php
member/login/style.css
member/login/app.js
member/logout/index.php
member/register/index.php
member/register/style.css
member/register/app.js
member/mypage/index.php
member/mypage/style.css
member/mypage/app.js
member/profile/index.php
member/profile/style.css
member/profile/app.js
member/password/index.php
member/password/style.css
member/password/app.js
```

작업 내용:

- 회원 로그인 화면
- 회원가입 화면
- 마이페이지
- 내 정보 수정
- 비밀번호 변경
- 로그아웃

완료 기준:

- 회원가입 후 로그인 가능
- 마이페이지 접근 가능
- 프로필 수정 가능
- 비밀번호 변경 가능

### 3.7 Admin Pages

생성 대상:

```text
admin/login/index.php
admin/login/style.css
admin/login/app.js
admin/logout/index.php
admin/users/index.php
admin/users/list.php
admin/users/save.php
admin/users/style.css
admin/users/app.js
admin/permissions/index.php
admin/permissions/save.php
```

작업 내용:

- 관리자 로그인
- 관리자 로그아웃
- 회원 목록
- 회원 상태 변경
- 회원 레벨 변경
- 페이지 권한 관리
- 게시판 권한 관리

완료 기준:

- level 8 이상만 관리자 접근 가능
- level 9 이상만 권한 관리 가능
- 회원 차단 가능
- 권한 변경 가능

### 3.8 Board Module Integration

기반 경로:

```text
D:/oliva1-github/board
D:/oliva1-github/admin/board/studio
```

생성 대상:

```text
common/board/bootstrap.php
common/board/config.php
common/board/core.php
common/board/repository.php
common/board/permission.php
common/board/skin.php
common/board/upload.php
common/board/csrf.php
board/index.php
board/view.php
board/write.php
board/update.php
board/delete.php
board/download.php
board/editor_upload.php
board/style.css
board/skins/
admin/boards/
```

변경 기준:

- `ob_studio_*` 함수명은 `board_*` 계열로 변경한다.
- JSON 저장소는 제거한다.
- `repository.php`가 DB CRUD를 담당한다.
- `AUTH_FILE` 의존성은 제거한다.
- `require_level()`은 `auth_require_level()`로 교체한다.
- `ob_studio_can()`은 `auth_can_board()`로 교체한다.
- 인라인 스타일은 CSS 클래스로 이동한다.

완료 기준:

- DB 기반 게시판 생성 가능
- 게시글 목록/보기/쓰기 가능
- 댓글 작성 가능
- 첨부파일 업로드/다운로드 가능
- 게시판 권한 적용 가능
- 관리자 보드 설정 가능

---

## 4. 검증 체크리스트

### 설치

- [ ] `install.lock` 없을 때 설치 진입 가능
- [ ] DB 연결 실패 시 다음 단계 차단
- [ ] 테이블 생성 성공
- [ ] 최초 level 10 관리자 생성
- [ ] 설치 완료 후 `install.lock` 생성
- [ ] `install.lock` 존재 시 설치 차단

### 인증

- [ ] 로그인 성공
- [ ] 로그인 실패
- [ ] blocked 회원 로그인 차단
- [ ] 로그아웃
- [ ] 관리자 가드
- [ ] 세션 재발급

### 권한

- [ ] level 1~10 권한 비교
- [ ] page view/write/manage 권한
- [ ] board list/view/write/comment/upload/manage 권한
- [ ] 권한 부족 시 403
- [ ] 권한 거부 로그 기록

### 회원

- [ ] 회원가입
- [ ] 마이페이지
- [ ] 프로필 수정
- [ ] 비밀번호 변경

### 관리자

- [ ] 회원 목록
- [ ] 회원 상태 변경
- [ ] 회원 레벨 변경
- [ ] 접속 로그 조회
- [ ] 권한 관리

### 게시판

- [ ] 게시판 생성
- [ ] 권한 설정
- [ ] 목록
- [ ] 보기
- [ ] 쓰기
- [ ] 수정
- [ ] 삭제
- [ ] 댓글
- [ ] 첨부파일
- [ ] 스킨 적용

### UI

- [ ] 인라인 스타일 없음
- [ ] CSS/JS 파일 분리
- [ ] 최소 폰트 크기 14px 이상
- [ ] 공통 layout.php 사용
- [ ] 관리자/회원/게시판 레이아웃 일관성

---

## 5. 1차 구현 범위

전체 기능을 한 번에 구현하지 않고 다음 순서로 나눈다.

### Sprint 1

- 설정 로드
- DB 연결
- 설치 마법사
- 스키마 생성
- 최초 관리자 생성

### Sprint 2

- 로그인/로그아웃
- 세션
- 관리자 가드
- 로그인 로그
- 접속 로그

### Sprint 3

- 회원가입
- 마이페이지
- 프로필 수정
- 비밀번호 변경
- 회원 관리

### Sprint 4

- 페이지/게시판 권한
- 권한 관리 화면

### Sprint 5

- oliva1 게시판 DB 이식
- 게시판 관리자 화면
- 스킨 정리

---

## 6. 다음 작업

다음 실제 구현 시작점은 Sprint 1이다.

```text
1. config.example.php
2. common/config.php
3. common/database.php
4. common/schema.php
5. install/index.php
```

Sprint 1 완료 후 설치 마법사로 DB 생성과 최초 관리자 생성까지 검증한다.
