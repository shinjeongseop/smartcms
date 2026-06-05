# smartcms

Independent PHP CMS core for reusable member management, level permissions, admin pages, board modules, and project-portable themes.

## Current Features

- PHP install wizard
- `config.local.php` generation
- Initial level 10 admin creation
- Member login, logout, registration, mypage, password change
- Admin dashboard
- 1-10 level permission model
- Page permission management
- Board permission management
- Board list, post list, post view, write, edit, hide
- Comments and comment moderation
- File upload and controlled download
- Search and pagination
- Access, login, and board audit logs
- CSRF protection for POST forms
- DB-backed site settings
- Board skin templates under `skins/board/default`

## Main Routes

- `/` home
- `/install/` install wizard
- `/member/` member hub
- `/member/login/` login
- `/member/register/` registration
- `/member/mypage/` mypage
- `/admin/` admin redirect
- `/admin/dashboard/` admin dashboard
- `/admin/users/` user management
- `/admin/boards/` board management
- `/admin/pages/` page permission management
- `/admin/logs/` log management
- `/admin/settings/` site settings
- `/board/` board hub and post list

## Setup

1. Copy the project to a PHP hosting environment.
2. Open `/install/`.
3. Enter DB connection information.
4. Create tables.
5. Create the first admin account.
6. Finish installation to create `install.lock`.

## Local Files Not Committed

- `config.local.php`
- `install.lock`
- uploaded files under `uploads/`

## Security Notes

- All POST forms use CSRF tokens.
- Uploaded files are stored under `uploads/`.
- `uploads/.htaccess` blocks PHP-like file execution on Apache-compatible hosting.
- File downloads go through `/board/download/` for permission checks.

## Documents

- Plan: `docs/01-plan/features/smartcms-core.plan.md`
- Design: `docs/02-design/features/smartcms-core.design.md`
- Do: `docs/02-design/features/smartcms-core.do.md`
