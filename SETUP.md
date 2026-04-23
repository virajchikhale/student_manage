# ⚙️ Setup Guide — Student Management System

This guide walks you through setting up the **Student Management System** on your local machine using XAMPP (or any equivalent AMP stack that ships with **PHP 5.6**).

> ⚠️ **Important:** This project uses the legacy `mysql_*` PHP extension, which was removed in PHP 7.0. You **must** use PHP 5.6 to run it without code changes.

---

## 📋 Prerequisites

| Requirement     | Version / Notes                                      |
|-----------------|------------------------------------------------------|
| **PHP**         | 5.6.x (required for `mysql_*` extension)            |
| **MySQL**       | 5.x or MariaDB equivalent                            |
| **Web Server**  | Apache (via XAMPP 5.6 / WAMP / LAMP)                 |
| **XAMPP**       | [XAMPP 5.6.40](https://sourceforge.net/projects/xampp/files/XAMPP%20Windows/5.6.40/) (recommended) |
| **Gmail Account** | With an [App Password](https://support.google.com/accounts/answer/185833) enabled for SMTP |

---

## 🔧 Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/<your-username>/student_manage.git
```

Place the cloned folder inside your web server's document root:

- **XAMPP (Windows):** `C:\xampp\htdocs\student_manage`
- **XAMPP (macOS):** `/Applications/XAMPP/htdocs/student_manage`
- **LAMP (Linux):** `/var/www/html/student_manage`

---

### 2. Start Your Local Server

Launch **XAMPP Control Panel** and start:
- ✅ **Apache**
- ✅ **MySQL**

---

### 3. Create the Database

1. Open your browser and go to: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **New** in the left sidebar
3. Name the database: **`student_management`**
4. Click **Create**

---

### 4. Import the Database Schema

You need to create the required tables. Run the following SQL in the **phpMyAdmin SQL tab** (select the `student_management` database first):

```sql
-- Departments
CREATE TABLE `department` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0=available, 1=assigned',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Admin
CREATE TABLE `admin_reg` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Principal
CREATE TABLE `principal_reg` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- HOD (Head of Department)
CREATE TABLE `hod_reg` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `report_to` INT(11) DEFAULT NULL,
  `department_id` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Teacher
CREATE TABLE `teacher_reg` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `report_to` INT(11) DEFAULT NULL,
  `department_id` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

#### Seed Sample Departments (Optional)

```sql
INSERT INTO `department` (`name`, `status`) VALUES
('Computer Science', '0'),
('Information Technology', '0'),
('Electronics', '0'),
('Mechanical Engineering', '0');
```

---

### 5. Configure the Database Connection

Open the file:

```
includes/connection.php
```

Verify the credentials match your local MySQL setup:

```php
<?php
$con = mysql_connect("localhost", "root", "");
// ↑ host           ↑ username   ↑ password (leave blank for default XAMPP)

mysql_select_db("student_management", $con);
?>
```

> If you've set a MySQL root password in XAMPP, replace the empty string `""` with your password.

---

### 6. Configure the Email (SMTP) Settings

Open the file:

```
email/variables.php
```

Update the following lines with your Gmail credentials:

```php
$mail_id  = "your_email@gmail.com";
$password = "your_app_password_here";
```

> **How to get a Gmail App Password:**
> 1. Go to your Google Account → Security
> 2. Enable **2-Step Verification**
> 3. Go to **App Passwords** → generate one for "Mail"
> 4. Use that 16-character password above

Also open `email/email_base.php` and verify the SMTP settings point to:
- **Host:** `smtp.gmail.com`
- **Port:** `587`
- **Encryption:** `TLS`

---

### 7. Access the Application

Open your browser and navigate to:

| Portal              | URL                                                             |
|---------------------|-----------------------------------------------------------------|
| **Admin Login**     | `http://localhost/student_manage/admin/`                        |
| **Admin Register**  | `http://localhost/student_manage/admin/register.php`            |
| **HOD Login**       | `http://localhost/student_manage/login/hod_login.php`           |
| **HOD Register**    | `http://localhost/student_manage/registration/hod_reg.php`      |
| **Principal Login** | `http://localhost/student_manage/login/principal_reg.php`       |
| **Principal Register** | `http://localhost/student_manage/registration/principal_reg.php` |
| **Teacher Login**   | `http://localhost/student_manage/login/teacher_reg.php`         |
| **Teacher Register**| `http://localhost/student_manage/registration/teacher_reg.php`  |

---

## 🔑 First-Time Registration Order

Follow this order when setting up the system for the first time:

```
1. Register an Admin          → admin/register.php
2. Register a Principal       → registration/principal_reg.php
3. Register HODs              → registration/hod_reg.php
                                (select a department and report to a Principal)
4. Register Teachers          → registration/teacher_reg.php
                                (select a department; they will auto-report to the HOD of that department)
```

> **OTP Step:** During registration, an OTP will be sent to the email address provided. You must enter this OTP to proceed.

---

## 🛠 Troubleshooting

| Issue | Solution |
|-------|----------|
| **White screen / PHP error** | Ensure you're using PHP 5.6. Check `php.ini` and confirm `extension=mysql.so` is enabled. |
| **`mysql_connect(): No such file`** | PHP version is ≥ 7.0. Switch XAMPP to PHP 5.6 via the config. |
| **OTP email not received** | Check spam folder. Verify App Password is correct. Ensure `Less secure app access` or App Passwords are configured on the Gmail account. |
| **Database connection failed** | Confirm MySQL is running and `connection.php` credentials are correct. |
| **HOD can't find departments** | Make sure you have inserted rows into the `department` table with `status = '0'`. |
| **Blank dashboard stats** | Log in as Admin first. Ensure session is active (`session_start()` is called). |

---

## 📁 Folder Permissions

On Linux/macOS, ensure the web server has read access to all project files:

```bash
chmod -R 755 /var/www/html/student_manage
```

---

## 🧪 Testing the Email Flow Locally

If you don't want to use real SMTP during development, you can use [Mailtrap](https://mailtrap.io/) as a fake SMTP inbox:

1. Sign up at [mailtrap.io](https://mailtrap.io/)
2. Get your SMTP credentials from the inbox settings
3. Update `email/email_base.php` with your Mailtrap host, port, username, and password

---

> For a full feature overview and architecture details, refer to [README.md](./README.md).
