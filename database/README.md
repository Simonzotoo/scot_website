# SCOTSA Database

Import the base schema, then apply migrations in order:

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p scotsa_platform < database/migrations/002_production_hardening.sql
mysql -u root -p scotsa_platform < database/migrations/003_rate_limit_table.sql
mysql -u root -p scotsa_platform < database/migrations/004_student_accounts.sql
mysql -u root -p scotsa_platform < database/migrations/005_profile_avatars.sql
```

`migrations/` adds: `login_attempts` (login rate limiting), `audit_log` (admin action history), `rate_limit_hits` (general request throttling, used by `download.php`), search indexes on `past_questions`/`courses`, `users.password_hash` (turns the student roster into real accounts students can register/log into), and `avatar_path` on both `admins` and `users` (profile photos, stored under `assets/images/avatars/` rather than `uploads/` since they need to be web-servable).

Default admin account:

- Email: `admin@scotsa.edu`
- Password: `Admin@12345`

Change the default password after first login (**My Profile → Change Password** in the admin sidebar). Additional admin accounts are managed in-app under **Admins** (super admin role required) rather than by editing this table directly.
