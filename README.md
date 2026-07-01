# 🐝 JobHive — PHP + MySQL Job Portal

A mini "Indeed" built with plain PHP 8 and MySQL (PDO). Includes authentication,
role-based authorization, job search with pagination, applications, saved jobs,
company dashboards, an admin approval panel, resume uploads and email notifications.

## Features

| Role | Can do |
|------|--------|
| **Job Seeker** | Register/login, edit profile, upload resume, search & filter jobs, apply, save jobs, track applications |
| **Company** | Dashboard, post jobs, edit/delete jobs, view & shortlist applicants |
| **Admin** | Approve/reject jobs, delete users, view site stats |

Concepts covered: **Authentication** (bcrypt + sessions), **Authorization** (role guards),
**Relationships & Joins** (users ⇄ jobs ⇄ applications ⇄ saved_jobs), **Search**,
**Pagination**, and **Email** (notifications on apply/approval).

## Requirements
- PHP 8.0+ with `pdo_mysql`
- MySQL / MariaDB (XAMPP works out of the box)

## Setup (localhost:8000)

1. **Start MySQL** (XAMPP Control Panel → MySQL → Start).

2. **Import the database.** Either:
   - phpMyAdmin → Import → choose `database.sql`, **or**
   - Command line:
     ```
     C:\xampp\mysql\bin\mysql.exe -u root < database.sql
     ```

3. **Run the app** with PHP's built-in server from the project folder:
   ```
   C:\xampp\php\php.exe -S localhost:8000
   ```

4. Open **http://localhost:8000**

### DB credentials
Defaults match XAMPP (host `127.0.0.1`, user `root`, empty password, db `job_portal`).
Change them in [`config/config.php`](config/config.php) if yours differ.

## Demo accounts
Password for all: **`password123`**

| Role | Email |
|------|-------|
| Admin | `admin@jobportal.test` |
| Company | `hr@acme.test` |
| Seeker | `ayesha@seeker.test` |

## Email
On a plain XAMPP install outbound mail isn't configured, so every notification is
appended to `storage/mail.log` (and also attempts real `mail()` delivery). Open that
file to see application/approval emails.

## Project structure
```
config/       DB connection & settings
includes/     header, footer, helpers, mailer, job card
auth/         register, login, logout
jobs/         browse (search+pagination), view, apply, save
user/         profile (+resume), applications, saved
company/      dashboard, post/edit job, applicants
admin/        dashboard, approve jobs, manage users
assets/css/   styling
uploads/      resumes
database.sql  schema + seed
```

## Deploying online
Vercel does not run PHP + MySQL persistently. Easiest free hosts for this stack:
**InfinityFree**, **000webhost**, or **Railway** (MySQL plugin). Upload the files,
import `database.sql`, and set the DB credentials in `config/config.php`.
