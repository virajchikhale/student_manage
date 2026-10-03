# 🎓 Student Management System

A web-based **Student Management System** built with PHP 8, PostgreSQL, and Bootstrap 4. This application provides a hierarchical, multi-role platform for managing academic institutions — from Admin oversight down to Teacher registration — with OTP-based email verification, session authentication, and a responsive admin dashboard.

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [System Architecture](#-system-architecture)
- [User Roles](#-user-roles)
- [Project Structure](#-project-structure)
- [Database Schema](#-database-schema)
- [Email & OTP System](#-email--otp-system)
- [Getting Started](#-getting-started)
- [Security](#-security)
- [Production deployment](#-production-deployment)
- [Testing](#-testing)
- [Known Limitations](#-known-limitations)
- [License](#-license)

---

## ✨ Features

- **Role-Based Access Control** — Five roles: Admin, Principal, HOD, Teacher and Student, each with its own dashboard and permissions
- **Student Records** — Enrol, edit, search, filter and delete students; auto roll numbers; temporary password emailed to the new student; printable student record
- **Courses** — Courses per department and semester with an assigned teacher; students are placed in every course of their department + semester
- **Attendance** — Take attendance per course and date (Present / Late / Absent), edit past days, session history chart, per-student percentage
- **Exams & Marks** — Create exams with maximum marks, enter marks with live percentage and grade, class average and pass count
- **Reports** — Attendance, results and low-attendance (< 75 %) reports with CSV export and print
- **Notices** — Announcements for everyone, staff only or students only
- **Role dashboards** — Stat cards, attendance trend, department / semester charts and "needs attention" lists (pure CSS, no chart library)
- **My profile** — Everyone can edit their details and change their password
- **OTP Email Verification** — Every registration is secured with a one-time password sent via PHPMailer (SMTP)
- **Multi-Step Registration Wizard** — Guided step-by-step registration forms for HODs and Teachers
- **Forgot Password Flow** — Email-based OTP verification to securely reset passwords
- **Admin Dashboard** — Real-time statistics showing counts of Principals, HODs, Teachers, and Departments
- **Session-Based Authentication** — Secure login sessions per role
- **Hierarchical Reporting Structure** — Teachers report to HODs; HODs report to Principals; Principals report to Admins
- **Department Management** — Departments are linked to HODs; only unassigned departments are available during HOD registration
- **Duplicate Prevention** — Real-time AJAX validation to prevent duplicate emails and phone numbers across all role tables
- **Responsive UI** — Bootstrap 4-based layout with a sidebar app shell

---

## 🛠 Tech Stack

| Layer        | Technology                                  |
|--------------|---------------------------------------------|
| **Backend**  | PHP 8.x (PDO, prepared statements)          |
| **Database** | PostgreSQL 16 — `student_management` database |
| **Frontend** | HTML5, CSS3, JavaScript (ES5), jQuery 3.x |
| **UI Framework** | Bootstrap 4 |
| **Email**    | PHPMailer (any SMTP server; Mailpit in Docker)      |
| **Icons / fonts** | Font Awesome 4.7, Material Design Iconic Font, Open Sans, Montserrat, Poppins (Nunito from Google Fonts on the login pages) |
| **Charts**   | None - dashboards are pure CSS (bars, rings, trend) |
| **Other**    | Select2, Animsition, Tilt, jQuery Steps (registration wizard) |
| **Packaging**| Docker Compose (app, PostgreSQL, Mailpit, one-shot seed job) |

---

## 🏗 System Architecture

```
student_manage/
│
├── index.php               ← Landing page (links to every portal)
├── forgot_password.php     ← Password reset (OTP) for every role: ?role=hod
├── logout.php
├── admin/                  ← Admin portal: login, register, dashboard, people, departments
├── login/                  ← Principal / HOD / Teacher / Student login pages
├── registration/           ← Principal / HOD / Teacher registration wizards
├── portal/                 ← Dashboard after principal / HOD / teacher / student login
├── app/                    ← Shared pages: students, student, courses, attendance, exams, marks,
│                             reports, export (CSV), notices, staff, profile
├── api/                    ← JSON endpoints: login, register, otp, check, reset_password, admin,
│                             student, course, attendance, marks, notice, profile
├── includes/
│   ├── bootstrap.php       ← env config, PDO, session, CSRF, OTP, mail helpers
│   ├── domain.php          ← permissions (who sees / edits what), grades, attendance maths
│   ├── layout.php          ← sidebar app shell + UI helpers (stat cards, bars, rings, pager)
│   ├── dashboard.php       ← dashboard bodies per role
│   ├── reports.php         ← report queries shared by the screen and the CSV export
│   ├── css/app.css, js/app.js  ← design system and shared behaviour (toasts, confirm, API forms)
│   └── js/auth.js          ← client for the login / registration forms
├── email/phpmailer/        ← PHPMailer library (used by includes/bootstrap.php)
├── database/               ← schema.sql, seed.php (demo data)
├── tests/smoke.sh          ← black-box smoke + security test (run in CI)
├── Dockerfile, docker-compose.yml, docker-compose.prod.yml, stop.sh, .env.example
```

---

## 👥 User Roles

The system supports a strict hierarchical structure:

```
Admin
  └── Principal
        └── HOD (Head of Department)
              └── Teacher
                    └── Student (enrolled by the staff, no self sign-up)
```

| Role          | Login Portal         | Registration                  | Reports To    |
|---------------|----------------------|-------------------------------|---------------|
| **Admin**     | `admin/index.php`    | `admin/register.php`          | —             |
| **Principal** | `login/principal_reg.php` | `registration/principal_reg.php` | Admin    |
| **HOD**       | `login/hod_login.php`| `registration/hod_reg.php`    | Principal     |
| **Teacher**   | `login/teacher_reg.php` | `registration/teacher_reg.php` | HOD        |
| **Student**   | `login/student_login.php` | created by Admin / Principal / HOD | —       |

### What each role can do

| | Admin | Principal | HOD | Teacher | Student |
|---|:-:|:-:|:-:|:-:|:-:|
| Departments, accounts, principal codes | ✔ | | | | |
| Add / edit / delete students and courses | ✔ | ✔ | own department | | |
| View students and courses | all | all | own department | own department | self / own class |
| Take attendance, create exams, enter marks | ✔ | ✔ | own department | own courses | |
| Reports + CSV export | ✔ | ✔ | own department | own department | |
| Post notices | ✔ | ✔ | ✔ | ✔ | read only |
| See own attendance, results, notices | | | | | ✔ |

> **HOD Registration Note:** A HOD must select an available (unassigned) department and a reporting Principal during registration. Once a HOD is assigned to a department, that department is marked as occupied and unavailable for other HODs.

---

## 🗄 Database Schema

The application connects to a PostgreSQL database named **`student_management`**.

### Tables

| Table            | Key Columns                                                            |
|------------------|------------------------------------------------------------------------|
| `admin_reg`      | `id`, `first_name`, `last_name`, `email`, `phone`, `password`          |
| `principal_reg`  | `id`, `first_name`, `last_name`, `email`, `phone`, `password`          |
| `hod_reg`        | `id`, `first_name`, `last_name`, `email`, `phone`, `password`, `report_to`, `department_id` |
| `teacher_reg`    | `id`, `first_name`, `last_name`, `email`, `phone`, `password`, `report_to`, `department_id` |
| `department`     | `id`, `name`, `status` (`0` = available, `1` = assigned)              |
| `student`        | `id`, `roll_no`, name, `email`, `phone`, `password`, `department_id`, `semester`, `status` (active / inactive / graduated), guardian + address details |
| `course`         | `id`, `code`, `name`, `department_id`, `semester`, `credits`, `teacher_id` |
| `attendance`     | `course_id`, `student_id`, `att_date`, `status` (`P` / `A` / `L`) — unique per course, student and day |
| `exam`, `mark`   | an exam belongs to a course (`title`, `exam_date`, `max_marks`); `mark` is one student's score |
| `notice`         | `title`, `body`, `audience` (all / staff / students), author |

> Passwords are stored with `password_hash()` (bcrypt). Accounts from the old MD5 version still work and are upgraded on first login.
> A course is taught to every *active* student with the same department and semester, so there is no separate enrolment table.
> Grades: A+ ≥ 90, A ≥ 80, B+ ≥ 70, B ≥ 60, C ≥ 50, D ≥ 40 (pass mark), F below. Minimum attendance: 75 %. Both are constants in `includes/domain.php`.
> `details` holds the principal verification codes an admin generates on the dashboard.

---

## 📧 Email & OTP System

Mail is sent with **PHPMailer** through the SMTP server configured by the `SMTP_*` environment variables (see `.env.example`). With Docker the bundled Mailpit catches every mail at http://localhost:8025.

| Mail          | Trigger                                  |
|---------------|------------------------------------------|
| OTP           | Entering an email on a registration page / on the forgot-password page |
| Welcome       | After successful registration            |
| Password alert| After a successful password change       |

### OTP Flow

1. The user enters an email; the server checks it (unique for sign-up, registered for reset)
2. The server generates a random OTP, keeps only a keyed hash of it in the session (10 minute expiry, 5 tries, 30 s resend delay) and emails it
3. The OTP is sent back with the final form and **verified on the server**; it is single-use
4. In `DEMO=true` the OTP is also shown in a popup, since demo visitors have no inbox

---

## 🚀 Getting Started

See [SETUP.md](./SETUP.md) for full installation and configuration instructions.

---

## 🔒 Security

- All SQL uses prepared statements; table names come from a fixed whitelist. Output is escaped through shared helpers.
- Passwords use `password_hash`; login also equalises timing for unknown accounts.
- Sessions are server-side (`SMSSESSID`): HttpOnly, SameSite=Lax, `Secure` over HTTPS, ID regenerated on login. Every state-changing API call needs a CSRF token.
- Every endpoint re-checks the caller's role and department/course scope on the server; the UI hiding a button is never the control.
- **Rate limiting is stored in the database** (table `rate_limit`), so it cannot be dodged by dropping the session cookie: 5 failed logins per address+account and 30 per address in 15 minutes; 5 OTP e-mails per address and 30 per client per hour; 10 wrong principal verification codes per client per hour. Behind a proxy set `TRUST_PROXY=true` so the real client address is used.
- OTPs are random, stored only as a keyed hash, expire after 10 minutes, allow 5 tries and are single-use.
- Apache sends `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy` and a Content-Security-Policy; directory listing is off; `.env`, `database/`, `email/`, `docker/` and `*.md/*.sql` are never served; version banners are hidden.
- Admin sign-up is only open for the very first admin, or to a signed-in admin. **In production create the first admin with `ADMIN_EMAIL` / `ADMIN_PASSWORD`** (the production compose file requires them), otherwise whoever reaches a fresh install first can claim it.
- SMTP credentials come only from the environment. An old hard-coded Gmail app password remains in the git history of this repository: it **must be revoked** (Google Account > Security > App passwords).

---

## 🏭 Production deployment

```bash
cp .env.example .env     # set DB_PASSWORD, ADMIN_EMAIL, ADMIN_PASSWORD, SMTP_*, TRUST_PROXY=true, DEMO=false
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
```

The production override refuses to start with default credentials, drops Linux capabilities, removes Mailpit, forces `DEMO=false` and publishes the app on `127.0.0.1` only. Put a TLS reverse proxy in front of it (nginx, Caddy, Cloudflare Tunnel...) that forwards `X-Forwarded-For` / `X-Forwarded-Proto`, and keep `TRUST_PROXY=true` so HTTPS cookies, HSTS and per-client rate limits work.

Backup and restore:

```bash
docker compose exec db sh -c 'pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB"' > backup.sql
docker compose exec -T db sh -c 'psql -U "$POSTGRES_USER" "$POSTGRES_DB"' < backup.sql
```

Existing databases pick up schema changes (such as the `rate_limit` table) automatically: the `seed` service re-applies the idempotent `database/schema.sql` on every start.

---

## 🧪 Testing

`tests/smoke.sh` is a black-box test of a running stack (demo mode): availability, blocked files, security headers, CSRF and authorization checks, and that the login and OTP rate limits hold even when every request uses a fresh session.

```bash
DEMO=true docker compose up -d --build
BASE_URL=http://localhost:8080 tests/smoke.sh      # use a fresh database: docker compose down -v
```

CI (`.github/workflows/ci.yml`) lints every PHP file, validates both compose files, runs the smoke test against a real stack and scans for committed secrets.

---

## ⚠️ Known Limitations

- **No automated unit tests** for the PHP code yet; coverage comes from `tests/smoke.sh` (HTTP level).
- **No second factor** and no password rules beyond a minimum length of 8.
- **Legacy hashes:** accounts that still carry an unsalted MD5 password from the very first version are accepted once and upgraded to `password_hash` at their next login.
- **OTPs live in the browser session**, so a code only works in the browser that requested it.
- **Rate limits are fixed-window per address / account.** A distributed attacker needs a WAF or reverse-proxy limits on top.
- **CSP allows `'unsafe-inline'`** for scripts and styles because the pages use inline handlers; it still blocks framing, foreign origins and plugins.
- **Bundled front-end libraries are old** (jQuery 3.2.1 / 3.3.1, Bootstrap 4.x, Select2) and are not auto-updated; upgrade jQuery to 3.7.x after retesting the wizard and Select2 pages.
- **Login pages load Nunito from Google Fonts**; self-host it if that third-party request matters to you.
- **Single node:** PHP sessions are files inside the app container (lost when it is recreated; not shared between replicas). Use a shared session store before scaling out.
- **No audit log** of who changed what beyond `attendance.marked_by`.

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

> Built with ❤️ as an academic project to demonstrate full-stack PHP development with role-based access control, email integration, and multi-step form workflows.
