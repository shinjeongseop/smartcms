# GitHub Commit Log Webhook

GitHub `push` 이벤트를 SmartCMS 게시판에 자동 등록하는 운영 문서다.

## 구조

1. GitHub Actions가 `main` push를 받는다.
2. 워크플로가 커밋 목록과 비교 링크를 만든다.
3. 워크플로가 `config.local.php`와 `webhook.local.php`를 생성한다.
4. FTP로 SmartCMS 서버에 배포한다.
5. `POST /webhooks/github-commit-log/index.php`로 JSON payload를 전송한다.
6. 서버는 토큰을 확인하고 `releases` 게시판에 글을 만든다.

## 관련 파일

- [`.github/workflows/deploy.yml`](/D:/smartcms/.github/workflows/deploy.yml)
- [`webhooks/github-commit-log/index.php`](/D:/smartcms/webhooks/github-commit-log/index.php)
- [`common/board.php`](/D:/smartcms/common/board.php)
- [`common/config.php`](/D:/smartcms/common/config.php)

## 설정

### GitHub Secrets

- `SMARTCMS_DB_HOST`
- `SMARTCMS_DB_NAME`
- `SMARTCMS_DB_USER`
- `SMARTCMS_DB_PASS`
- `SMARTCMS_WEBHOOK_URL`
- `SMARTCMS_WEBHOOK_TOKEN`
- `SMARTCMS_WEBHOOK_BOARD_KEY`
- `SMARTCMS_WEBHOOK_AUTHOR_NAME`

### 런타임 파일

워크플로가 배포 시 다음 파일을 생성한다.

- `config.local.php`
- `webhook.local.php`

`config.local.php`

```php
<?php
return [
    'table_prefix' => 'sc_',
    'db' => [
        'host' => 'localhost',
        'name' => 'smartcms',
        'user' => 'smartcms',
        'pass' => '비밀값',
        'charset' => 'utf8mb4',
    ],
];
```

`webhook.local.php`

```php
<?php
return [
    'github_commit_log' => [
        'token' => '비밀값',
        'board_key' => 'releases',
        'author_name' => 'GitHub Actions',
    ],
];
```

## 게시글 규칙

- 제목은 첫 번째 커밋 메시지를 기준으로 실제 변경을 짐작할 수 있게 요약한다.
- 커밋이 1건이면 그 메시지를 제목으로 쓴다.
- 커밋이 여러 건이면 첫 메시지 뒤에 `외 N건 변경`을 붙인다.
- 본문은 `커밋 상세`부터 시작한다.
- 각 항목은 해결 내용, 짧은 SHA, 수정 파일을 보여준다.
- 전체 SHA, 작성자, 시각, 설명 같은 보조 메타정보는 본문에 넣지 않는다.
- 작성자 기본값은 `GitHub Actions`다.

## 권한 규칙

- 자동 등록은 로그인 없이 웹훅 토큰만으로 처리한다.
- 게시판 화면에서 사람이 직접 글을 쓰는 경우는 기존 로그인/권한 정책을 따른다.
- 관리자 전용 화면 권한과 자동 등록 웹훅은 분리한다.

## 실패 코드

- `401`: 웹훅 토큰 불일치
- `404`: 게시판 키 오류 또는 게시판 미존재
- `500`: PHP 오류, DB 오류, `table_prefix` 누락, 설정 파일 구조 불일치

## 점검 순서

1. `SMARTCMS_WEBHOOK_URL`이 최신 서버 주소인지 확인한다.
2. `SMARTCMS_WEBHOOK_TOKEN`이 서버와 일치하는지 확인한다.
3. `SMARTCMS_WEBHOOK_BOARD_KEY`가 `releases`인지 확인한다.
4. 운영 DB가 `sc_` 접두사를 쓰면 `config.local.php`에도 반영한다.
5. GitHub Actions 로그의 `Publish commit log to SmartCMS board` 단계 응답을 확인한다.

## 운영 메모

- 웹훅은 사람 로그인과 독립적이다.
- GitHub Actions의 작성자 표시는 환경변수 `SMARTCMS_WEBHOOK_AUTHOR_NAME`으로 바꿀 수 있다.
- `releases` 게시판의 자동 등록이 멈추면, 먼저 웹훅 응답 코드와 GitHub Actions 로그를 본다.
