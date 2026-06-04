# 공통 회원관리 모듈 기획

## 1. 개요

여러 PHP 프로젝트에서 반복 사용할 수 있는 회원관리 모듈을 기획한다. 회원 데이터는 프로젝트별로 공유하지 않고, 각 프로젝트의 DB 안에서 독립적으로 관리한다. 단, 로그인, 관리자 보호, 접속 로그, 기본 권한 관리 코드는 공통 구조로 재사용할 수 있게 설계한다.

이 모듈은 현재 스마트보드 프로젝트와 무관하게 독립 개발한다. 앞으로 여러 PHP 프로젝트에 회원, 관리자, 게시판, CMS 기능이 필요할 때 이식할 수 있도록 프로젝트 독립형 회원 및 권한 관리 구조로 설계한다.

## 2. 목적

- 프로젝트별 독립 회원 DB를 유지한다.
- 여러 프로젝트에 복사 또는 이식 가능한 공통 PHP 회원관리 모듈을 만든다.
- 관리자 페이지를 로그인으로 보호한다.
- 그누보드처럼 1~10 단계 회원 레벨을 기준으로 접근 권한을 관리한다.
- 일반 페이지, 관리자 페이지, 게시판별 접근 권한과 접속 로그를 관리할 수 있게 한다.
- `oliva1-github`에서 개발 중인 독립형 게시판 모듈을 기반으로 공통 보드 모듈을 통합한다.
- 초기 구현은 가볍게 시작하되, 나중에 플랜, 결제, 사용량 제한으로 확장 가능하게 한다.

## 3. 핵심 원칙

- 회원 공유 없음: 각 프로젝트는 서로 회원 데이터를 공유하지 않는다.
- 코드 재사용: 인증, 세션, 권한 검증, 관리자 가드 로직은 공통 모듈화한다.
- 프로젝트 독립성: 테이블 prefix 또는 설정값으로 프로젝트별 테이블을 분리한다.
- 이식성 우선: 특정 프로젝트의 화면, URL, DB명, 서비스명에 종속되지 않게 설계한다.
- 모듈 연동성: 추후 개발될 보드, 페이지, 통계, 결제, 파일 모듈이 동일한 권한 검증 함수를 사용할 수 있게 한다.
- UI 일관성: 공개 페이지, 회원 페이지, 관리자 페이지, 게시판은 동일한 디자인 토큰, 공통 레이아웃, 공통 컴포넌트 규칙을 따른다.
- DB 저장소 우선: 회원, 권한, 게시판, 게시글, 댓글, 첨부파일, 로그는 JSON 파일이 아니라 DB 테이블에 저장한다.
- 보안 우선: 비밀번호 원문은 저장하지 않고 해시만 저장한다.
- 단계적 확장: 처음부터 그누보드 전체 수준으로 만들지 않고, 필요한 기능부터 MVP로 구현한다.

## 4. 범위

### 포함

- 관리자 로그인
- 관리자 로그아웃
- 관리자 세션 유지
- 회원 로그인
- 회원 로그아웃
- 회원가입
- 마이페이지
- 내 정보 확인 및 수정
- 비밀번호 변경
- `/admin/*` 접근 보호
- 회원 목록 관리
- 회원 상태 관리
- 회원 레벨 관리: 1~10
- 기본 역할 관리: `admin`, `manager`, `user`
- 일반 페이지별 접근 권한 관리
- 게시판별 접근 권한 관리
- `oliva1-github/board` 모듈 통합
- 게시판 DB 스키마 생성
- 게시판 스킨 구조 통합
- 게시판 관리자 화면 통합
- 로그인 로그 저장
- 접속 로그 관리
- 회원별 접속 이력 조회
- IP 해시별 접속 이력 조회
- 프로젝트별 설정값 지원

### 제외

- 프로젝트 간 통합 로그인
- 중앙 인증 서버
- 소셜 로그인
- 결제 및 구독 관리
- 이메일 인증
- 사용자별 화면 대시보드

위 제외 항목은 MVP 이후 필요할 때 확장한다.

## 5. 사용자 유형

### 관리자

