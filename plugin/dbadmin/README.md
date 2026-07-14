# MyAdmin — Web Database Manager

PHP + MySQL 기반의 경량 웹 데이터베이스 관리 도구입니다.  
phpMyAdmin 없이 브라우저에서 DB 스키마 변경부터 데이터 입력까지 모두 처리할 수 있습니다.

---

## 스크린샷

> `http://your-server/dbadmin/`

---

## 요구사항

| 항목 | 버전 |
|------|------|
| PHP | 8.0 이상 |
| MySQL / MariaDB | 5.7 / 10.3 이상 |
| 웹서버 | Apache / Nginx |
| 브라우저 | Chrome, Edge, Firefox (최신) |

---

## 설치

### 1. 저장소 받기

```bash
git clone https://github.com/shinjeongseop/dbadmin.git
```

또는 프로젝트 전체를 웹서버 루트나 호스트 프로젝트의 플러그인 폴더에 업로드합니다.

```
/html/plugins/dbadmin (또는 /www/dbadmin 등)
├── config/
│   ├── config.sample.php
│   └── config.php
├── common/
│   ├── css/common.css
│   ├── js/common.js
│   └── php/
│       ├── bootstrap.php
│       ├── config.php
│       ├── db.php
│       ├── common.php
│       └── db.php
├── index.php
├── login.php
├── app.js
├── ui.js
└── style.css
```

### 2. 설정 파일 준비

`config/config.sample.php`를 `config/config.php`로 복사한 뒤 환경에 맞게 수정합니다.

```bash
cp config/config.sample.php config/config.php
```

필수로 확인할 항목은 다음과 같습니다.

```php
define('MYADMIN_PLUGIN_URL', '/plugins/dbadmin');
define('MYADMIN_DB_HOST', 'localhost');
define('MYADMIN_DB_USER', 'your_db_user');
define('MYADMIN_DB_PASS', 'your_db_password');
define('MYADMIN_DB_PORT', 3306);
define('MYADMIN_ADMIN_PASSWORD', 'your_admin_password');
```

### 3. 웹서버에 배포

프로젝트가 웹에서 직접 접근 가능한 위치에 있어야 합니다.

- 웹서버 루트 배포 예시: `http://your-server/dbadmin/`
- 플러그인 배포 예시: `http://your-server/plugins/dbadmin/`

### 4. 접속

브라우저에서 접속한 뒤 로그인 화면에 `MYADMIN_ADMIN_PASSWORD` 값을 입력합니다.

### 5. 타 프로젝트 복사 설치

다른 PHP 프로젝트에는 이 프로젝트 폴더 전체를 `dbadmin/` 이름으로 원하는 플러그인 경로에 복사합니다.

| 설치 위치 | `MYADMIN_PLUGIN_URL` | 접속 URL |
|-----------|----------------------|----------|
| `/plugins/dbadmin` | `/plugins/dbadmin` | `/plugins/dbadmin/` |
| `/plugin/dbadmin` | `/plugin/dbadmin` | `/plugin/dbadmin/` |
| `/dbadmin` | `/dbadmin` | `/dbadmin/` |

호스트 프로젝트의 `common/`, CSS, JS 파일을 참조하지 않으며, 플러그인 내부 파일만으로 동작합니다.

### 6. 배포 체크

- `config/config.php`는 커밋하지 않고 서버별로 따로 관리합니다.
- `config/` 디렉터리는 웹에서 직접 접근되지 않도록 차단하는 것이 안전합니다.
- 관리자 비밀번호는 `MYADMIN_ADMIN_PASSWORD` 값으로만 제어합니다.

### 7. 최소 필요 파일

다른 프로젝트에 붙일 때는 아래 파일과 폴더를 함께 복사하면 됩니다.

- `head.php`
- `foot.php`
- `common/`
- `config/config.sample.php`
- `index.php`
- `login.php`
- `logout.php`
- `dbs.php`
- `list.php`
- `write.php`
- `update.php`
- `save_row.php`
- `delete.php`
- `export.php`
- `import.php`
- `structure.php`
- `structure.php`
- `app.js`
- `ui.js`
- `style.css`

