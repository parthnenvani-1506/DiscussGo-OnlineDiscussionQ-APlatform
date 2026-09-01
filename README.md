# DiscussHub 🚀

> **Intelligent Community Q&A & Discussion Platform**  
> Built with Laravel 11 · MySQL 8 · Bootstrap 5 · Vanilla JS · Local Ollama LLM (`llama3.2:3b`)

---

## 🌟 Overview & Architecture

**DiscussHub** is a modernized, MCA-level developer community and discussion platform. It evolves standard Q&A forums with integrated, privacy-focused local AI models, mathematical TF-IDF semantic vector search, live duplicate detection, and automated solution synthesis.

### Key Capabilities:
- 🤖 **Local AI Engine**: Integrated with local Ollama (`llama3.2:3b`) for zero-cost, privacy-first answer summaries & moderation with smart heuristic fallbacks.
- 🔍 **Real-Time Duplicate Prevention**: TF-IDF cosine similarity analyzes questions on-the-fly as you type to prevent duplicate threads.
- 🏷️ **AI Semantic Tag Extraction**: Automatically recommends relevant taxonomy tags based on question title & code description.
- 📊 **Real-time Quality Scorer**: Dynamic evaluation meter giving developers instant feedback on phrasing, code blocks, and detail before submitting.
- ⭐ **Gamified Reputation & Badges**: Tiered developer leveling (`newcomer` → `contributor` → `experienced` → `expert` → `mentor`), automatic badge unlocking, and points audit trail.
- 🛡️ **Comprehensive Admin Center**: Complete administrative control with live telemetry, Chart.js analytics, user moderation, tag merging, and audit trail logging.
- 🌓 **Dark & Light Mode**: Custom design system with instant theme switching and responsive support down to 375px.

---

## 🛠️ Technology Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Database**: MySQL 8 (Fulltext Boolean Indexing, JSON columns, Foreign Key cascades)
- **AI / NLP**: Ollama (`llama3.2:3b`), Custom PHP TF-IDF Cosine Similarity Engine
- **Frontend**: Blade Components, Bootstrap 5, Bootstrap Icons, Custom CSS Design Tokens, Vanilla JS (Fetch API)
- **Charts**: Chart.js 4.x

---

## 📁 Database Schema & Models

DiscussHub features 17 relational and polymorphic tables:
1. `users`: Multi-level reputation, bio, avatar, suspended flag, legacy migration flags.
2. `categories`: Discussion domains with custom color codes and icon classes.
3. `tags`: Taxonomy tags with automated usage count tracking.
4. `questions`: Full-text searchable discussions with view counts, AI summary cache, and pinned state.
5. `question_tag`: Pivot relationship table.
6. `answers`: Community solutions with accepted flag (`is_accepted`) and author references.
7. `votes`: Polymorphic voting (+1 / -1) on questions and answers with denormalized score caching.
8. `bookmarks`: Personal user reading lists.
9. `notifications`: JSON-driven activity alerts (answer posted, solution accepted, upvote received, badge earned).
10. `badges`: Achievement milestones with criteria strings.
11. `user_badges`: Awarded user achievements.
12. `reputation_transactions`: Immutable log of points awarded/deducted.
13. `reports`: Polymorphic user reporting queue with administrative dismiss/delete actions.
14. `audit_logs`: Immutable security audit trail of all administrative actions.
15. `ai_requests`: Performance and latency telemetry for local AI model calls.
16. `contact_messages`: Inquiries and support feedback.
17. `admins`: Separate administrative authentication table with bcrypt security.

---

## 🚀 Getting Started & Installation

### 1. Prerequisites
- **PHP** >= 8.2 with PDO, cURL, MBString extensions
- **MySQL** >= 8.0 (or MariaDB 10.4+)
- **Composer** >= 2.x
- *(Optional for AI)* **Ollama** installed locally with `llama3.2:3b`

### 2. Setup Environment
```bash
# Clone or open the repository
cd c:/xampp/htdocs/DiscussHub

# Install Composer dependencies
composer install

# Configure Environment
cp .env.example .env
```

Ensure your `.env` contains your database configuration:
```env
APP_NAME="DiscussHub"
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=DiscussHub_db
DB_USERNAME=root
DB_PASSWORD=

OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_MODEL=llama3.2:3b
```

### 3. Database Migration & Seeding
```bash
# Run fresh migrations and seed initial categories, tags, badges, and admin user
php artisan migrate --seed
```

### 4. Running Local AI (Optional)
To enable local LLM summarization and AI moderation:
```bash
# In a separate terminal
ollama run llama3.2:3b
```
*(Note: If Ollama is not running, the application automatically falls back to heuristic extraction without any downtime.)*

### 5. Start Development Server
```bash
php artisan serve
```
Visit the application at: **`http://localhost:8000`**

---

## 🔑 Default Credentials

### Admin Control Center
- **URL**: `http://localhost:8000/admin/login`
- **Email**: `admin@discusshub.ai`
- **Password**: `admin123`

### Moderator Account
- **Email**: `moderator@discusshub.ai`
- **Password**: `mod123456`

---

## 🧪 Automated Testing

Run the automated test suite verifying all core feature domains:
```bash
php artisan test
```

---

## 📄 License
This project is open-source software built for educational and portfolio demonstration.
