# Training Exam System

A multi-organization online training assessment and certification exam system built with PHP, MySQL, and Bootstrap.

## Features

- **Multi-Organization Support** — manage multiple organizations, each with their own question banks
- **Admin Dashboard** — manage organizations, question banks, questions, participants, and results
- **Participant Registration** — public registration with IC uniqueness validation
- **Timed Exams** — timed multiple-choice assessments with randomized question order
- **Auto-Scoring** — automatic scoring with fail/pass/excellent classification
- **Result Export** — CSV export of exam results

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8+, PDO, MySQL |
| Frontend | HTML, CSS, Bootstrap 5, Vanilla JS |
| Architecture | MVC-like (no framework) |
| Local Dev | Laragon |
| Deployment | cPanel |

## Folder Structure

```
training-exam-system/
├── admin/           # Admin entry points
├── assets/          # CSS, JS, images
│   ├── css/
│   ├── js/
│   └── img/
├── config/          # Database & app configuration
├── controllers/     # Request handlers
├── database/        # SQL schema & seed scripts
├── helpers/         # Utility functions
├── models/          # Database interaction (PDO)
├── storage/         # Exports, logs (gitignored contents)
├── views/           # HTML templates
│   ├── layout/      # Shared headers/footers
│   ├── admin/       # Admin page templates
│   └── public/      # Participant page templates
├── .gitignore
├── .htaccess        # Security & directory protection
├── index.php        # Public landing page
├── init.php         # App bootstrap (session, config, helpers)
└── README.md
```

## Local Setup (Laragon)

### Prerequisites
- [Laragon](https://laragon.org/) installed with PHP 8+ and MySQL
- HeidiSQL (included with Laragon) or phpMyAdmin

### Steps

1. **Clone the repository**
   ```bash
   cd C:\laragon\www
   git clone https://github.com/mirzadham/training-exam-system.git
   ```

2. **Create the database**
   - Open HeidiSQL and connect to your local MySQL
   - Run `database/schema.sql` to create the database and all tables

3. **Configure database credentials** (if needed)
   - Default config assumes Laragon defaults (`root` / no password)
   - To override, create `config/database.local.php` (gitignored):
     ```php
     <?php
     define('DB_HOST', '127.0.0.1');
     define('DB_NAME', 'training_exam_system');
     define('DB_USER', 'root');
     define('DB_PASS', 'your_password');
     define('DB_CHARSET', 'utf8mb4');
     require_once __DIR__ . '/../config/database.php'; // load getDBConnection()
     ```

4. **Start Laragon**
   - Make sure Apache and MySQL are running

5. **Access the application**
   - Public: `http://training-exam-system.test/` or `http://localhost/training-exam-system/`
   - Admin: `http://training-exam-system.test/admin/` or `http://localhost/training-exam-system/admin/`

### Default Admin Account
The first admin account is created via a seed script (see `database/seed_admin.php`). This will be available after Phase 2.

## Scoring Rules

| Score | Classification |
|-------|---------------|
| Below 50% | Fail |
| 50% – 80% | Pass |
| Above 80% | Excellent |

## Deployment (cPanel)

Deployment notes will be added during Phase 9 (Hardening & Cleanup).

## License

Private project. All rights reserved.
