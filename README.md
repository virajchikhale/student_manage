# 🎓 Student Management System

A web-based **Student Management System** built with PHP, MySQL, and Bootstrap 4. This application provides a hierarchical, multi-role platform for managing academic institutions — from Admin oversight down to Teacher registration — with OTP-based email verification, session authentication, and a responsive admin dashboard.

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
- [Known Limitations](#-known-limitations)
- [License](#-license)

---

## ✨ Features

- **Role-Based Access Control** — Four distinct user roles: Admin, Principal, HOD, and Teacher
- **OTP Email Verification** — Every registration is secured with a one-time password sent via PHPMailer (SMTP)
- **Multi-Step Registration Wizard** — Guided step-by-step registration forms for HODs and Teachers
- **Forgot Password Flow** — Email-based OTP verification to securely reset passwords
- **Admin Dashboard** — Real-time statistics showing counts of Principals, HODs, Teachers, and Departments
- **Session-Based Authentication** — Secure login sessions per role
- **Hierarchical Reporting Structure** — Teachers report to HODs; HODs report to Principals; Principals report to Admins
- **Department Management** — Departments are linked to HODs; only unassigned departments are available during HOD registration
- **Duplicate Prevention** — Real-time AJAX validation to prevent duplicate emails and phone numbers across all role tables
- **Responsive UI** — Bootstrap 4-based layout with animations (WOW.js, Animsition) and perfect scrollbar

---

## 🛠 Tech Stack

| Layer        | Technology                                  |
|--------------|---------------------------------------------|
| **Backend**  | PHP 5.x / 7.x (legacy `mysql_*` functions)  |
| **Database** | MySQL — `student_management` database       |
| **Frontend** | HTML5, CSS3, JavaScript (ES5), jQuery 3.2   |
| **UI Framework** | Bootstrap 4.1                           |
| **Email**    | PHPMailer (SMTP via Gmail App Password)      |
| **Icons**    | Font Awesome 4.7 / 5, Material Design Icons |
| **Charts**   | Chart.js                                    |
| **Map**      | jQVMap (Vector Map)                          |
| **Other**    | Select2, Animsition, WOW.js, Perfect Scrollbar, jQuery Steps (wizard) |

---

## 🏗 System Architecture

```
student_manage/
│
├── admin/                  ← Admin portal (login, register, dashboard)
│   ├── admin_includes/     ← Shared header & sidebar partials
│   ├── forgot_password/    ← Admin password reset (OTP-based)
│   ├── css / js / vendor/  ← Admin-specific assets
│   ├── index.php           ← Admin login page
│   ├── register.php        ← Admin registration with OTP
│   └── dashboard.php       ← Admin dashboard with live stats
│
├── login/                  ← Role-specific login pages
│   ├── hod_login.php       ← HOD login
│   ├── principal_reg.php   ← Principal login
│   └── teacher_reg.php     ← Teacher login
│
├── registration/           ← Role-specific multi-step registration forms
│   ├── hod_reg.php         ← HOD registration wizard (3 steps)
│   ├── principal_reg.php   ← Principal registration
│   └── teacher_reg.php     ← Teacher registration
│
├── sqloperations/          ← Backend AJAX handlers for DB operations
│   ├── insert_reg.php      ← Handles all role INSERT operations
│   ├── update_reg.php      ← Handles password update operations
│   └── login.php           ← Authenticates users against MD5-hashed passwords
│
├── validation/             ← Real-time AJAX validation endpoints
│   ├── emailvalid.php      ← Checks for duplicate email per role table
│   ├── checkmob.php        ← Checks for duplicate phone number
│   └── codevalid.php       ← OTP/code validation
│
├── email/                  ← PHPMailer-based email service
│   ├── email_base.php      ← Main mailer script
│   ├── variables.php       ← Email subject/body templates per role & action
│   └── index.php           ← Router / entry point
│
└── includes/               ← Shared assets and DB connection
    ├── connection.php      ← MySQL DB connection
    └── css / js / vendor/  ← Shared frontend libraries
```

---

## 👥 User Roles

The system supports a strict hierarchical structure:

```
Admin
  └── Principal
        └── HOD (Head of Department)
              └── Teacher
```

| Role          | Login Portal         | Registration                  | Reports To    |
|---------------|----------------------|-------------------------------|---------------|
| **Admin**     | `admin/index.php`    | `admin/register.php`          | —             |
| **Principal** | `login/principal_reg.php` | `registration/principal_reg.php` | Admin    |
| **HOD**       | `login/hod_login.php`| `registration/hod_reg.php`    | Principal     |
| **Teacher**   | `login/teacher_reg.php` | `registration/teacher_reg.php` | HOD        |

> **HOD Registration Note:** A HOD must select an available (unassigned) department and a reporting Principal during registration. Once a HOD is assigned to a department, that department is marked as occupied and unavailable for other HODs.

---

## 🗄 Database Schema

The application connects to a MySQL database named **`student_management`**.

### Tables

| Table            | Key Columns                                                            |
|------------------|------------------------------------------------------------------------|
| `admin_reg`      | `id`, `first_name`, `last_name`, `email`, `phone`, `password`          |
| `principal_reg`  | `id`, `first_name`, `last_name`, `email`, `phone`, `password`          |
| `hod_reg`        | `id`, `first_name`, `last_name`, `email`, `phone`, `password`, `report_to`, `department_id` |
| `teacher_reg`    | `id`, `first_name`, `last_name`, `email`, `phone`, `password`, `report_to`, `department_id` |
| `department`     | `id`, `name`, `status` (`0` = available, `1` = assigned)              |

> Passwords are stored as **MD5 hashes**.

---

## 📧 Email & OTP System

The email module (`email/`) uses **PHPMailer** with Gmail SMTP.

### Email Types

| Type                 | Trigger                                | Audience        |
|----------------------|----------------------------------------|-----------------|
| `reg_otp`            | On email entry during registration     | All roles        |
| `forgot_otp`         | On email entry during password reset   | All roles        |
| `thanks`             | After successful registration          | All roles        |
| `pass_change_alert`  | After successful password change       | All roles        |

### OTP Flow

1. User enters email on registration/forgot-password page
2. System validates the email (checks for existence/uniqueness)
3. A random OTP is generated server-side and emailed to the user
4. User enters the OTP on the frontend; it is verified client-side (JS comparison)
5. On success, the registration or password update is submitted

---

## 🚀 Getting Started

See [SETUP.md](./SETUP.md) for full installation and configuration instructions.

---

## ⚠ Known Limitations

- **Deprecated MySQL Extension** — The project uses the legacy `mysql_*` PHP extension, which was removed in PHP 7.0+. A web server running **PHP 5.6** (or XAMPP/WAMP with PHP 5.6) is required, or the codebase needs to be migrated to `mysqli_*` or PDO.
- **Client-Side OTP Verification** — OTP is currently validated on the browser side (JavaScript). For production use, this should be moved to a server-side check.
- **MD5 Passwords** — Passwords are hashed with MD5, which is cryptographically weak. Migration to `password_hash()` / `password_verify()` (bcrypt) is strongly recommended for any production deployment.
- **SQL Injection Risk** — Direct string interpolation is used in SQL queries. Prepared statements (PDO/MySQLi) should be adopted for security.

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

> Built with ❤️ as an academic project to demonstrate full-stack PHP development with role-based access control, email integration, and multi-step form workflows.
