# SmartCMS 프로젝트 지침

이 파일은 SmartCMS 프로젝트를 위한 팀 공유 컨벤션 및 아키텍처 규칙을 포함합니다.

## 코딩 표준

- **PHP 버전:** PHP 8.1 이상
- **엄격한 타입 선언 (Strict Typing):** 모든 PHP 파일은 반드시 `declare(strict_types=1);`로 시작해야 합니다.
- **네이밍 규칙:**
  - 글로벌 함수는 반드시 `smartcms_` 접두사를 사용해야 합니다 (예: `smartcms_fetch_all`).
  - 변수 및 내부 함수는 `snake_case`를 사용합니다.
  - 데이터베이스 테이블은 테이블 접두사 처리를 위해 반드시 `smartcms_table()` 헬퍼를 통해 접근해야 합니다.
- **보안:**
  - HTML 출력 시 항상 `smartcms_h()`를 사용하여 이스케이프 처리를 해야 합니다.
  - POST 요청 핸들러에서는 항상 `smartcms_verify_csrf_or_fail()`를 호출해야 합니다.
  - 모든 데이터베이스 상호작용에는 PDO 준비된 문구(prepared statements)를 사용합니다.

## 아키텍처

- **데이터베이스:** `common/database.php`를 통해 접근합니다. 제공된 헬퍼 함수(`smartcms_fetch_one`, `smartcms_fetch_all`, `smartcms_execute` 등)를 사용하세요.
- **인증:** `common/auth.php`에서 관리됩니다. 접근 제어를 위해 `smartcms_require_login()` 또는 `smartcms_require_level()`을 사용하세요.
- **라우팅:** 파일 기반 라우팅과 `common/routes.php`를 통해 처리됩니다.
- **관리자 패널:** 상세 지침은 `admin/GEMINI.md`에서 확인할 수 있습니다.

## 워크플로우

- **오류 처리:** 데이터베이스 작업 및 사용자 대면 로직에는 `try...catch` 블록을 사용하여 민감한 정보가 유출되거나 치명적 오류가 발생하는 것을 방지하세요.
- **초기화:** 템플릿에서 사용되기 전에 `$total_users`, `$message`와 같은 뷰 전용 변수들을 항상 초기화하세요.
- **테스트:** 새로운 기능을 추가하거나 버그를 수정할 때, 영향을 받는 UI를 수동으로 테스트하거나 재현 스크립트를 사용하여 변경 사항을 검증하세요.
