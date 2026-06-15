# 🎓 EduLyntrixCoreX — Unified College Management System

[![Live Demo](https://img.shields.io/badge/Live%20Demo-Online-brightgreen?style=flat-square)](https://edulyntrixcorex.rf.gd)
[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat-square&logo=mysql)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat-square&logo=bootstrap)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-Educational-blue?style=flat-square)](#license)

> A secure, role-based College ERP system built with PHP & MySQL — covering attendance, fees, results, timetables, and academic administration across four dedicated portals.

🌐 **Live Demo:** [https://edulyntrixcorex.rf.gd](https://edulyntrixcorex.rf.gd)

---

## 📌 Overview

EduLyntrixCoreX is a full-stack educational management platform built to digitize and automate college operations. Developed as a final-year BCA project, it demonstrates production-level backend practices including PDO prepared statements, bcrypt password hashing, session fixation prevention, and role-based access control — implemented across a normalized 17-table MySQL schema.

The system was built by a team of 3, led end-to-end over 4 months using Git/GitHub for version control and task coordination.

---

## ✨ Key Features

### 🔐 Security (Beyond Standard Requirements)
- PDO Prepared Statements — SQL injection prevention
- bcrypt Password Hashing — industry-standard credential storage
- Session Fixation Prevention — regenerates session ID on login
- XSS Output Escaping — sanitized across all 4 portals
- Role-Based Access Control — strict per-role data isolation

### 👤 Student Portal
- Secure registration & login
- Attendance tracking & leave requests
- Fee management & payment status
- Timetable access & academic reports
- Profile & password management

### 👨‍🏫 Faculty Portal
- Attendance marking & management
- Academic monitoring & timetable
- Department operations

### 🏛️ HOD Dashboard
- Department overview & faculty deployment
- Enrollment & attendance monitoring
- Leave request processing
- Timetable management & system status

### ⚙️ Admin Panel
- User, role & department management
- Academic session & grade management
- Student & staff records
- Reports & analytics

---

## 🛠️ Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP (OOP, PDO, Session Management) |
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5 |
| Database | MySQL (17 tables, normalized schema) |
| Version Control | Git & GitHub |
| Deployment | InfinityFree Hosting |

---

## 🗄️ Database Architecture

17 interconnected tables covering the full academic lifecycle:

`students` · `staff` · `departments` · `attendance` · `grades` · `academic_sessions` · `timetable` · `leave_requests` · `users` · `roles` · `fees` · `results` · `subjects` · `enrollments` · `notices` · `profile_images` · `audit_logs`

---

## 📁 Project Structure

```
edulyntrixcorex/
│
├── assets/              # CSS, JS, images
├── corex_root/          # Admin portal modules
├── includes/            # Shared config, DB connection, helpers
├── public/              # Student, Faculty, HOD portal modules
├── uploads/
│   └── profiles/        # User profile images
└── README.md
```

---

## ⚙️ Local Setup

### Prerequisites
- XAMPP (Apache + MySQL)
- PHP 7.4+
- Git

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/abhayrana0708/edulyntrixcorex.git

# 2. Move to XAMPP's web root
mv edulyntrixcorex /path/to/xampp/htdocs/

# 3. Start Apache and MySQL in XAMPP Control Panel

# 4. Create database and import schema
#    Open phpMyAdmin → create DB → import SQL file

# 5. Configure database credentials
#    Edit: includes/db_connect.php
```

```php
// includes/db_connect.php
$host = 'localhost';
$dbname = 'edulyntrixcorex';
$username = 'root';
$password = '';
```

```
# 6. Open in browser
http://localhost/edulyntrixcorex
```

---

## 🌍 Deployment

Deployed on **InfinityFree** free hosting.

Steps followed:
1. Database export from local XAMPP → import to remote MySQL
2. PHP configuration for production environment
3. File upload via hosting control panel
4. GitHub integration for version tracking
5. End-to-end production testing across all 4 portals

---

## 🧪 Testing

- **24 test cases** executed across core modules (Student, Faculty, Attendance)
- Covered: login flows, role isolation, SQL edge cases, form validation, session handling
- All critical bugs identified and resolved before university submission

---

## 👨‍💻 Author

**Abhay Rana** — BCA Final Year (2026), Gautam College Hamirpur, HPU

[![LinkedIn](https://img.shields.io/badge/LinkedIn-Connect-0A66C2?style=flat-square&logo=linkedin)](https://www.linkedin.com/in/abhay-rana-b7b07a288)
[![GitHub](https://img.shields.io/badge/GitHub-Profile-181717?style=flat-square&logo=github)](https://github.com/abhayrana0708)

**Stack:** PHP · MySQL · JavaScript · HTML5 · CSS3 · Bootstrap · Git

---

## 📄 License

Developed for educational and portfolio purposes. Feel free to reference the architecture or implementation patterns — attribution appreciated.
