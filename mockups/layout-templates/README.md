# SmartCMS Layout Templates

SmartCMS에서 반복 사용할 수 있는 Tailwind CSS 기반 정적 레이아웃 목업입니다.

## Pages

- `/home.html`: SmartCMS 메인 홈 레이아웃
- `/admin.html`: SmartCMS 관리자 대시보드 레이아웃

두 페이지 모두 시멘틱 HTML, 분리된 JavaScript, Tailwind 빌드 CSS를 사용합니다. 데이터는 화면 검토용 더미 데이터입니다.

## Docker

```powershell
docker compose up --build -d
```

공용 Caddy HTTPS 프록시가 실행 중이면 다음 주소를 확인합니다.

- `https://localhost/smartcms-layouts/home.html`
- `https://localhost/smartcms-layouts/admin.html`

프록시를 사용하지 않을 때는 Docker 직접 포트로 확인할 수 있습니다.

- `http://localhost:8088/home.html`
- `http://localhost:8088/admin.html`

중지:

```powershell
docker compose down
```

## Local CSS Build

```powershell
npm install
npm run build
```
