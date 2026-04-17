<div align="center">

<img src="public/adminlogo2.jpeg" alt="DiscussGo Logo" width="100" height="100" style="border-radius: 50%;" />

# 💬 DiscussGo

**Real discussions. Real people. Real solutions.**

A community-driven Q&A platform where users ask questions, share knowledge, and engage in meaningful discussions.

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![AdminLTE](https://img.shields.io/badge/AdminLTE-3.x-3C8DBC?style=for-the-badge&logo=adminlte&logoColor=white)](https://adminlte.io)

</div>

---

## ✨ Features

### 👤 User Side
- 🔐 **Authentication** — Signup, login, and logout with session management
- ❓ **Ask Questions** — Post questions with category tags and rich descriptions
- 💬 **Answer & Discuss** — Reply to questions, edit or delete your own answers
- 👍 **Like System** — Like/unlike answers with live like counts
- 🔍 **Search & Filter** — Search questions by title, filter by category
- 🧑 **Profile Management** — Update username, city, and profile picture
- 📬 **Contact Form** — Reach out via the built-in contact page
- 🕐 **Latest Questions** — Dedicated feed for the most recent activity

### 🛠️ Admin Panel
- 📊 **Dashboard** — Live stats: total questions, users, categories, and answers
- 👥 **User Management** — Search, block, or remove users
- 📋 **Question Management** — Filter by category or user, delete content
- 💡 **Answer Management** — View all answers or drill down by question
- 🗂️ **Category CRUD** — Create, edit, and delete categories inline

---

## 🗂️ Project Structure

```
discussgo/
├── 📁 admin/                  # Admin panel (AdminLTE)
│   ├── index.php              # Dashboard with stats
│   ├── login.php              # Admin authentication
│   ├── manage_users.php       # User management
│   ├── manage_questions.php   # Question management
│   ├── manage_answers.php     # Answer management
│   ├── manage_categories.php  # Category CRUD
│   └── includes/              # Shared header/footer
│
├── 📁 client/                 # User-facing views
│   ├── questions.php          # Question listing
│   ├── question_details.php   # Single question view
│   ├── answers.php            # Answers display
│   ├── profile.php            # User profile page
│   ├── login.php              # Login form
│   └── signup.php             # Registration form
│
├── 📁 common/                 # Shared utilities
│   ├── db.php                 # Database connection
│   └── time_ago_function.php  # Human-readable timestamps
│
├── 📁 server/
│   └── request.php            # Central request handler (all POST/GET logic)
│
├── 📁 public/                 # Static assets
│   ├── style.css              # Custom styles
│   ├── script.js              # Client-side JS (likes, interactions)
│   └── profiles/              # User uploaded profile images
│
└── index.php                  # Main entry point
```

---

## 🗄️ Database Schema

| Table | Description |
|---|---|
| `users` | Registered users with profile info |
| `questions` | Questions with title, description, category |
| `answers` | Answers linked to questions and users |
| `category` | Question categories |
| `answer_likes` | Junction table for answer likes |
| `contact_messages` | Messages from the contact form |
| `admins` | Admin credentials |

---

## 🚀 Getting Started

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) or any PHP + MySQL environment
- PHP 7.4+
- MySQL 5.7+

### Installation

**1. Clone the repository**
```bash
git clone https://github.com/parthnenvani-1506/DiscussGo-OnlineDiscussionQ-APlatform.git
```

**2. Move to your web server root**
```bash
# For XAMPP
cp -r discussgo/ C:/xampp/htdocs/discussgo
```

**3. Create the database**

Open phpMyAdmin and create a database named `discussgo`, then import the SQL file:
```bash
mysql -u root -p discussgo < discussgo.sql
```

**4. Configure the database connection**

Edit `common/db.php`:
```php
$host     = "localhost";   // or localhost:3307 for XAMPP
$username = "root";
$password = "";
$database = "discussgo";
```

**5. Run the app**

Visit: `http://localhost/discussgo`  
Admin panel: `http://localhost/discussgo/admin`

---

## 🖥️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP (procedural) |
| Database | MySQL via `mysqli` |
| Frontend | HTML5, CSS3, Bootstrap 5.3 |
| Admin UI | AdminLTE 3 |
| Icons | Font Awesome, Ionicons |
| JavaScript | Vanilla JS |

---

## ⚠️ Security Notice

> This project is intended for **educational/development purposes**.  
> Before deploying to production, consider:
> - Replacing MD5 password hashing with `password_hash()` / `password_verify()`
> - Using prepared statements throughout the admin panel
> - Adding CSRF protection to all forms
> - Validating and sanitizing all user inputs server-side

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

<div align="center">

Made with ❤️ by [parthnenvani-1506](https://github.com/parthnenvani-1506)

</div>
