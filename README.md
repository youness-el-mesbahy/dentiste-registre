# Dentiste Registre

Dentiste Registre is a **PHP + MySQL** web application for managing a dental practice:

- Patient registry
- Consultations history
- Appointments scheduling
- Patient document uploads
- Basic role-based access (`dentiste`, `assistant`, `admin`)
- Visitor/guest analytics tracking

> The UI is built with Bootstrap assets located under `assets/`.

## Tech stack

- **Backend**: PHP (no framework)
- **Database**: MySQL / MariaDB
- **DB access**: `mysqli`
- **Frontend**: HTML + Bootstrap (bundled in `assets/`)

## Project structure (high level)

- `index.php`
  - Entry point; redirects to `login.php` if not authenticated, otherwise redirects to `dashboard.php`.
- `login.php`
  - Authentication (session-based) + CSRF token usage.
- `dashboard.php`
  - Main dashboard and statistics.
- `patients-list.php`, `add-patient.php`, `patient-dashboard.php`
  - Patient listing / creation / details.
- `appointments-list.php`
  - Appointment listing.
- `upload-document.php`
  - Upload patient documents.
- `user-management.php`
  - Admin-only user CRUD.
- `guest-analytics.php`
  - Activity analytics (guest/user actions).
- `includes/`
  - Feature modules and utilities (auth, validation, CSRF, dashboard widgets, etc.).
- `db/`
  - `tables.sql`: schema only
  - `db.sql`: schema + sample data

## Requirements

- PHP 7.4+ (8.x recommended)
- MySQL or MariaDB
- A web server (Apache/Nginx) **or** PHP built-in server for local dev

## Database setup

### 1) Create the database

The app is configured by default to use:

- **Database name**: `mediregister`

Create it first:

```sql
CREATE DATABASE mediregister CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

### 2) Import schema (and optionally seed data)

- **Schema only**:

```bash
mysql -u root -p mediregister < db/tables.sql
```

- **Schema + sample data**:

```bash
mysql -u root -p mediregister < db/db.sql
```

## Configuration

Database connection is configured in `connection.php`:

- `$host = "localhost"`
- `$user = "root"`
- `$pass = ""` (empty)
- `$dbname = "mediregister"`

Update these values to match your local environment.

## Run locally

### Option A: PHP built-in server (quick start)

From the project root:

```bash
php -S localhost:8000
```

Then open:

- `http://localhost:8000/`

### Option B: Apache/Nginx

1. Point your vhost/document-root to the repository folder.
2. Make sure PHP and MySQL extensions are enabled.
3. Open the site in your browser.

## Authentication & roles

Authentication is session-based.

Roles in the database schema:

- `dentiste`
- `assistant`
- `admin`

Role helpers are located in `includes/auth/auth.php`.

### Demo login credentials

To quickly test the application, use these credentials:

- **Username**: `demo@dentisteregistre.com`
- **Password**: `123456`

> **⚠️ Security Warning**: These are demo credentials only. Change or remove this account before deploying to production.

### Demo / seed users

If you imported `db/db.sql`, it inserts sample users into `users`.

Additionally, there are helper scripts:

- `includes/users/create-admin-user.php`
- `includes/users/create-test-user.php`

Open those scripts in the browser once (after configuring DB) to create test accounts.

## Features

- **Patients**
  - Add and list patients
  - Patient profile page with related data
- **Consultations**
  - Create and list consultations (stored in `consultations`)
- **Appointments**
  - Schedule appointments and view them (stored in `appointments`)
- **Documents**
  - Upload documents linked to a patient (stored in `patient_documents`)
- **User management**
  - Admin-only user CRUD (`user-management.php`)
- **Analytics**
  - Guest tracking stored in `guests` and `guest_activities`

## Uploads

Uploaded files are stored under:

- `uploads/patients/`

Make sure the web server has write permissions to the `uploads/` directory.

## Security notes

A security review is available in `security_analysis.md`.

Important notes:

- `db/db.sql` contains sample data and may include **insecure** credentials.
- Review file upload handling (`upload-document.php`) before production.
- Review for SQL injection risks where queries are not parameterized.

## Troubleshooting

- **Blank page / 500 error**
  - Check PHP error logs and ensure required extensions are enabled (`mysqli`).
- **Database connection failed**
  - Verify credentials in `connection.php` and that MySQL is running.
- **Permissions error when uploading**
  - Ensure `uploads/` is writable by the web server user.
