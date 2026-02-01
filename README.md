## HRMS (PHP + MySQL) — WAMP-ready

### 1) Create the database
- Open phpMyAdmin
- Create database: `hrms`
- Import `database/schema.sql`

### 2) Configure DB credentials
Edit `config/config.php` and set:
- DB host, name, username, password

### 3) Run in browser
Open:
- `http://localhost/php program/hproject/public/`

### 4) First-time usage
- Sign up as **Employee** (or HR).
- Email verification: this demo app **shows the verification link on screen** (no real email sending yet).
- After verifying, you can sign in.

### Notes
- Passwords are stored using `password_hash()`.
- This is a starter implementation you can extend (real email sending, reports, salary slips, etc.).