- 관리자 페이지에 로그인한다.
- 회원과 권한을 관리한다.
- 접속 로그를 확인한다.
- 문제가 있는 회원을 차단한다.

### 일반 회원

- 각 프로젝트에 독립적으로 등록된다.
- 회원 로그인, 로그아웃, 회원가입, 내 정보 수정, 비밀번호 변경 화면을 사용할 수 있다.

## 6. MVP 기능

1. 관리자 로그인
2. 관리자 세션
3. 관리자 페이지 보호
4. 설치 마법사
5. 최초 관리자 계정 생성
6. 회원 로그인
7. 회원 로그아웃
8. 회원가입
9. 마이페이지
10. 내 정보 수정
11. 비밀번호 변경
12. 회원 테이블 생성
13. 1~10 회원 레벨 적용
14. 일반 페이지 권한 테이블 생성
15. 게시판 권한 테이블 생성
16. 로그인 로그 테이블 생성
17. 접속 로그 테이블 생성
18. 페이지/보드 권한 검증 공통 함수
19. 접속 로그 공통 함수
20. `oliva1-github/board` 기반 게시판 모듈 통합
21. 게시판 DB 스키마 생성
22. 게시판 공통 디자인/레이아웃 통일

## 7. 권장 파일 구조

```text
common/auth/
  config.php
  password.php
  session.php
  guard.php
  user.php
  level.php
  permission.php
  module.php

install/
  index.php
  check.php
  schema.php
  create_admin.php
  finish.php
  style.css
  app.js

member/login/
  index.php
  app.js
  style.css

member/logout/
  index.php

member/register/
  index.php
  app.js
  style.css

member/mypage/
  index.php
  app.js
  style.css

member/profile/
  index.php
  app.js
  style.css

member/password/
  index.php
  app.js
  style.css

admin/login/
  index.php
  app.js
  style.css

admin/users/
  index.php
  list.php
  save.php
  style.css
  app.js

admin/access-logs/
  index.php
  summary.php
  list.php
  style.css
  app.js

common/board/
  bootstrap.php
  config.php
  core.php
  repository.php
  permission.php
  skin.php
  upload.php
  csrf.php

board/
  index.php
  view.php
  write.php
  update.php
  delete.php
  download.php
  editor_upload.php
  style.css
  skins/

admin/boards/
  index.php
  list.php
  write.php
  update.php
  delete.php
  permissions.php
  settings.php
  audit.php
  app.js
  style.css
```

## 7-1. 이식성 기준

회원관리 모듈은 특정 프로젝트 전용 기능이 아니라 다른 PHP 프로젝트에도 옮겨 쓸 수 있는 공통 모듈이어야 한다.

### 이식 시 변경 가능한 값

```text
project_key
table_prefix
login_url
admin_home_url
session_name
cookie_path
default_member_level
admin_level
super_admin_level
```

### 이식 시 유지해야 하는 공통 계약

```text
auth_current_user()
auth_require_login()
auth_require_level($level)
auth_can_page($page_key, $action)
auth_can_board($board_key, $action)
auth_log_access($data)
```

각 프로젝트는 설정값만 바꾸고 위 함수 계약은 동일하게 사용한다.

테이블명은 문서에서 접두사 없이 표기한다. 실제 프로젝트 적용 시 충돌 방지가 필요하면 `table_prefix` 설정으로 `project_users`, `project_boards`처럼 생성한다.

## 7-1-1. 설치 마법사 기준

그누보드 설치 마법사처럼 브라우저에서 초기 설치를 진행할 수 있게 한다. 설치 마법사는 DB 연결 확인, 테이블 생성, 기본 설정 저장, 최초 최고 관리자 생성을 담당한다.

### 설치 단계

```text
1. 환경 점검
2. DB 연결 정보 확인
3. 테이블 prefix 설정
4. 스키마 생성
5. 사이트 기본 정보 입력
6. 최초 관리자 계정 생성
7. 설치 완료 및 잠금 처리
```

### 설치 마법사 보안 조건

