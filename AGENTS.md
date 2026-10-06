# AGENTS.md

Plain PHP + mysqli app (no framework, no Composer, no npm, no build/test/lint pipeline). Comments, UI copy, and commit messages are in Indonesian.

## Run

```bash
php -S localhost:8000        # from project root; entry: http://localhost:8000/
```

Requires MySQL/MariaDB running with database `perpustakaan` (import `database.sql`). DB credentials are hardcoded in `config/koneksi.php` (`root`, empty password). There is no automated test suite — verify changes by curling the dev server (login flow, redirects) or manually in the browser.

## Architecture

- `index.php` (root) = router: session set → `user/home.php`, else → `auth/login.php`.
- Pages live in subfolders (`auth/`, `user/`); shared markup goes in `includes/` (`sidebar.php` is included by `user/*.php` via `__DIR__`).
- Auth state = `$_SESSION['sudah_login']`; every `user/` page must start with `session_start()` + guard redirect **before any output**.
- `auth/akun.php` is a one-shot account seeder (`admin_perpus` / `perpus2026`) — not linked anywhere; normally should be deleted after seeding.
- CSS is split by surface, all loaded per-page via `<link>`: `assets/css/style.css` (global reset + Ubuntu `@import` — must stay the first line of the file), `auth.css` (login/register), `sidebar.css`, `dashboard.css`.
- `gambar/` holds Figma design exports (e.g. `Login Page.jpg`, `Home Page.jpg`) — the source of truth for UI work.
- `user/` table `role` is `admin|petugas|anggota`; `user/home.php` conditionally renders extra menus for `petugas`.

## Gotchas (learned the hard way)

- **Relative paths are the #1 bug source.** `header("Location: ...")` and asset `href`s resolve relative to the *requesting file's folder*, not the project root. From `auth/`: `../user/home.php`; from `user/`: `../auth/login.php`, `../assets/...`. Root `index.php` uses paths *without* `../`.
- Any `echo`/HTML output before `header()` breaks redirects ("headers already sent"). `config/koneksi.php` intentionally outputs nothing.
- `session_start()` must run once per request; `includes/sidebar.php` guards with `session_status() === PHP_SESSION_NONE`.
- Sidebar active menu is hardcoded `class="active"` in `includes/sidebar.php` — update it when adding pages.

## Security conventions

- New DB writes: use prepared statements (`mysqli_prepare` + `bind_param`) and `password_hash()` — follow `auth/proses_register.php`. Known debt: `auth/proses_login.php` still uses `mysqli_real_escape_string` + interpolated SQL.
- Always escape output with `htmlspecialchars()` when echoing session/user data.
