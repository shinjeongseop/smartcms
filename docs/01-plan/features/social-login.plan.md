# 소셜 로그인 기능 추가 계획

> Feature: social-login
> Phase: Plan
> Target: smartcms PHP 회원 인증

## 1. 목적

현재 이메일/비밀번호 기반 로그인에 Google, Kakao, Naver 등 소셜 로그인 공급자를 추가한다.
소셜 로그인 사용 여부와 provider별 주요 키는 관리자 페이지에서 제어할 수 있게 한다.

## 2. 현재 구조 요약

- 회원 인증 핵심: `common/auth.php`
- 회원 로그인 화면: `member/login/index.php`
- 관리자 설정 화면: `admin/settings/index.php`
- 설정 저장소: `site_settings` 키-값 테이블
- 사용자 테이블: `users`
- 로그인 기록: `login_logs`, `access_logs`
- Composer 의존성 파일 없음. 외부 라이브러리 없이 PHP 표준 함수 기반 구현을 우선한다.

## 3. 범위

### 포함

- 관리자 설정에서 소셜 로그인 전체 사용 여부 제어
- provider별 사용 여부 제어
- provider별 Client ID, Client Secret, Redirect URI, Scope 관리
- 회원 로그인 화면에 활성화된 provider 버튼 표시
- OAuth state 검증 및 CSRF 방어
- provider callback 처리
- 기존 회원과 소셜 계정 연결
- 기존 이메일 회원 연결 시 최초 1회 기존 비밀번호 확인
- 신규 소셜 로그인 사용자 자동 가입
- 로그인 성공/실패 로그 기록

### 제외

- 소셜 계정 연결 해제 UI
- 여러 소셜 계정을 한 계정에 연결하는 고급 계정 관리 UI
- 관리자 로그인에 소셜 로그인 적용
- provider 관리자 콘솔 자동 설정
- SMS, Passkey, SAML, LDAP

## 4. 지원 provider 1차 범위

1. Google
2. Kakao
3. Naver

provider별 정확한 인증/토큰/userinfo 엔드포인트와 scope는 구현 시점에 공식 문서로 재확인한다.

## 5. 관리자 설정 요구사항

`admin/settings/index.php`에 "소셜 로그인" 섹션을 추가한다.

공통 설정:

- `social_login_enabled`: 전체 사용 여부
- `social_login_auto_register`: 신규 소셜 사용자 자동 가입 여부
- `social_login_default_level`: 소셜 가입 기본 회원 레벨

provider별 설정:

- `social_{provider}_enabled`
- `social_{provider}_client_id`
- `social_{provider}_client_secret`
- `social_{provider}_redirect_uri`
- `social_{provider}_scope`

관리자 화면 원칙:

- Client Secret은 저장 후 전체 값을 다시 노출하지 않는다.
- 비어 있는 secret 입력은 기존 값을 유지한다.
- "삭제" 체크박스를 둬야 secret 제거가 가능하다.
- Redirect URI는 추천 값을 화면에 표시하되 URL을 코드에서 추측 생성하지 않는다. 현재 요청 host 기반 표시가 필요하면 "참고값"으로만 노출한다.

## 6. 데이터 모델

신규 테이블: `user_social_accounts`

필드 초안:

- `id`
- `user_id`
- `provider`
- `provider_user_id`
- `provider_email`
- `provider_name`
- `access_token_hash`
- `refresh_token_hash`
- `linked_at`
- `last_login_at`
- `created_at`
- `updated_at`

제약:

- `UNIQUE(provider, provider_user_id)`
- `INDEX(user_id)`
- `INDEX(provider_email)`

기존 `users` 테이블 변경:

- 직접 변경 없이 기존 `email`, `name`, `nickname`, `avatar_path`, `level`, `role`, `status`를 재사용한다.
- 소셜 신규 가입 시 임시 비밀번호 해시는 `password_hash(random_bytes(...))`로 생성한다.

## 7. 인증 흐름

### 시작

1. 사용자가 `/member/login/`에서 소셜 로그인 버튼 클릭
2. `/member/social/start/?provider=google&next=...` 요청
3. provider 활성화 여부와 키 설정 확인
4. OAuth `state` 생성 후 세션에 저장
5. provider authorize URL로 redirect

### 콜백

1. `/member/social/callback/?provider=google&code=...&state=...`
2. 세션의 state와 요청 state 비교
3. code로 access token 요청
4. userinfo 조회
5. `provider_user_id` 기준으로 기존 연결 계정 조회
6. 연결 계정이 있으면 해당 user로 로그인
7. 연결 계정이 없고 같은 email 사용자가 있으면 소셜 연결 대기 세션을 생성
8. 기존 비밀번호 확인 화면으로 이동
9. 사용자가 기존 비밀번호를 1회 확인하면 해당 user에 소셜 계정을 연결
10. 연결 계정이 없고 같은 email도 없으면 자동 가입 설정에 따라 신규 user 생성
11. 세션 로그인 처리 및 `last_login_at`, 로그 갱신
12. 안전한 `next` 경로로 redirect