```text
install.lock 파일이 있으면 설치 마법사 접근 차단
이미 관리자 계정이 있으면 설치 마법사 접근 차단
설치 완료 후 install 디렉터리 삭제 또는 잠금 권고
관리자 비밀번호는 password_hash()로 저장
DB 비밀번호는 웹에서 노출하지 않음
CSRF 토큰 적용
```

### 설치 결과물

```text
config.local.php 또는 프로젝트별 설정 파일
install.lock
users 테이블
page_permissions 테이블
board_permissions 테이블
boards 관련 테이블
login_logs 테이블
access_logs 테이블
최초 level 10 관리자 계정
```

## 7-2. 향후 모듈 연동 기준

보드, 일반 페이지, 파일 업로드, 관리자 메뉴 등 향후 추가 모듈은 자체 권한 로직을 따로 만들지 않는다. 반드시 공통 auth 모듈의 권한 함수를 호출한다.

### 일반 페이지 모듈

```text
auth_can_page('contact', 'view')
auth_can_page('admin_visits', 'view')
auth_can_page('admin_settings', 'manage')
```

### 보드 모듈

```text
auth_can_board('notice', 'list')
auth_can_board('notice', 'view')
auth_can_board('notice', 'write')
auth_can_board('notice', 'comment')
auth_can_board('notice', 'upload')
auth_can_board('notice', 'manage')
```

### 관리자 메뉴 모듈

```text
auth_require_level(8)
auth_require_level(9)
auth_require_level(10)
```

이 기준을 지키면 신규 모듈이 늘어나도 권한 로직이 흩어지지 않는다.

## 7-2-1. 게시판 모듈 통합 기준

게시판 모듈은 [D:/oliva1-github/board](D:/oliva1-github/board)와 [D:/oliva1-github/admin/board/studio](D:/oliva1-github/admin/board/studio)를 기반으로 가져온다. 단, 독립 공통 모듈 기준에 맞게 다음을 변경한다.

### 유지할 기능

```text
독립 모듈 bootstrap 구조
스킨 레지스트리
default/table/card/gallery/qna/notice/faq/webzine 스킨
목록/보기/쓰기/수정/삭제
댓글
첨부파일 정책
스팸 정책
CSRF
권한 매핑
관리자 보드 스튜디오
감사 로그
```

### 변경할 기능

```text
AUTH_FILE 의존성 제거
require_level() 의존성 제거
ob_studio_* 함수명을 공통 board_* 계열로 정리
JSON 저장소를 DB 저장소로 전환
인라인 style 제거
프로젝트별 layout.php 설정 사용
auth_can_board() 기반 권한 검사로 통합
공통 접속 로그와 감사 로그 연결
```

### 권한 매핑

```text
oliva1 read     -> board_view_level
oliva1 write    -> board_write_level
oliva1 comment  -> board_comment_level
oliva1 admin    -> board_manage_level
추가 list       -> board_list_level
추가 upload     -> board_upload_level
```

## 7-2-2. 디자인과 레이아웃 통일 기준

회원, 관리자, 게시판, CMS 관리 화면은 각각 따로 만든 화면처럼 보이면 안 된다. 모든 화면은 공통 레이아웃과 디자인 토큰을 따른다.

### 공통 원칙

```text
프로젝트별 layout.php 설정 사용
공통 CSS 엔트리 우선 사용
페이지 전용 CSS는 필요한 경우만 분리
인라인 스타일 금지
Noto Sans KR 폰트 유지
최소 폰트 크기 14px 이상
공통 버튼, 카드, 폼, 테이블, 배너, 필터 UI 재사용
공개 페이지와 관리자 페이지의 톤은 구분하되 컴포넌트 규칙은 공유
```

### 관리자 레이아웃

```text
상단 공통 헤더
관리자용 사이드 또는 탭 메뉴
공통 페이지 타이틀 영역
공통 필터 카드
공통 데이터 테이블
공통 액션 버튼
공통 빈 상태/오류 상태
```

### 게시판 레이아웃

```text
게시판 목록/보기/쓰기 화면은 같은 보드 셸을 사용
스킨은 콘텐츠 표현 방식만 바꾼다
헤더, 푸터, 검색, 페이지네이션, 버튼 위치는 통일한다
첨부파일, 댓글, 권한 오류 UI는 공통 컴포넌트를 사용한다
```

