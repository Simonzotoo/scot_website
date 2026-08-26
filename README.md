# SCOTSA Platform

A PHP, MySQL, Tailwind CSS, and JavaScript platform for the School of Computing and Technology Students Association.

## Structure

- `index.php` - public homepage with hero, about, executives, announcements, gallery, and footer.
- `resources.php` - student resource portal with search, filters, and pagination.
- `download.php` - secure, rate-limited PDF download handler with tracking.
- `contact.php` - public contact page.
- `admin/` - admin dashboard, login, uploads, programs, courses, announcements, students roster, admin accounts, and profile/password management.
- `student/` - student self-registration/login and a personal dashboard (recommended resources for their program/level, download history, announcements).
- `includes/` - config, database, auth (admin + student), session, security, icon vocabulary, and layout files. Not web-accessible (see `includes/.htaccess`).
- `assets/css/tailwind.input.css` - Tailwind source; compiles to `assets/css/tailwind.css`. That compiled file **is committed** (there's no server-side build step on typical shared PHP hosting) — rebuild and commit it whenever markup classes change.
- `assets/css/styles.css` - custom UI styles layered on top of the compiled Tailwind build.
- `assets/js/main.js` - mobile menus and confirmation behavior.
- `database/schema.sql` - base MySQL schema and seed data.
- `database/migrations/` - incremental migrations to run after `schema.sql` (login throttling, audit log, rate limiting, search indexes, student account passwords).
- `uploads/` - PDF storage. Not web-accessible directly — always served through `download.php`.

## Setup

1. Create the database and apply migrations, in order:

   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p scotsa_platform < database/migrations/002_production_hardening.sql
   mysql -u root -p scotsa_platform < database/migrations/003_rate_limit_table.sql
   mysql -u root -p scotsa_platform < database/migrations/004_student_accounts.sql
   ```

2. Copy `.env.example` to `.env` and fill in real values:

   ```bash
   cp .env.example .env
   ```

   At minimum, set `DB_USER`/`DB_PASS` to a dedicated low-privilege MySQL user (not `root`) with `SELECT, INSERT, UPDATE, DELETE` on `scotsa_platform.*` — the app never needs schema-altering privileges at runtime. Set `APP_ENV=production` on a real deployment (enables HTTPS redirect + HSTS + hides PHP errors from output). Leave `APP_BASE_URL` unset unless you're behind a reverse proxy that rewrites the path — it's otherwise auto-detected from where the project sits under the web server's document root. Set `STUDENT_EMAIL_DOMAIN` to your school's email domain — only addresses ending in it can self-register a student account.

3. Install Node dependencies and build the Tailwind CSS (Node/npm is a **build-time only** dependency — the deployed app itself is plain PHP and needs no Node runtime):

   ```bash
   npm install
   npm run build:css
   ```

   Re-run `npm run build:css` (or `npm run watch:css` while developing) any time you add or change Tailwind classes in markup — the compiled `assets/css/tailwind.css` is what ships, not the CDN.

4. Serve the app through Apache (e.g. XAMPP's htdocs) so `.htaccess` rules apply, or via PHP's built-in server for quick local checks (note: the built-in server does **not** honor `.htaccess`, so the `/uploads`, `/includes`, `/database`, and `.env` protections won't be active under it):

   ```bash
   php -S localhost:8000
   ```

5. Visit the app at whatever path it's served from (e.g. `http://localhost/scot/index.php` under XAMPP).

## Default admin & first login

- Email: `admin@scotsa.edu`
- Password: `Admin@12345`

**Rotate this immediately** — sign in, go to **My Profile → Change Password**, and set a new password (10+ characters). To add more admins or manage roles, a super admin can use **Admin → Admins** in the sidebar.

## Student portal

Students self-register at `/student/register.php` with a `@STUDENT_EMAIL_DOMAIN` email and a password (no email verification step — the domain match is the gate, so no outbound mail setup is required). Their dashboard at `/student/dashboard.php` shows resources matching their program/level, their own download history, and recent announcements.

Two related admin-side notes:

- **Admin → Students** (`admin/users.php`) can pre-add a roster entry without a password; the student "claims" it by registering with the same email later. It can also **reset a student's password** (clears it back to unset), which is the account-recovery path since there's no email-based "forgot password" flow.
- Every download made while a student is signed in is attributed to their account (`downloads.user_id`), which is what powers their dashboard's download history.

## Production checklist

- `.env` has `APP_ENV=production`, a non-root DB user, and real DB credentials — never commit `.env`.
- Served over HTTPS (the app enforces this and sets HSTS once `APP_ENV=production`).
- Deployed through Apache/Nginx with `.htaccess`-equivalent rules honored, so `/uploads`, `/includes`, `/database`, and dotfiles stay blocked from direct access.
- `assets/css/tailwind.css` has been rebuilt (`npm run build:css`) after any markup change.
- Default admin password changed.