### 기존 이메일 회원 연결 확인

1. callback에서 같은 email의 기존 `users` 레코드를 찾는다.
2. 즉시 연결하지 않고 provider 정보, provider user id, next 경로를 세션에 임시 저장한다.
3. `/member/social/confirm/`에서 기존 이메일과 비밀번호를 확인한다.
4. `password_verify()` 성공 시 `user_social_accounts`에 연결 정보를 저장한다.
5. 확인 실패 시 일반 로그인 실패 메시지를 보여주고 연결하지 않는다.
6. 연결 성공 후에는 임시 세션 값을 삭제하고 기존 `users.id`로 로그인한다.

## 8. 보안 요구사항

- OAuth `state`는 세션 저장 후 1회성으로 사용한다.
- `next`는 기존 `smartcms_member_login_next_target()` 정책과 동일하게 내부 경로만 허용한다.
- Client Secret은 평문 노출을 피한다.
- 저장 암호화는 별도 앱 키가 없으면 구현 전 `SMARTCMS_SECRET_KEY` 또는 설정 파일 키 도입을 먼저 결정한다.
- 토큰 원문은 DB에 저장하지 않고 해시 또는 최소 정보만 저장한다.
- provider 응답 email이 없거나 검증되지 않은 경우 가입을 막는다.
- blocked/left 상태 사용자는 소셜 로그인으로 우회 로그인할 수 없다.
- 기존 이메일 회원과 소셜 계정 연결은 최초 1회 기존 비밀번호 확인 후에만 허용한다.
- 소셜 연결 대기 세션은 짧은 만료 시간을 두고, 성공/실패 후 즉시 삭제한다.
- 실패 사유는 사용자에게 일반화해 노출하고 상세 내용은 서버 로그 또는 로그인 로그에 남긴다.

## 9. 파일 변경 계획

신규:

- `common/social_auth.php`
- `member/social/start/index.php`
- `member/social/callback/index.php`
- `member/social/confirm/index.php`

수정:

- `common/schema.php`: `user_social_accounts` 테이블 생성
- `common/settings.php`: 소셜 로그인 기본 설정 추가
- `common/auth.php`: 소셜 로그인 세션 생성 헬퍼 추가 또는 기존 로그인 세션 함수 분리
- `common/auth.php`: 기존 회원 비밀번호 확인 재사용 함수 추가
- `member/login/index.php`: 소셜 로그인 버튼 영역 추가
- `admin/settings/index.php`: 소셜 로그인 설정 UI 및 저장 처리 추가
- `common/css/common.css`: 소셜 로그인 버튼 최소 스타일

## 10. 구현 순서

1. 설정 키와 관리자 UI 추가
2. `user_social_accounts` 테이블 추가
3. 공통 social auth provider 설정 로더 작성
4. OAuth start/callback 라우트 작성
5. provider별 token/userinfo 정규화 함수 작성
6. 회원 로그인 화면에 활성 provider 버튼 표시
7. 기존 email 계정 발견 시 비밀번호 확인 화면으로 분기
8. 비밀번호 확인 성공 시 소셜 계정 연결 처리
9. 신규 자동 가입 처리
10. 로그 기록과 예외 처리 정리
11. 문법 검사 및 수동 OAuth 테스트

## 11. 성공 기준

- 관리자 페이지에서 소셜 로그인 전체 on/off 가능
- provider별 on/off와 주요 키 저장 가능
- 비활성 provider 버튼은 로그인 화면에 표시되지 않음
- 활성 provider 클릭 시 provider 인증 페이지로 이동
- callback state 불일치 시 로그인 실패 처리
- 기존 email 회원은 최초 1회 기존 비밀번호 확인 후 새 계정을 만들지 않고 연결됨
- 기존 비밀번호 확인 실패 시 소셜 계정이 연결되지 않음
- 신규 소셜 사용자는 설정에 따라 자동 가입 또는 가입 차단됨
- 로그인 성공 후 기존 세션 권한 체계를 그대로 사용
- `php -l` 문법 검사 통과
- `rg "btn-outline"` 등 프로젝트 금지 패턴 회귀 없음

## 12. 주요 리스크

- provider별 OAuth 응답 포맷 차이
- Client Secret 저장 방식
- 이메일 미제공 또는 미검증 provider 계정 처리
- 기존 이메일 계정 연결 확인 UX 복잡도
- 운영 도메인과 Redirect URI 불일치

## 13. 결정 필요 사항

1. 1차 provider를 Google, Kakao, Naver로 확정할지
2. 신규 소셜 사용자 자동 가입을 기본 허용할지
3. Client Secret 암호화를 위해 별도 앱 키를 도입할지
4. 소셜 로그인을 회원 로그인에만 적용할지, 추후 관리자 로그인에도 확장할지

## 14. 확정 정책

- 기존 이메일 계정과 소셜 계정 연결은 최초 1회 기존 비밀번호 확인을 요구한다.
- 같은 email 사용자가 있더라도 provider callback만으로 자동 연결하지 않는다.