## 7-3. 기본 회원 페이지 세트

공통 회원관리 모듈은 백엔드 함수뿐 아니라 프로젝트에 바로 붙일 수 있는 기본 페이지 세트를 포함한다. 화면 스타일은 프로젝트별 layout.php 설정과 공통 CSS 규칙을 따르며, 비즈니스 문구와 디자인은 프로젝트별로 조정 가능해야 한다.

### 필수 페이지

```text
/member/login/       회원 로그인
/member/logout/      회원 로그아웃
/member/register/    회원가입
/member/mypage/      마이페이지
/member/profile/     내 정보 수정
/member/password/    비밀번호 변경
/admin/login/        관리자 로그인
/admin/logout/       관리자 로그아웃
```

### 추후 확장 페이지

```text
/member/find-id/        아이디/이메일 찾기
/member/reset-password/ 비밀번호 재설정
/member/withdraw/       회원 탈퇴
```

초기 MVP에서는 이메일 인증과 비밀번호 재설정 메일 발송은 제외할 수 있지만, URL과 흐름은 확장 가능하게 남긴다.

### 마이페이지 역할

`/member/mypage/`는 회원 로그인 후 진입하는 개인 홈으로 사용한다. 정보 수정 자체는 `/member/profile/`로 분리하고, 마이페이지는 각 회원이 자신의 상태와 사용 가능한 기능을 확인하는 허브 역할을 한다.

```text
회원 이름
회원 레벨
회원 상태
최근 로그인 일시
내 정보 수정 링크
비밀번호 변경 링크
권한 있는 보드/페이지 바로가기
```

## 8. 데이터 구조 후보

### 회원 테이블

```text
users
- id
- email
- password_hash
- name
- company_name
- role
- level
- status
- last_login_at
- created_at
- updated_at
```

### 회원 레벨 정책

```text
level 1 = 신규/제한 회원
level 2 = 일반 회원
level 3 = 인증 회원
level 4 = 우수 회원
level 5 = 파트너/고급 회원
level 6 = 운영 보조
level 7 = 매니저
level 8 = 관리자
level 9 = 최고 관리자
level 10 = 개발/시스템 관리자
```

레벨명은 프로젝트별로 설정 가능하게 하되, 비교 기준은 숫자 레벨을 사용한다.

### 페이지 권한 테이블

```text
page_permissions
- id
- page_key
- page_path
- title
- page_view_level
- page_write_level
- page_manage_level
- allow_guest
- status
- created_at
- updated_at
```

일반 페이지와 관리자 페이지 모두 `page_key` 기준으로 권한을 확인한다.

### 게시판 권한 테이블

```text
board_permissions
- id
- board_key
- board_name
- board_list_level
- board_view_level
- board_write_level
- board_comment_level
- board_upload_level
- board_manage_level
- allow_guest_list
- allow_guest_view
- status
- created_at
- updated_at
```

게시판 기능은 `oliva1-github/board`를 기반으로 통합하되, 저장소는 DB 테이블을 사용한다.

### 게시판 테이블

```text
boards
- id
- board_key
- board_name
- description
- skin
- display_type
- items_per_page
- use_editor
- use_comments
- use_attachments
- status
- created_by
- created_at
- updated_at
```

### 게시글 테이블

```text
board_posts
- id
- board_id
- parent_id
- category
- title
- content
- excerpt
- author_id
- author_name
- is_notice
- is_secret
- is_hidden
- view_count
- comment_count
- attachment_count
- created_at
- updated_at
```

### 댓글 테이블

```text
board_comments
- id
- board_id
- post_id
- parent_id
- author_id
- author_name
- content
- is_hidden
- created_at
- updated_at
```

### 첨부파일 테이블

```text
board_files
- id
- board_id
- post_id
- comment_id
- original_name
- stored_name
- file_path
- file_size
- mime_type
- download_count
- uploaded_by
- created_at
```

### 게시판 감사 로그 테이블