필요하면 이 폴더를 통째로 복사하는 쪽이 가장 안전합니다. 일부 파일만 빼서 옮기면 공통 include 경로가 어긋날 수 있습니다.

### 8. 복사 후 점검

1. `config/config.php`가 서버별 값으로 생성됐는지 확인합니다.
2. `MYADMIN_PLUGIN_URL`이 실제 설치 경로와 같은지 확인합니다.
3. 브라우저에서 메인 페이지와 로그인 페이지가 열리는지 확인합니다.
4. DB 목록, 테이블 목록, 구조 조회가 정상 응답하는지 확인합니다.

---

## 기능

### 데이터 관리 (Data CRUD)
- 테이블 데이터 목록 조회 (페이지네이션)
- 행 추가 / 수정 / 삭제
- **서버사이드 검색** — 전체 컬럼 또는 특정 컬럼 대상 LIKE 검색
- **컬럼별 정렬** — 헤더 클릭으로 ASC / DESC 전환
- **CSV 내보내기** — 현재 검색·정렬 상태 그대로 다운로드 (UTF-8 BOM, Excel 호환)
- **CSV 가져오기** — 헤더 자동 매칭, AUTO_INCREMENT 컬럼 자동 제외

### 외래키 관리 (Foreign Key)
- 외래키 목록 시각화
- 참조 테이블 클릭으로 바로 이동

### 구조 보기 (Structure)
- 컬럼 메타 정보 (타입 / NULL / KEY / DEFAULT / EXTRA / COMMENT)
- 인덱스 섹션
- 외래키 섹션
- 읽기 전용 조회 화면

### 보안
- 세션 기반 로그인 (비밀번호 인증)
- 모든 식별자 backtick escape 처리
- 모든 값 `mysqli_real_escape_string` 처리
- 컬럼 타입 whitelist 검증
- 읽기 전용 동작

---

## 프로젝트 구조

```
dbadmin/
├── common/
│   ├── css/
│   │   └── common.css
│   ├── js/
│   │   └── common.js
│   └── php/
│       ├── bootstrap.php   # 플러그인 초기화
│       ├── config.php      # 호환 진입점
│       ├── db.php          # DB 연결 헬퍼
│       ├── common.php      # 요청/응답 헬퍼
│       └── db.php          # DB 연결 헬퍼
├── head.php                # 공통 head
├── foot.php                # 공통 footer/script
├── index.php               # 메인 페이지
├── ui.js                   # 네이티브 모달/탭 유틸
├── app.js                  # 프론트엔드 로직
├── style.css               # 스타일
├── login.php               # 로그인
├── logout.php              # 로그아웃
├── dbs.php                 # DB / 테이블 / 컬럼 목록 API
├── list.php                # 데이터 목록 (검색, 정렬)
├── write.php               # 행 추가 폼
├── update.php              # 행 수정 폼
├── save_row.php            # 행 저장 API (INSERT / UPDATE)
├── delete.php              # 행 삭제 API
├── export.php              # CSV 내보내기
├── import.php              # CSV 가져오기
└── structure.php           # 구조 보기 (읽기 전용)
```

---

## 주의사항

- 단일 관리자 환경을 가정하여 설계되었습니다. 다중 사용자 권한 관리는 지원하지 않습니다.
- PK가 없는 테이블은 행 수정·삭제가 불가합니다.
- `config/config.php`에 DB 비밀번호가 평문으로 저장됩니다. 웹서버에서 `config/` 디렉터리 직접 접근을 제한하세요.
- 구조 조회와 행 편집 외의 관리자 기능은 제거되었습니다.

---

## 라이선스

MIT License

Copyright (c) 2026

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

---

## 사용 기술

- **Backend**: PHP 8+ (vanilla, MySQLi)
- **Frontend**: Vanilla JavaScript (IIFE 모듈 패턴)
- **UI**: 독립 CSS + 네이티브 모달/탭 유틸 (`style.css`, `ui.js`)
- **DB**: MySQL / MariaDB
