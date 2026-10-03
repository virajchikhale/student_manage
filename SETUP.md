# ⚙️ Setup Guide — Student Management System

## 🐳 Docker (recommended)

```bash
cp .env.example .env        # optional: every value has a default
docker compose up -d --build
```

| What      | URL                    |
|-----------|------------------------|
| App       | http://localhost:8080  |
| Mailpit   | http://localhost:8025  (all OTP / notification mails land here) |

Services: `app` (PHP 8.3 + Apache), `db` (PostgreSQL 16), `seed` (one-shot job on every `up`), `mailpit`.

**First admin:** open `/admin/` → *Create the first admin* (OTP arrives in Mailpit), or set `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `.env` and an admin is created on start. After that, admin sign-up is closed; admins add admins from the dashboard (**Add admin**).

**Typical order:** admin → generate a *principal verification code* on the dashboard → principal registers with it → HODs (pick a free department + principal) → teachers (department must already have an HOD). Departments are managed under *Departments*.

### Demo mode

`DEMO=true` (in `.env` or exported) makes every start:

1. wipe all people, codes and departments, then load demo data (`database/seed.php`): 1 admin, 1 principal, 3 HODs, 7 teachers, code `DEMO-PRINCIPAL`; *Mechanical Engineering* is left without an HOD so HOD sign-up can be tried;
2. show demo logins on the login pages (`admin@demo.local`, `principal@demo.local`, `hod.cs@demo.local`, `teacher.cs1@demo.local`, password `demo12345` — `DEMO_PASSWORD`);
3. show OTP codes in a popup instead of requiring an inbox.

`./stop.sh` removes the containers **and the database volume** when `DEMO=true`, so the next start is clean (with `DEMO=false` it only stops). `_infra/demo.sh student` starts the stack with `DEMO=true`, opens a public tunnel and cleans everything up on Ctrl+C.

### Real email

Set in `.env` (Gmail needs an App Password):

```
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USER=you@gmail.com
SMTP_PASSWORD=your-app-password
MAIL_FROM=you@gmail.com
```

## 🖥 Without Docker

Needs PHP ≥ 8.1 with `pdo_pgsql`, and PostgreSQL 12+.

1. Create the database and load the schema: `createdb student_management && psql student_management -f database/schema.sql`
2. Provide the settings as environment variables of the web server / PHP-FPM (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `SMTP_*`, … — names as in `.env.example`; defaults are `localhost:5432` / `postgres` / empty password).
3. Serve the folder (the app also works in a sub-folder such as `/student_manage/`). Block `email/`, `database/` and `*.sql` from the web.
4. Optional demo data: `DEMO=true php database/seed.php`

## 🛠 Troubleshooting

| Issue | Fix |
|-------|-----|
| "Server error" on any form | `docker compose logs app` — usually the database is not reachable or the schema was not loaded (`docker compose run --rm seed`) |
| OTP mail not received | Check Mailpit (`:8025`) or your SMTP settings; the app log shows the SMTP error |
| Teacher form has no departments | Teachers can only join a department that already has an HOD |
| Port 8080 busy | Set `APP_PORT` in `.env` |