```text
board_audit_logs
- id
- board_id
- post_id
- user_id
- action
- message
- ip_hash
- user_agent
- created_at
```

### 로그인 로그 테이블

```text
login_logs
- id
- user_id
- email
- ip_hash
- user_agent
- result
- created_at
```

### 접속 로그 테이블

```text
access_logs
- id
- user_id
- session_key
- access_type
- target_type
- target_key
- request_path
- method
- ip_hash
- origin
- referer
- user_agent
- result
- status_code
- created_at
```

`access_type`은 접속 성격을 구분한다.

```text
page_view
admin_view
login_success
login_fail
logout
permission_denied
```

`target_type`은 접근 대상의 종류를 구분한다.

```text
page
admin
board
member
system
```

## 9. 관리자 보호 정책

- `/admin/*`는 기본적으로 로그인 필요 영역으로 본다.
- `/admin/login/`은 예외로 둔다.
- 로그인 성공 시 서버 세션을 생성한다.
- 세션 쿠키는 `HttpOnly`, `SameSite=Lax`, HTTPS 환경에서는 `Secure`를 사용한다.
- 관리자 처리 파일도 세션 검증을 통과해야 한다.
- 관리자 기본 접근 레벨은 8 이상으로 둔다.
- 최고 관리자 기능은 9 이상, 시스템 설정은 10만 접근 가능하게 한다.

## 9-1. 권한 판정 정책

권한 판정은 역할보다 레벨을 우선 기준으로 한다.

```text
접근 가능 여부 = 사용자 level >= 리소스 요구 level
```

역할은 UI 표시, 관리자 메뉴 구분, 예외 정책에 사용한다.

```text
admin
manager
user
```

예외적으로 특정 기능이 역할 기반 제어가 필요하면 `role`과 `level`을 함께 검사한다.

```text
사용자 level >= page_manage_level
AND role IN ('admin', 'manager')
```

## 11. 프로젝트 통합 기준

- 이 모듈은 특정 프로젝트의 기존 기능에 직접 결합하지 않는다.
- 프로젝트별 `config.php`, `database.php`, `layout.php`, `routes.php`로 DB, URL, 세션, head/footer, CSS 엔트리를 연결한다.
- 회원 로그인, 로그아웃, 로그인 실패는 `login_logs`와 `access_logs`에 기록한다.
- 관리자 페이지 접근과 권한 거부는 `access_logs`에 기록한다.
- 공개 페이지 중 제한 페이지는 `page_permissions` 기준으로 접근 제어한다.
- 게시판은 `board_permissions` 기준으로 목록, 보기, 쓰기, 댓글, 업로드, 관리 권한을 제어한다.
- `oliva1-github/board` 기반 게시판 모듈은 독립 DB 테이블 기반으로 이식한다.
- 게시판 화면과 관리자 화면은 공통 디자인/레이아웃 규칙을 적용한다.

## 12. 성공 기준

- 설치 마법사로 DB 스키마와 최초 level 10 관리자 계정을 생성할 수 있다.
- 설치 완료 후 `install.lock` 또는 동등한 잠금 장치로 재설치를 차단한다.
- 관리자 로그인 없이는 `/admin/*`에 접근할 수 없다.
- 회원은 회원가입, 로그인, 로그아웃, 마이페이지, 내 정보 수정, 비밀번호 변경을 사용할 수 있다.
- 회원은 1~10 레벨을 가진다.
- 페이지별 보기, 쓰기, 관리 권한 레벨을 설정할 수 있다.
- 게시판별 목록, 보기, 쓰기, 댓글, 업로드, 관리 권한 레벨을 설정할 수 있다.
- 관리자 계정으로 로그인 후 접속 로그를 볼 수 있다.
- 로그인 성공, 로그인 실패, 로그아웃, 관리자 접근, 권한 거부가 접속 로그에 기록된다.
- 관리자는 `/admin/access-logs/`에서 접속 로그를 조회할 수 있다.
- 접속 로그는 회원, IP 해시, 접근 유형, 대상, 기간 기준으로 필터링할 수 있다.
- 게시판 데이터는 JSON 파일이 아니라 DB 테이블에 저장된다.
- 게시판 목록, 보기, 쓰기, 댓글, 첨부파일, 관리자 설정 화면이 공통 디자인 규칙을 따른다.
- 모든 신규 CSS와 JS는 별도 파일로 분리된다.
- 인라인 스타일을 추가하지 않는다.
- 프로젝트별 layout.php 구조를 유지한다.

