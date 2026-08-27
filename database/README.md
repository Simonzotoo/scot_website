# SCOTSA Database

Import the base schema, then apply migrations in order:

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p scotsa_platform < database/migrations/002_production_hardening.sql
mysql -u root -p scotsa_platform < database/migrations/003_rate_limit_table.sql
mysql -u root -p scotsa_platform < database/migrations/004_student_accounts.sql
mysql -u root -p scotsa_platform < database/migrations/005_profile_avatars.sql
mysql -u root -p scotsa_platform < database/migrations/006_exam_period_and_file_types.sql
mysql -u root -p scotsa_platform < database/migrations/007_manageable_content.sql
mysql -u root -p scotsa_platform < database/migrations/008_page_hero_image.sql
mysql -u root -p scotsa_platform < database/migrations/009_page_hero_photo_pool.sql
mysql -u root -p scotsa_platform < database/migrations/010_fix_level_sort_order.sql
```

Or, more simply, apply every migration in the folder in filename order:

```bash
for f in database/migrations/*.sql; do mysql -u root -p scotsa_platform < "$f"; done
```

`migrations/` adds: `login_attempts` (login rate limiting), `audit_log` (admin action history), `rate_limit_hits` (general request throttling, used by `download.php`), search indexes on `past_questions`/`courses`, `users.password_hash` (turns the student roster into real accounts students can register/log into), `avatar_path` on both `admins` and `users` (profile photos, stored under `assets/images/avatars/` rather than `uploads/` since they need to be web-servable), `exam_month`/`exam_year` on `past_questions` plus a widened `mime_type`-aware upload pipeline, the `team_members`/`gallery_items`/`site_settings` tables that make the executives roster, gallery, and hero/contact settings admin-manageable instead of hardcoded in PHP, the `page_hero_photos` rotation pool for inner-page banners, and a fix for `levels.sort_order` (was `TINYINT`, silently clamped Level 300/400 to the same value — now `SMALLINT`).

Default admin account:

- Email: `admin@scotsa.edu`
- Password: `Admin@12345`

Change the default password after first login (**My Profile → Change Password** in the admin sidebar). Additional admin accounts are managed in-app under **Admins** (super admin role required) rather than by editing this table directly.

## Test database

Automated tests (see `../tests/`) run against a **separate** `scotsa_platform_test` database — they never touch the real `scotsa_platform` data. Set it up once:

```bash
mysql -u root -e "CREATE DATABASE scotsa_platform_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sed 's/scotsa_platform/scotsa_platform_test/g' database/schema.sql | mysql -u root
for f in database/migrations/*.sql; do sed 's/scotsa_platform/scotsa_platform_test/g' "$f" | mysql -u root; done

# The app connects as a dedicated least-privilege user (scotsa_app), not
# root — grant it the same access on the test DB that it already has on
# the real one:
mysql -u root -e "GRANT SELECT, INSERT, UPDATE, DELETE ON scotsa_platform_test.* TO 'scotsa_app'@'localhost'; FLUSH PRIVILEGES;"
```

Then run the suite with `composer test` (see the project root `README`/`composer.json`).
