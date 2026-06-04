# common-auth-module Design Document

> **Summary**: 여러 PHP 프로젝트에 독립 이식 가능한 회원/권한/게시판/CMS 코어 설계
>
> **Project**: Independent PHP Auth Builder Core
> **Version**: 0.1
> **Author**: Codex
> **Date**: 2026-06-04
> **Status**: Draft
> **Planning Doc**: [common-auth-module.plan.md](../../01-plan/features/common-auth-module.plan.md)

---

## 1. Overview

### 1.1 Design Goals

- 특정 프로젝트에 종속되지 않는 독립형 PHP 회원/권한 모듈을 설계한다.
- 그누보드식 1~10 레벨 권한 모델을 페이지, 관리자, 게시판에 일관되게 적용한다.
- 설치 마법사로 DB 스키마 생성과 최초 level 10 관리자 계정 생성을 처리한다.
- `oliva1-github/board` 게시판 모듈을 기반으로 하되 JSON 저장소를 DB 저장소로 전환한다.
- 모든 화면은 공통 layout 설정과 공통 CSS 엔트리를 통해 디자인 일관성을 유지한다.
- API 키/외부 API 제공 기능은 현재 설계 범위에서 제외한다.

### 1.2 Design Principles

- **독립성**: 모듈은 특정 프로젝트의 DB명, URL, 헤더, 푸터, CSS 파일명에 직접 의존하지 않는다.
- **통합 설정 우선**: 프로젝트 연결은 `config.local.php`, `database.php`, `layout.php`, `routes.php` 같은 명확한 설정 파일을 통해 처리한다.
- **레벨 중심 권한**: 기본 권한 판단은 `user.level >= required_level`로 통일한다.
- **역할은 보조 정보**: `role`은 UI, 관리자 메뉴, 예외 정책에 사용하고 기본 접근 판단은 레벨로 한다.
- **DB 저장소**: 회원, 권한, 게시판, 게시글, 댓글, 첨부파일, 로그는 DB에 저장한다.
- **인라인 스타일 금지**: 모든 스타일은 공통 CSS 또는 페이지/스킨 CSS로 분리한다.
- **보안 기본값**: 비밀번호 해시, CSRF, 세션 쿠키 보안, 설치 잠금, 권한 실패 로그를 기본으로 둔다.

---

## 2. Architecture

### 2.1 Component Diagram

```text
Browser
  |
  v
Project Integration Layer
  - config.local.php
  - database.php
  - layout.php
  - routes.php
  |
  v
Common Core
  - common/auth
  - common/board
  - common/ui
  - common/log
  |
  v
Feature Pages
  - install
  - member
  - admin
  - board
  |
  v
Database
  - users
  - page_permissions
  - board_permissions
  - boards
  - board_posts
  - board_comments
  - board_files
  - board_audit_logs
  - login_logs
  - access_logs
```

### 2.2 Module Responsibilities

| Module | Responsibility |
|---|---|
| `install/` | 환경 점검, DB 연결, 스키마 생성, 최초 관리자 생성, 설치 잠금 |
| `common/auth/` | 세션, 로그인, 로그아웃, 비밀번호, 현재 사용자, 관리자 가드 |
| `common/auth/permission.php` | 페이지/게시판/레벨 권한 판정 |
| `common/log/` | 접속 로그, 로그인 로그, 권한 거부 로그 기록 |
| `common/board/` | 게시판 도메인, DB repository, 권한 연결, 스킨, 첨부파일, CSRF |
| `common/ui/` | head/footer 렌더링 설정 호출, 공통 컴포넌트 헬퍼 |
| `member/` | 회원 로그인, 로그아웃, 회원가입, 마이페이지, 프로필, 비밀번호 변경 |
| `admin/` | 관리자 로그인, 회원 관리, 권한 관리, 접속 로그, 게시판 관리 |
| `board/` | 공개 게시판 목록, 보기, 쓰기, 수정, 삭제, 다운로드 |

### 2.3 Data Flow

#### Login Flow

```text
Login Form
  -> validate email/password
  -> find users by email
  -> password_verify()
  -> check status
  -> session_regenerate_id()
  -> store auth session
  -> write login_logs
  -> write access_logs(login_success/login_fail)
  -> redirect
```

#### Permission Flow