## 13. 리스크와 대응

### 관리자 계정 초기 생성 문제

- 리스크: 최초 관리자 계정이 없으면 로그인할 수 없다.
- 대응: 설치 마법사에서 최초 level 10 관리자 계정을 생성한다.

### 설치 마법사 노출 문제

- 리스크: 설치 완료 후 설치 마법사가 외부에 노출되면 재설치 또는 설정 노출 위험이 있다.
- 대응: `install.lock` 확인, 관리자 계정 존재 확인, 설치 디렉터리 삭제 권고를 적용한다.

### 프로젝트별 재사용 난이도

- 리스크: 특정 프로젝트에 너무 종속되면 다른 프로젝트 이식이 어렵다.
- 대응: 공통 함수명, 설정값, 테이블 prefix를 분리한다.

### 모듈별 권한 중복

- 리스크: 보드, 페이지, 관리자 화면마다 권한 코드를 따로 만들면 유지보수가 어려워진다.
- 대응: 모든 모듈은 `auth_can_page`, `auth_can_board`, `auth_require_level` 같은 공통 권한 함수를 사용한다.

### 보안 노출

- 리스크: 관리자 화면, 회원 정보, 접속 로그가 외부에 노출될 수 있다.
- 대응: `/admin/*` 세션 가드와 레벨 기반 권한 검증을 필수화한다.

## 14. 단계별 일정

### 1단계: 설계

- 테이블 구조 확정
- 공통 auth 모듈 함수명 확정
- 관리자 보호 범위 확정
- 설치 마법사 단계 확정
- 1~10 레벨 기본 정책 확정
- 페이지/보드 권한 판정 규칙 확정
- 게시판 DB 스키마 확정
- oliva1 게시판 모듈 이식 범위 확정
- 공통 디자인/레이아웃 규칙 확정

### 2단계: 설치 마법사와 관리자 로그인 MVP

- 설치 마법사
- 환경 점검
- 스키마 생성
- 최초 관리자 계정 생성
- 설치 잠금 처리
- 사용자 테이블
- 로그인 화면
- 세션 처리
- `/admin/*` 가드

### 2-1단계: 회원 기본 페이지

- 회원 로그인
- 회원 로그아웃
- 회원가입
- 마이페이지
- 내 정보 수정
- 비밀번호 변경
- 회원 페이지 공통 UI 규칙

### 3단계: 접속 로그 관리

- 접속 로그 테이블
- 공통 접속 로그 함수
- 로그인 성공/실패 기록
- 로그아웃 기록
- 관리자 페이지 접근 기록
- 권한 거부 기록
- 접속 로그 관리자 화면

### 4단계: 관리자 화면 확장

- 회원 관리
- 접속 로그 조회
- 권한 관리

### 4-1단계: 게시판 모듈 통합

- oliva1 게시판 모듈 분석
- JSON 저장소 제거
- DB repository 구현
- 게시판 스키마 생성
- auth_can_board() 권한 연결
- 프로젝트별 layout.php 적용
- 인라인 스타일 제거
- 게시판 스킨 CSS 정리
- 관리자 보드 설정 화면 통합

### 5단계: 재사용 패키지화

- 다른 프로젝트 이식 기준 문서화
- 설정값 분리
- 테이블 prefix 정책 정리
- 공통 함수 계약 문서화
- 보드/페이지 모듈 연동 예제 문서화

## 15. 다음 단계

다음 PDCA 단계는 설계 문서 작성이다.

```text
$pdca design common-auth-module
```

설계 단계에서는 실제 테이블 DDL, 함수명, 관리자 URL 구조, 권한 검증 흐름, 게시판 DB 저장소 전환 방식을 확정한다.
