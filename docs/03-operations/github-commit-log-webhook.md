# GitHub Commit Log Webhook

`main` 브랜치에 push가 일어날 때, GitHub Actions가 SmartCMS 게시판에 커밋 로그를 자동 등록하는 기능이다.

## 동작 흐름

1. GitHub Actions가 `push` 이벤트를 받는다.
2. 최근 커밋 목록과 비교 링크를 JSON으로 만든다.
3. `POST /webhooks/github-commit-log/` 로 전송한다.
4. SmartCMS가 비밀 토큰을 확인한 뒤 게시판 글을 생성한다.

## 서버 설정

`config.local.php` 또는 동등한 서버 전용 설정에 아래 값을 둔다.

```php
return [
    'webhooks' => [
        'github_commit_log' => [
            'token' => '여기에_긴_비밀값',
            'author_name' => 'GitHub Actions',
        ],
    ],
];
```

`board_key`는 요청 본문에서 전달하거나, 서버 설정에 넣어도 된다.

## GitHub Actions secrets

- `SMARTCMS_WEBHOOK_URL`: 예: `https://example.com/webhooks/github-commit-log/`
- `SMARTCMS_WEBHOOK_TOKEN`: 서버 설정의 `token`과 같은 값
- `SMARTCMS_WEBHOOK_BOARD_KEY`: 자동 등록할 게시판 키, 예: `notice` 또는 `changelog`

## 요청 형식

`Content-Type: application/json`

```json
{
  "board_key": "changelog",
  "repository": "shinjeongseop/smartcms",
  "branch": "main",
  "before": "abc1234",
  "after": "def5678",
  "compare_url": "https://github.com/shinjeongseop/smartcms/compare/abc1234...def5678",
  "author_name": "GitHub Actions",
  "commits": [
    {
      "sha": "def5678",
      "message": "fix: update comment avatar",
      "author": "Codex",
      "timestamp": "2026-06-17T10:00:00+09:00"
    }
  ]
}
```

## 게시글 규칙

- 제목은 기본적으로 `브랜치 + 커밋 수`로 만든다.
- 본문은 저장소, 브랜치, 비교 링크, 커밋 목록을 포함한다.
- 커밋이 많으면 최대 20개까지만 본문에 담고 나머지는 생략한다.