```text
Request
  -> auth_current_user()
  -> resolve target permission
  -> compare user level
  -> allow or deny
  -> if deny, write access_logs(permission_denied)
```

#### Board Read Flow

```text
/board/?board=notice
  -> load board by board_key
  -> auth_can_board('notice', 'list')
  -> load posts from board_posts
  -> load skin registry
  -> render list with project layout settings
```

#### Install Flow

```text
/install/
  -> block if install.lock exists
  -> block if users has any level 10 admin
  -> environment check
  -> DB connection check
  -> table_prefix input
  -> create schema
  -> create first level 10 admin
  -> write config.local.php
  -> write install.lock
```

### 2.4 Dependencies

| Component | Depends On | Purpose |
|---|---|---|
| `install/schema.php` | database.php | 테이블 생성 |
| `common/auth/session.php` | PHP Session | 로그인 세션 유지 |
| `common/auth/password.php` | PHP `password_hash`, `password_verify` | 비밀번호 보안 |
| `common/auth/permission.php` | `users`, `page_permissions`, `board_permissions` | 권한 판정 |
| `common/board/repository.php` | database.php | 게시판 DB CRUD |
| `common/board/skin.php` | filesystem | 스킨 레지스트리 |
| `common/log/access.php` | database.php | 접속 로그 기록 |
| `member/*`, `admin/*`, `board/*` | common core + layout.php | 화면 렌더링 |

---

## 3. File Structure

```text
config.example.php
config.local.php
install.lock

common/
  auth/
    config.php
    password.php
    session.php
    guard.php
    user.php
    level.php
    permission.php
    module.php
  board/
    bootstrap.php
    config.php
    core.php
    repository.php
    permission.php
    skin.php
    upload.php
    csrf.php
  log/
    access.php
    login.php
  ui/
    layout.php
    routes.php
    components.php

install/
  index.php
  check.php
  schema.php
  create_admin.php
  finish.php
  style.css
  app.js

member/
  login/
  logout/
  register/
  mypage/
  profile/
  password/

admin/
  login/
  logout/
  users/
  permissions/
  access-logs/
  boards/

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
    default/
    table/
    card/
    gallery/
    qna/
    notice/
    faq/
    webzine/
```

---

## 4. Configuration Design

### 4.1 `config.example.php`

```php
<?php
return [
    'project_key' => 'default',
    'base_url' => '',
    'table_prefix' => '',
    'session_name' => 'auth_builder_session',
    'cookie_path' => '/',
    'default_member_level' => 2,
    'admin_level' => 8,
    'super_admin_level' => 10,
    'login_url' => '/member/login/',
    'admin_login_url' => '/admin/login/',
    'admin_home_url' => '/admin/users/',
    'db' => [
        'host' => 'localhost',
        'name' => '',
        'user' => '',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'theme' => [
        'head_file' => null,
        'foot_file' => null,
        'css_url' => '/common/css/common.css',
    ],
];
```

### 4.2 Table Prefix Rule

문서와 기본 DDL은 접두사 없는 이름을 사용한다.

```text
users
boards
board_posts
```

프로젝트에 충돌 위험이 있으면 설치 마법사에서 `table_prefix`를 받아 실제 생성명을 바꾼다.

```text
table_prefix = "cms_"
cms_users
cms_boards
cms_board_posts
```

코드에서는 항상 `auth_table('users')`, `board_table('boards')` 같은 헬퍼를 사용한다.

---

## 5. Data Model

### 5.1 Core Tables

#### `users`

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(80) NOT NULL,
    company_name VARCHAR(120) DEFAULT NULL,
    role ENUM('admin','manager','user') NOT NULL DEFAULT 'user',
    level TINYINT UNSIGNED NOT NULL DEFAULT 2,
    status ENUM('active','pending','blocked','left') NOT NULL DEFAULT 'active',
    last_login_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    INDEX idx_users_role_level (role, level),
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### `page_permissions`

