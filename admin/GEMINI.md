# 관리자 패널 지침

이 지침은 `admin/` 디렉토리에 특별히 적용됩니다.

## 레이아웃 및 컴포넌트
- **헤더/푸터:** `smartcms_admin_page_header()`와 `smartcms_admin_footer()`를 사용하세요.
- **네비게이션:** 새로운 메뉴 항목은 `admin/common.php`의 `smartcms_admin_nav_items()`에 추가해야 합니다.
- **UI 컴포넌트:** 일관성을 위해 `common/ui/components.php`에 정의된 컴포넌트(카드, 알림 등)를 사용하세요.

## 접근 제어
- 모든 관리자 페이지는 상단에서 `smartcms_admin_user()`를 호출하여 사용자가 충분한 레벨(기본값: 8)을 보유하고 있는지 확인해야 합니다.