```sql
CREATE TABLE page_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_key VARCHAR(80) NOT NULL,
    page_path VARCHAR(255) NOT NULL,
    title VARCHAR(120) NOT NULL,
    page_view_level TINYINT UNSIGNED NOT NULL DEFAULT 0,
    page_write_level TINYINT UNSIGNED NOT NULL DEFAULT 8,
    page_manage_level TINYINT UNSIGNED NOT NULL DEFAULT 8,
    allow_guest TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('active','disabled') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_page_permissions_key (page_key),
    INDEX idx_page_permissions_path (page_path),
    INDEX idx_page_permissions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### `board_permissions`

```sql
CREATE TABLE board_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    board_key VARCHAR(80) NOT NULL,
    board_name VARCHAR(120) NOT NULL,
    board_list_level TINYINT UNSIGNED NOT NULL DEFAULT 0,
    board_view_level TINYINT UNSIGNED NOT NULL DEFAULT 0,
    board_write_level TINYINT UNSIGNED NOT NULL DEFAULT 8,
    board_comment_level TINYINT UNSIGNED NOT NULL DEFAULT 2,
    board_upload_level TINYINT UNSIGNED NOT NULL DEFAULT 8,
    board_manage_level TINYINT UNSIGNED NOT NULL DEFAULT 8,
    allow_guest_list TINYINT(1) NOT NULL DEFAULT 1,
    allow_guest_view TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('active','disabled') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_board_permissions_key (board_key),
    INDEX idx_board_permissions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.2 Board Tables

#### `boards`

```sql
CREATE TABLE boards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    board_key VARCHAR(80) NOT NULL,
    board_name VARCHAR(120) NOT NULL,
    description TEXT DEFAULT NULL,
    skin VARCHAR(40) NOT NULL DEFAULT 'default',
    display_type ENUM('auto','card','list','table') NOT NULL DEFAULT 'auto',
    items_per_page TINYINT UNSIGNED NOT NULL DEFAULT 10,
    use_editor TINYINT(1) NOT NULL DEFAULT 1,
    use_comments TINYINT(1) NOT NULL DEFAULT 1,
    use_attachments TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('active','hidden','disabled') NOT NULL DEFAULT 'active',
    created_by BIGINT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_boards_key (board_key),
    INDEX idx_boards_status (status),
    INDEX idx_boards_created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### `board_posts`

```sql
CREATE TABLE board_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    board_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED DEFAULT NULL,
    category VARCHAR(80) DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    content MEDIUMTEXT NOT NULL,
    excerpt VARCHAR(500) DEFAULT NULL,
    author_id BIGINT UNSIGNED DEFAULT NULL,
    author_name VARCHAR(80) NOT NULL,
    is_notice TINYINT(1) NOT NULL DEFAULT 0,
    is_secret TINYINT(1) NOT NULL DEFAULT 0,
    is_hidden TINYINT(1) NOT NULL DEFAULT 0,
    view_count INT UNSIGNED NOT NULL DEFAULT 0,
    comment_count INT UNSIGNED NOT NULL DEFAULT 0,
    attachment_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_board_posts_board_created (board_id, created_at),
    INDEX idx_board_posts_board_notice (board_id, is_notice, created_at),
    INDEX idx_board_posts_author (author_id),
    FULLTEXT KEY ft_board_posts_title_content (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### `board_comments`

```sql
CREATE TABLE board_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    board_id BIGINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED DEFAULT NULL,
    author_id BIGINT UNSIGNED DEFAULT NULL,
    author_name VARCHAR(80) NOT NULL,
    content TEXT NOT NULL,
    is_hidden TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_board_comments_post_created (post_id, created_at),
    INDEX idx_board_comments_parent (parent_id),
    INDEX idx_board_comments_author (author_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### `board_files`

```sql
CREATE TABLE board_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    board_id BIGINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED DEFAULT NULL,
    comment_id BIGINT UNSIGNED DEFAULT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
    mime_type VARCHAR(120) DEFAULT NULL,
    download_count INT UNSIGNED NOT NULL DEFAULT 0,
    uploaded_by BIGINT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_board_files_post (post_id),
    INDEX idx_board_files_comment (comment_id),
    INDEX idx_board_files_uploaded_by (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### `board_audit_logs`

```sql
CREATE TABLE board_audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    board_id BIGINT UNSIGNED DEFAULT NULL,
    post_id BIGINT UNSIGNED DEFAULT NULL,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    action VARCHAR(80) NOT NULL,
    message VARCHAR(500) NOT NULL,
    ip_hash CHAR(64) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_board_audit_board_created (board_id, created_at),
    INDEX idx_board_audit_user_created (user_id, created_at),
    INDEX idx_board_audit_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.3 Log Tables

#### `login_logs`

```sql
CREATE TABLE login_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    email VARCHAR(190) NOT NULL,
    ip_hash CHAR(64) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    result ENUM('success','fail','blocked') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_logs_user_created (user_id, created_at),
    INDEX idx_login_logs_email_created (email, created_at),
    INDEX idx_login_logs_result_created (result, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### `access_logs`

```sql
CREATE TABLE access_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    session_key VARCHAR(128) DEFAULT NULL,
    access_type ENUM('page_view','admin_view','login_success','login_fail','logout','permission_denied') NOT NULL,
    target_type ENUM('page','admin','board','member','system') NOT NULL DEFAULT 'page',
    target_key VARCHAR(120) DEFAULT NULL,
    request_path VARCHAR(255) NOT NULL,
    method VARCHAR(10) NOT NULL DEFAULT 'GET',
    ip_hash CHAR(64) DEFAULT NULL,
    origin VARCHAR(255) DEFAULT NULL,
    referer VARCHAR(500) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    result ENUM('success','fail','denied') NOT NULL DEFAULT 'success',
    status_code SMALLINT UNSIGNED NOT NULL DEFAULT 200,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_access_logs_user_created (user_id, created_at),
    INDEX idx_access_logs_type_created (access_type, created_at),
    INDEX idx_access_logs_target_created (target_type, target_key, created_at),
    INDEX idx_access_logs_ip_created (ip_hash, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.4 Relationships

```text
users 1 ---- N board_posts
users 1 ---- N board_comments
users 1 ---- N board_files
users 1 ---- N login_logs
users 1 ---- N access_logs
users 1 ---- N board_audit_logs

boards 1 ---- 1 board_permissions by board_key
boards 1 ---- N board_posts
boards 1 ---- N board_comments
boards 1 ---- N board_files
boards 1 ---- N board_audit_logs

board_posts 1 ---- N board_comments
board_posts 1 ---- N board_files
```

---

## 6. Function Contracts

### 6.1 Auth

```php
auth_current_user(): array
auth_is_logged_in(): bool
auth_login(string $email, string $password): array
auth_logout(): void
auth_register(array $input): array
auth_update_profile(int $user_id, array $input): bool
auth_change_password(int $user_id, string $current, string $next): bool
```

### 6.2 Guard

```php
auth_require_login(): void
auth_require_level(int $level): void
auth_require_admin(): void
auth_redirect_after_login(array $user): string
```

### 6.3 Permission

```php
auth_can_page(string $page_key, string $action = 'view', ?array $user = null): bool
auth_can_board(string $board_key, string $action = 'view', ?array $user = null): bool
auth_required_page_level(string $page_key, string $action): int
auth_required_board_level(string $board_key, string $action): int
```

권한 action 매핑:

```text
page: view, write, manage
board: list, view, write, comment, upload, manage
```

### 6.4 Logging

```php
auth_log_login(?int $user_id, string $email, string $result): void
auth_log_access(array $data): void
auth_log_permission_denied(string $target_type, string $target_key): void
```

### 6.5 Board

```php
board_find(string $board_key): ?array
board_list_posts(string $board_key, array $filters): array
board_find_post(string $board_key, int $post_id): ?array
board_create_post(string $board_key, array $input, array $user): int
board_update_post(string $board_key, int $post_id, array $input, array $user): bool
board_delete_post(string $board_key, int $post_id, array $user): bool
board_create_comment(string $board_key, int $post_id, array $input, array $user): int
board_upload_file(string $board_key, array $file, array $user): array
board_log_audit(array $data): void
```

---

## 7. Page Design

### 7.1 Install

| Path | Purpose | Access |
|---|---|---|
| `/install/` | 설치 시작/환경 점검 | `install.lock` 없을 때 |
| `/install/check.php` | 환경, PHP 확장, 쓰기 권한 확인 | install only |
| `/install/schema.php` | DB 연결 확인 및 스키마 생성 | install only |
| `/install/create_admin.php` | 최초 level 10 관리자 생성 | install only |
| `/install/finish.php` | 설치 잠금 생성 | install only |

설치 완료 조건:

```text
config.local.php exists
install.lock exists
users table exists
level 10 admin exists
```

### 7.2 Member

| Path | Purpose | Access |
|---|---|---|
| `/member/login/` | 회원 로그인 | guest |
| `/member/logout/` | 로그아웃 | logged-in |
| `/member/register/` | 회원가입 | guest |
| `/member/mypage/` | 회원 홈 | logged-in |
| `/member/profile/` | 내 정보 수정 | logged-in |
| `/member/password/` | 비밀번호 변경 | logged-in |

### 7.3 Admin

| Path | Purpose | Required Level |
|---|---|---|
| `/admin/login/` | 관리자 로그인 | guest |
| `/admin/logout/` | 관리자 로그아웃 | 8 |
| `/admin/users/` | 회원 목록/관리 | 8 |
| `/admin/permissions/` | 페이지/보드 권한 관리 | 9 |
| `/admin/access-logs/` | 접속 로그 조회 | 8 |
| `/admin/boards/` | 게시판 관리 | 8 |
| `/admin/boards/permissions.php` | 게시판 권한 설정 | 8 |
| `/admin/boards/settings.php` | 게시판 설정 | 8 |
| `/admin/boards/audit.php` | 게시판 감사 로그 | 8 |

### 7.4 Board

| Path | Purpose | Permission |
|---|---|---|
| `/board/` | 게시판 목록 | `list` |
| `/board/view.php` | 게시글 보기 | `view` |
| `/board/write.php` | 글쓰기 폼 | `write` |
| `/board/update.php` | 글 저장/수정 | `write` |
| `/board/delete.php` | 글 삭제 | owner or `manage` |
| `/board/download.php` | 첨부 다운로드 | `view` |
| `/board/editor_upload.php` | 에디터 이미지 업로드 | `upload` |

---

## 8. UI/UX Design

### 8.1 Layout Contract

프로젝트별 head/footer는 직접 파일명을 고정하지 않는다.

```php
ui_render_head([
    'title' => '...',
    'body_class' => '...',
    'stylesheets' => [],
]);

ui_render_foot([
    'scripts' => [],
]);
```

Layout 기본 동작:

```text
1. 프로젝트 layout.php가 있으면 사용
2. 없으면 모듈 기본 head/footer 사용
3. CSS 엔트리는 config theme.css_url을 우선 사용
```

### 8.2 Common Components

| Component | Use |
|---|---|
| `ui_card` | 로그인 박스, 필터 박스, 게시판 설정 패널 |
| `ui_button` | 저장, 삭제, 이동, 검색 |
| `ui_form_row` | 회원/게시판 설정 입력 |
| `ui_table` | 회원 목록, 접속 로그, 게시글 목록 |
| `ui_badge` | 레벨, 상태, 권한 |
| `ui_empty_state` | 데이터 없음 |
| `ui_alert` | 성공/오류 메시지 |
| `ui_pagination` | 게시판/로그 페이지네이션 |

### 8.3 Design Rules

- 모든 폰트 크기는 14px 이상.
- 인라인 스타일 금지.
- 스킨 CSS는 게시판 콘텐츠 표현만 담당.
- 관리자 화면은 같은 페이지 타이틀, 필터 카드, 테이블 레이아웃을 사용.
- 회원 화면은 같은 인증 카드 레이아웃을 사용.
- 권한 오류 화면은 모든 모듈에서 동일 컴포넌트를 사용.

---

## 9. Board Integration Design

### 9.1 Source Module

기반 모듈:

```text
D:/oliva1-github/board
D:/oliva1-github/admin/board/studio
```

### 9.2 Keep

- 스킨 레지스트리 개념
- `default`, `table`, `card`, `gallery`, `qna`, `notice`, `faq`, `webzine` 스킨
- 목록/보기/쓰기/수정/삭제
- 댓글
- 첨부파일
- 스팸 정책
- CSRF
- 감사 로그

### 9.3 Replace

| oliva1 Current | New Design |
|---|---|
| `ob_studio_*` | `board_*` |
| JSON store | DB repository |
| `AUTH_FILE` | `common/auth/bootstrap.php` |
| `require_level()` | `auth_require_level()` |
| `ob_studio_can()` | `auth_can_board()` |
| inline style | CSS class |
| project-specific head/footer | `ui_render_head()`, `ui_render_foot()` |

### 9.4 Permission Mapping

| oliva1 Permission | New Permission |
|---|---|
| `read` | `board_view_level` |
| `write` | `board_write_level` |
| `comment` | `board_comment_level` |
| `admin` | `board_manage_level` |
| new | `board_list_level` |
| new | `board_upload_level` |

---

## 10. Security Considerations

- 비밀번호는 `password_hash(PASSWORD_DEFAULT)`로 저장한다.
- 로그인 성공 시 `session_regenerate_id(true)`를 호출한다.
- 세션 쿠키는 `HttpOnly`, `SameSite=Lax`, HTTPS에서는 `Secure`를 사용한다.
- 설치 완료 후 `install.lock`이 없으면 관리자 화면 접근 전에 경고한다.
- 설치 마법사는 `install.lock` 또는 level 10 관리자 존재 시 차단한다.
- 모든 POST 요청은 CSRF 토큰을 확인한다.
- 모든 출력은 HTML escape를 기본으로 한다.
- DB 입력은 prepared statement 또는 프로젝트 database.php의 DB 정책을 따른다.
- 파일 업로드는 확장자, MIME, 크기, 저장 경로를 검증한다.
- 권한 거부는 `access_logs`에 기록한다.
- IP는 원문 저장을 피하고 HMAC hash로 저장한다.

---

## 11. Error Handling

| Case | Handling |
|---|---|
| 설치 잠금 존재 | `/install/` 접근 차단 |
| DB 연결 실패 | 설치 화면에서 오류 표시, 다음 단계 차단 |
| 로그인 실패 | `login_logs` 기록 후 일반 오류 메시지 |
| blocked 회원 로그인 | `blocked` 로그 기록 후 차단 메시지 |
| 권한 부족 | 403 화면 + `access_logs(permission_denied)` |
| 게시판 없음 | 404 화면 |
| 게시글 없음 | 404 화면 |
| CSRF 실패 | 400 화면 |
| 파일 업로드 실패 | 폼 오류 메시지 |

---

## 12. Test Plan

| Type | Target | Method |
|---|---|---|
| Install Test | 설치 마법사 | DB 없는 상태에서 설치 완료 확인 |
| Install Lock Test | 재설치 차단 | `install.lock` 생성 후 접근 차단 확인 |
| Auth Test | 로그인/로그아웃 | 성공/실패/blocked 케이스 확인 |
| Permission Test | 레벨 권한 | level 1~10별 page/board 접근 결과 확인 |
| Member Test | 회원가입/마이페이지 | 입력 검증, 프로필 수정, 비밀번호 변경 |
| Admin Test | 회원 관리 | level 8/9/10 권한 차이 확인 |
| Board Test | 목록/보기/쓰기/댓글/첨부 | DB 저장과 권한 확인 |
| Migration Test | oliva1 board 이식 | JSON 의존 제거 여부 확인 |
| UI Test | 디자인 일관성 | 공통 head/footer, CSS 분리, 인라인 스타일 없음 |
| Security Test | CSRF/session/upload | 실패 케이스 차단 확인 |

---

## 13. Implementation Guide

### 13.1 Implementation Order

1. `config.example.php`, database.php, table name helper 작성
2. 설치 마법사와 schema 생성 구현
3. `users`, `login_logs`, `access_logs` 스키마 구현
4. auth session/login/logout/password 구현
5. 관리자 로그인과 `/admin/*` 가드 구현
6. 회원 기본 페이지 구현
7. page/board permission 스키마와 함수 구현
8. 접속 로그 관리자 화면 구현
9. board 스키마 구현
10. oliva1 board repository를 DB 기반으로 이식
11. board 스킨 구조 이식
12. admin boards 화면 이식
13. 공통 UI 컴포넌트와 CSS 정리
14. 테스트 및 분석

### 13.2 Implementation Boundaries

- 외부 API 키 관리 기능은 구현하지 않는다.
- 중앙 인증 서버는 구현하지 않는다.
- 소셜 로그인은 구현하지 않는다.
- 결제/구독은 구현하지 않는다.
- 이메일 인증/비밀번호 재설정 메일은 MVP 이후로 둔다.

---

## 14. Version History

| Version | Date | Changes | Author |
|---|---|---|---|
| 0.1 | 2026-06-04 | Initial design from PDCA plan | Codex |
