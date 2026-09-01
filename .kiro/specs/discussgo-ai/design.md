# DiscussHub — System Design Document

## Architecture Overview

```
┌──────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                          │
│         Bootstrap 5 + Custom CSS + Vanilla JS                │
│              Quill.js (editor) · Chart.js (charts)           │
└──────────────────────────────┬───────────────────────────────┘
                               │ HTTP
┌──────────────────────────────▼───────────────────────────────┐
│                      LARAVEL 11 (MVC)                        │
│                                                              │
│  Routes → Middleware → Controllers → Services → Models       │
│                                                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │                HTTP Middleware Stack                  │   │
│  │  AuthMiddleware · AdminMiddleware · ModMiddleware     │   │
│  │  RateLimitMiddleware · CsrfMiddleware                 │   │
│  │  CheckSuspended · CheckRole                          │   │
│  └─────────────────────────┬────────────────────────────┘   │
│                             │                                │
│  ┌──────────────────────────▼────────────────────────────┐  │
│  │                    Controllers                        │  │
│  │  Auth · Question · Answer · Profile · Like           │  │
│  │  Follow · Bookmark · Notification · Search           │  │
│  │  Tag · Category · Report · Home                      │  │
│  │  Admin/* · Moderator/*                               │  │
│  └──────────────────────────┬────────────────────────────┘  │
│                             │                                │
│  ┌──────────────────────────▼────────────────────────────┐  │
│  │                   Service Layer                       │  │
│  │  AIService · SimilarityService · TagExtractionService │  │
│  │  ReputationService · BadgeService                     │  │
│  │  NotificationService · RecommendationService         │  │
│  │  ModerationService · FollowService                   │  │
│  └──────────────────────────┬────────────────────────────┘  │
│                             │                                │
│  ┌──────────────────────────▼────────────────────────────┐  │
│  │                 Eloquent ORM Models                   │  │
│  │  User · Question · Answer · Category · Tag            │  │
│  │  Like · Bookmark · Follow · Notification              │  │
│  │  Badge · Reputation · Report · ModerationAction      │  │
│  └──────────────────────────┬────────────────────────────┘  │
└────────────────────────────┬─────────────────────────────────┘
                             │
          ┌──────────────────┼──────────────┐
          │                  │              │
 ┌────────▼──────┐  ┌────────▼───┐  ┌──────▼──────────┐
 │  MySQL 8.x    │  │  Storage   │  │  Ollama Local   │
 │  Database     │  │  (uploads) │  │  llama3.2:3b    │
 └───────────────┘  └────────────┘  └─────────────────┘
```

---

## Role System

```
users.role ENUM:
  'user'       → standard user
  'moderator'  → can moderate content, cannot access admin settings
  'admin'      → full access (stored in separate admins table)
```

**Middleware:**
- `auth` → checks Laravel Auth session (users table)
- `admin` → checks admin session (admins table, separate auth)
- `moderator` → checks `Auth::user()->role === 'moderator' || role === 'admin'`
- `check.suspended` → checks `Auth::user()->is_suspended === true`

---

## Directory Structure

```
discussgo-ai/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   ├── LogoutController.php
│   │   │   │   └── PasswordResetController.php
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── QuestionController.php
│   │   │   │   ├── AnswerController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── TagController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── ContactController.php
│   │   │   │   ├── BadgeController.php
│   │   │   │   ├── AnalyticsController.php
│   │   │   │   ├── AuditLogController.php
│   │   │   │   ├── AICenterController.php
│   │   │   │   └── ModeratorController.php   ← appoint/remove moderators
│   │   │   ├── Moderator/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── QueueController.php        ← AI queue + report queue
│   │   │   │   ├── ActionController.php       ← remove, warn, suspend
│   │   │   │   └── HistoryController.php      ← own action log
│   │   │   ├── QuestionController.php
│   │   │   ├── AnswerController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── LikeController.php             ← replaces VoteController
│   │   │   ├── FollowController.php           ← NEW
│   │   │   ├── BookmarkController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── SearchController.php
│   │   │   ├── TagController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ReportController.php
│   │   │   └── HomeController.php
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php
│   │   │   ├── ModeratorMiddleware.php        ← NEW
│   │   │   └── CheckSuspended.php
│   │   └── Requests/
│   │       ├── Auth/
│   │       │   ├── LoginRequest.php
│   │       │   └── RegisterRequest.php
│   │       ├── StoreQuestionRequest.php
│   │       ├── UpdateQuestionRequest.php
│   │       ├── StoreAnswerRequest.php
│   │       ├── UpdateAnswerRequest.php
│   │       ├── UpdateProfileRequest.php
│   │       └── Moderator/
│   │           ├── RemoveContentRequest.php   ← reason required
│   │           └── WarnUserRequest.php        ← reason required
│   ├── Models/
│   │   ├── User.php
│   │   ├── Question.php
│   │   ├── Answer.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── Like.php                           ← replaces Vote model
│   │   ├── Follow.php                         ← NEW
│   │   ├── Bookmark.php
│   │   ├── Notification.php
│   │   ├── Badge.php
│   │   ├── UserBadge.php
│   │   ├── ReputationTransaction.php
│   │   ├── Report.php
│   │   ├── ModerationAction.php               ← NEW
│   │   ├── UserWarning.php                    ← NEW
│   │   ├── AuditLog.php
│   │   ├── AiRequest.php
│   │   ├── ContactMessage.php
│   │   └── Admin.php
│   ├── Services/
│   │   ├── AIService.php
│   │   ├── SimilarityService.php
│   │   ├── TagExtractionService.php
│   │   ├── TagMergeService.php                ← NEW: duplicate tag/category detection
│   │   ├── ReputationService.php
│   │   ├── BadgeService.php
│   │   ├── NotificationService.php
│   │   ├── RecommendationService.php
│   │   ├── ModerationService.php
│   │   └── FollowService.php
│   └── Policies/
│       ├── QuestionPolicy.php
│       └── AnswerPolicy.php
├── database/
│   └── migrations/
│       ├── 001_create_users_table.php
│       ├── 002_create_categories_table.php
│       ├── 003_create_tags_table.php
│       ├── 004_create_questions_table.php
│       ├── 005_create_question_tag_table.php
│       ├── 006_create_answers_table.php
│       ├── 007_create_likes_table.php         ← replaces votes
│       ├── 008_create_follows_table.php        ← NEW
│       ├── 009_create_bookmarks_table.php
│       ├── 010_create_notifications_table.php
│       ├── 011_create_badges_table.php
│       ├── 012_create_user_badges_table.php
│       ├── 013_create_reputation_transactions_table.php
│       ├── 014_create_reports_table.php
│       ├── 015_create_moderation_actions_table.php  ← NEW
│       ├── 016_create_user_warnings_table.php        ← NEW
│       ├── 017_create_audit_logs_table.php
│       ├── 018_create_ai_requests_table.php
│       └── 019_create_contact_messages_table.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php
│       │   ├── admin.blade.php
│       │   ├── moderator.blade.php            ← NEW
│       │   └── auth.blade.php
│       ├── partials/
│       │   ├── navbar.blade.php
│       │   ├── footer.blade.php
│       │   ├── flash-messages.blade.php
│       │   ├── question-card.blade.php
│       │   └── answer-card.blade.php
│       ├── home/
│       │   └── index.blade.php
│       ├── questions/
│       │   ├── index.blade.php
│       │   ├── show.blade.php
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       ├── auth/
│       │   ├── login.blade.php
│       │   ├── register.blade.php
│       │   └── forgot-password.blade.php
│       ├── profile/
│       │   ├── show.blade.php
│       │   └── edit.blade.php
│       ├── follows/
│       │   ├── followers.blade.php            ← NEW
│       │   └── following.blade.php            ← NEW
│       ├── search/
│       │   └── results.blade.php
│       ├── bookmarks/
│       │   └── index.blade.php
│       ├── notifications/
│       │   └── index.blade.php
│       ├── tags/
│       │   └── show.blade.php
│       ├── categories/
│       │   ├── index.blade.php                ← grid of category cards with images
│       │   └── show.blade.php
│       ├── moderator/                         ← NEW
│       │   ├── dashboard.blade.php
│       │   ├── ai-queue.blade.php
│       │   ├── report-queue.blade.php
│       │   └── history.blade.php
│       └── admin/
│           ├── dashboard.blade.php
│           ├── users/
│           ├── questions/
│           ├── answers/
│           ├── categories/
│           │   ├── index.blade.php
│           │   └── form.blade.php             ← includes image upload
│           ├── tags/
│           ├── reports/
│           ├── contact/
│           ├── badges/
│           ├── analytics/
│           ├── audit-logs/
│           ├── ai-center/
│           └── moderators/                    ← NEW
│               └── index.blade.php
└── routes/
    ├── web.php
    └── api.php
```

---

## Database Design

### Table: users
```sql
CREATE TABLE users (
    id                      BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_name               VARCHAR(50) NOT NULL,
    email                   VARCHAR(191) NOT NULL UNIQUE,
    password                VARCHAR(255) NOT NULL,
    city                    VARCHAR(100) NULL,
    bio                     TEXT NULL,
    profile_image           VARCHAR(255) DEFAULT 'default_profile.png',
    role                    ENUM('user','moderator') DEFAULT 'user',
    reputation              INT UNSIGNED DEFAULT 0,
    level                   ENUM('newcomer','contributor','experienced','expert','mentor') DEFAULT 'newcomer',
    followers_count         INT UNSIGNED DEFAULT 0,
    following_count         INT UNSIGNED DEFAULT 0,
    is_suspended            BOOLEAN DEFAULT FALSE,
    suspended_until         TIMESTAMP NULL,
    warning_count           INT UNSIGNED DEFAULT 0,
    password_reset_required BOOLEAN DEFAULT FALSE,
    email_verified_at       TIMESTAMP NULL,
    remember_token          VARCHAR(100) NULL,
    created_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_reputation (reputation DESC),
    INDEX idx_role (role)
);
```

### Table: categories
```sql
CREATE TABLE categories (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL UNIQUE,
    slug            VARCHAR(100) NOT NULL UNIQUE,
    description     TEXT NULL,
    image           VARCHAR(255) NULL,          -- stored filename in storage/categories/
    question_count  INT UNSIGNED DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Note:** `color` and `icon` columns from v1 design are removed. Category identity is now expressed through an image.  
Category cards on explore page use the image as a background with a gradient overlay and category name on top.  
If no image is uploaded, a placeholder SVG with the first letter of the category name is shown.

### Table: tags
```sql
CREATE TABLE tags (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL UNIQUE,
    slug        VARCHAR(50) NOT NULL UNIQUE,
    description TEXT NULL,
    usage_count INT UNSIGNED DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usage (usage_count DESC)
);
```

### Table: questions
```sql
CREATE TABLE questions (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED NOT NULL,
    category_id         BIGINT UNSIGNED NOT NULL,
    title               VARCHAR(300) NOT NULL,
    slug                VARCHAR(355) NOT NULL UNIQUE,
    description         TEXT NOT NULL,
    view_count          INT UNSIGNED DEFAULT 0,
    like_count          INT UNSIGNED DEFAULT 0,     -- denormalized for performance
    answer_count        INT UNSIGNED DEFAULT 0,
    bookmark_count      INT UNSIGNED DEFAULT 0,
    is_answered         BOOLEAN DEFAULT FALSE,
    accepted_answer_id  BIGINT UNSIGNED NULL,
    ai_summary          TEXT NULL,
    ai_summary_at       TIMESTAMP NULL,
    ai_summary_answer_count INT UNSIGNED DEFAULT 0, -- count when summary was generated
    is_flagged          BOOLEAN DEFAULT FALSE,
    ai_flag_score       FLOAT NULL,
    ai_flag_reason      VARCHAR(255) NULL,
    is_featured         BOOLEAN DEFAULT FALSE,
    is_removed          BOOLEAN DEFAULT FALSE,       -- soft delete by moderator
    removed_by          BIGINT UNSIGNED NULL,
    removed_at          TIMESTAMP NULL,
    removed_reason      TEXT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_user (user_id),
    INDEX idx_category (category_id),
    INDEX idx_likes (like_count DESC),
    INDEX idx_created (created_at DESC),
    INDEX idx_flagged (is_flagged),
    FULLTEXT idx_search (title, description)
);
```

### Table: question_tag (pivot)
```sql
CREATE TABLE question_tag (
    question_id BIGINT UNSIGNED NOT NULL,
    tag_id      BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (question_id, tag_id),
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);
```

### Table: answers
```sql
CREATE TABLE answers (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id     BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,
    answer          TEXT NOT NULL,
    like_count      INT UNSIGNED DEFAULT 0,     -- denormalized
    is_accepted     BOOLEAN DEFAULT FALSE,
    is_ai_generated BOOLEAN DEFAULT FALSE,      -- true if drafted by AI Generate Answer
    is_flagged      BOOLEAN DEFAULT FALSE,
    ai_flag_score   FLOAT NULL,
    ai_flag_reason  VARCHAR(255) NULL,
    is_removed      BOOLEAN DEFAULT FALSE,
    removed_by      BIGINT UNSIGNED NULL,
    removed_at      TIMESTAMP NULL,
    removed_reason  TEXT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_question (question_id),
    INDEX idx_likes (like_count DESC),
    INDEX idx_flagged (is_flagged)
);
```

### Table: likes (polymorphic — replaces votes)
```sql
CREATE TABLE likes (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    likeable_type   VARCHAR(50) NOT NULL,    -- 'question' or 'answer'
    likeable_id     BIGINT UNSIGNED NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (user_id, likeable_type, likeable_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_likeable (likeable_type, likeable_id)
);
```

### Table: follows (NEW)
```sql
CREATE TABLE follows (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    follower_id     BIGINT UNSIGNED NOT NULL,   -- user who is following
    following_id    BIGINT UNSIGNED NOT NULL,   -- user being followed
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_follow (follower_id, following_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_follower (follower_id),
    INDEX idx_following (following_id)
);
```

### Table: bookmarks
```sql
CREATE TABLE bookmarks (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_bookmark (user_id, question_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);
```

### Table: notifications
```sql
CREATE TABLE notifications (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    type        VARCHAR(100) NOT NULL,
    -- Types:
    -- 'answer_posted'      → someone answered your question
    -- 'answer_accepted'    → your answer was accepted
    -- 'content_liked'      → someone liked your content
    -- 'badge_earned'       → you earned a badge
    -- 'user_followed'      → someone followed you
    -- 'content_removed'    → moderator removed your content
    -- 'user_warned'        → moderator warned you
    -- 'user_suspended'     → moderator suspended you
    data        JSON NOT NULL,
    is_read     BOOLEAN DEFAULT FALSE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read)
);
```

### Table: badges
```sql
CREATE TABLE badges (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    icon        VARCHAR(10) NOT NULL,
    tier        ENUM('bronze','silver','gold') DEFAULT 'bronze',
    criteria    VARCHAR(100) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Table: user_badges
```sql
CREATE TABLE user_badges (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NOT NULL,
    badge_id    BIGINT UNSIGNED NOT NULL,
    awarded_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_badge (user_id, badge_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);
```

### Table: reputation_transactions
```sql
CREATE TABLE reputation_transactions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    points          INT NOT NULL,
    reason          VARCHAR(100) NOT NULL,
    reference_type  VARCHAR(50) NULL,
    reference_id    BIGINT UNSIGNED NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at DESC)
);
```

### Table: reports
```sql
CREATE TABLE reports (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reporter_id     BIGINT UNSIGNED NOT NULL,
    reportable_type VARCHAR(50) NOT NULL,
    reportable_id   BIGINT UNSIGNED NOT NULL,
    reason          ENUM('spam','offensive','duplicate','misleading','other') NOT NULL,
    details         TEXT NULL,
    status          ENUM('pending','reviewed','dismissed','actioned') DEFAULT 'pending',
    reviewed_by     BIGINT UNSIGNED NULL,      -- moderator user_id
    reviewed_at     TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_reportable (reportable_type, reportable_id)
);
```

### Table: moderation_actions (NEW)
```sql
CREATE TABLE moderation_actions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    moderator_id    BIGINT UNSIGNED NOT NULL,   -- user with role='moderator'
    action_type     ENUM('remove_question','remove_answer','warn_user','suspend_user','dismiss_flag','dismiss_report','escalate') NOT NULL,
    target_type     VARCHAR(50) NULL,           -- 'question', 'answer', 'user'
    target_id       BIGINT UNSIGNED NULL,
    reason          TEXT NOT NULL,
    report_id       BIGINT UNSIGNED NULL,       -- if triggered by report
    ai_flag_source  BOOLEAN DEFAULT FALSE,      -- if triggered by AI flag
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (moderator_id) REFERENCES users(id),
    INDEX idx_moderator (moderator_id),
    INDEX idx_created (created_at DESC)
);
```

### Table: user_warnings (NEW)
```sql
CREATE TABLE user_warnings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    moderator_id    BIGINT UNSIGNED NOT NULL,
    reason          TEXT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (moderator_id) REFERENCES users(id),
    INDEX idx_user (user_id)
);
```

### Table: audit_logs
```sql
CREATE TABLE audit_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_type  ENUM('admin','moderator') NOT NULL,
    actor_id    BIGINT UNSIGNED NOT NULL,
    action      VARCHAR(100) NOT NULL,
    target_type VARCHAR(50) NULL,
    target_id   BIGINT UNSIGNED NULL,
    details     JSON NULL,
    ip_address  VARCHAR(45) NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_actor (actor_type, actor_id),
    INDEX idx_created (created_at DESC)
);
```

### Table: ai_requests
```sql
CREATE TABLE ai_requests (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    feature         VARCHAR(100) NOT NULL,
    -- Features:
    -- 'summarization'       → best answer summary
    -- 'answer_generation'   → AI generate answer
    -- 'duplicate_check'     → TF-IDF duplicate detection
    -- 'tag_suggestion'      → keyword → tag matching
    -- 'moderation'          → toxicity scoring
    input_length    INT UNSIGNED NULL,
    response_time   FLOAT NULL,
    success         BOOLEAN DEFAULT TRUE,
    question_id     BIGINT UNSIGNED NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_feature (feature),
    INDEX idx_created (created_at DESC)
);
```

### Table: contact_messages
```sql
CREATE TABLE contact_messages (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(191) NOT NULL,
    message     TEXT NOT NULL,
    is_read     BOOLEAN DEFAULT FALSE,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Table: admins (separate auth)
```sql
CREATE TABLE admins (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50) NOT NULL UNIQUE,
    email       VARCHAR(191) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Table: category_requests (NEW — user-requested new categories)
```sql
CREATE TABLE category_requests (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED NOT NULL,
    requested_name      VARCHAR(100) NOT NULL,         -- what the user typed
    suggested_category_id BIGINT UNSIGNED NULL,        -- closest existing category (AI suggestion)
    similarity_score    FLOAT NULL,                    -- 0.0–1.0 score vs suggested
    status              ENUM('pending','approved','merged','dismissed') DEFAULT 'pending',
    actioned_by         BIGINT UNSIGNED NULL,          -- admin id
    actioned_at         TIMESTAMP NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (suggested_category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_created (created_at DESC)
);
```

**How this table is used:**
- When a user types a category name that does NOT match any existing category exactly AND similarity score < 0.75 (not close enough to auto-suggest), but they still click "Request new category" — a row is inserted here.
- When similarity score ≥ 0.75, the suggestion panel shows first; only if user dismisses it and explicitly requests a new category does the row get inserted.
- Admin reviews these in the AI Center → Category Requests panel.

---

## Eloquent Model Relationships

```php
// User
hasMany: questions, answers, likes, bookmarks, notifications,
         reputationTransactions, userBadges, moderationActions (as moderator),
         userWarnings (received), reports (as reporter)
belongsToMany: badges (through user_badges)
// Follow relationships:
belongsToMany: followers (User through follows, foreign=following_id, other=follower_id)
belongsToMany: following (User through follows, foreign=follower_id, other=following_id)

// Question
belongsTo: user, category
hasMany: answers, likes (morphMany), bookmarks, reports (morphMany)
belongsToMany: tags (through question_tag)
hasOne: acceptedAnswer (Answer where is_accepted = true)

// Answer
belongsTo: question, user
hasMany: likes (morphMany), reports (morphMany)

// Like (polymorphic)
belongsTo: user
morphTo: likeable (Question or Answer)

// Follow
belongsTo: follower (User), following (User)

// Category
hasMany: questions
// image stored in storage/app/public/categories/{filename}
// accessed via Storage::url($category->image)

// ModerationAction
belongsTo: moderator (User)

// UserWarning
belongsTo: user, moderator (User)
```

---

## Routes

### Public Routes (web.php)
```php
// Home (with feed tabs)
GET  /                          HomeController@index

// Auth
GET  /login                     Auth\LoginController@show
POST /login                     Auth\LoginController@authenticate
GET  /register                  Auth\RegisterController@show
POST /register                  Auth\RegisterController@store
POST /logout                    Auth\LogoutController@destroy

// Questions
GET  /questions                 QuestionController@index
GET  /questions/create          QuestionController@create        [auth]
POST /questions                 QuestionController@store         [auth]
GET  /questions/{slug}          QuestionController@show
GET  /questions/{id}/edit       QuestionController@edit          [auth, owner]
PUT  /questions/{id}            QuestionController@update        [auth, owner]
DELETE /questions/{id}          QuestionController@destroy       [auth, owner]

// Answers
POST /answers                   AnswerController@store           [auth]
GET  /answers/{id}/edit         AnswerController@edit            [auth, owner]
PUT  /answers/{id}              AnswerController@update          [auth, owner]
DELETE /answers/{id}            AnswerController@destroy         [auth, owner]
POST /answers/{id}/accept       AnswerController@accept          [auth, question_owner]

// Likes (AJAX — replaces votes)
POST /like                      LikeController@toggle            [auth]
-- body: { likeable_type: 'question'|'answer', likeable_id: int }
-- returns: { liked: bool, count: int }

// Follows (AJAX)
POST /follow/{user_id}          FollowController@toggle          [auth]
GET  /users/{id}/followers      FollowController@followers
GET  /users/{id}/following      FollowController@following

// Bookmarks
POST /bookmarks                 BookmarkController@toggle        [auth]
GET  /bookmarks                 BookmarkController@index         [auth]

// Notifications
GET  /notifications             NotificationController@index     [auth]
POST /notifications/read-all    NotificationController@readAll   [auth]
POST /notifications/{id}/read   NotificationController@read      [auth]

// Search
GET  /search                    SearchController@index

// Profile
GET  /profile                   ProfileController@show           [auth]
GET  /profile/edit              ProfileController@edit           [auth]
PUT  /profile                   ProfileController@update         [auth]
GET  /users/{id}                ProfileController@showPublic

// Tags & Categories
GET  /tags/{slug}               TagController@show
GET  /categories                CategoryController@index
GET  /categories/{slug}         CategoryController@show

// Reports
POST /reports                   ReportController@store           [auth]
```

### Moderator Routes (web.php, prefix: /moderator, middleware: moderator)
```php
GET  /moderator                         Moderator\DashboardController@index
GET  /moderator/ai-queue                Moderator\QueueController@aiQueue
GET  /moderator/report-queue            Moderator\QueueController@reportQueue
POST /moderator/remove-question/{id}    Moderator\ActionController@removeQuestion
POST /moderator/remove-answer/{id}      Moderator\ActionController@removeAnswer
POST /moderator/warn-user/{id}          Moderator\ActionController@warnUser
POST /moderator/suspend-user/{id}       Moderator\ActionController@suspendUser
POST /moderator/dismiss-flag/{id}       Moderator\ActionController@dismissFlag
POST /moderator/dismiss-report/{id}     Moderator\ActionController@dismissReport
POST /moderator/escalate/{user_id}      Moderator\ActionController@escalate
GET  /moderator/history                 Moderator\HistoryController@index
```

### Admin Routes (web.php, prefix: /admin, middleware: admin)
```php
GET  /admin                             Admin\DashboardController@index
GET  /admin/users                       Admin\UserController@index
POST /admin/users/{id}/appoint-mod      Admin\ModeratorController@appoint
POST /admin/users/{id}/remove-mod       Admin\ModeratorController@remove
POST /admin/users/{id}/ban              Admin\UserController@permanentBan
GET  /admin/questions                   Admin\QuestionController@index
GET  /admin/answers                     Admin\AnswerController@index
GET  /admin/categories                  Admin\CategoryController@index
POST /admin/categories                  Admin\CategoryController@store
PUT  /admin/categories/{id}             Admin\CategoryController@update
DELETE /admin/categories/{id}           Admin\CategoryController@destroy
GET  /admin/tags                        Admin\TagController@index
POST /admin/tags/merge                  Admin\TagController@merge
GET  /admin/badges                      Admin\BadgeController@index
GET  /admin/contact                     Admin\ContactController@index
GET  /admin/ai-center                   Admin\AICenterController@index
GET  /admin/audit-logs                  Admin\AuditLogController@index
GET  /admin/analytics                   Admin\AnalyticsController@index
GET  /admin/moderators                  Admin\ModeratorController@index
```

### API Routes (api.php)
```php
POST /api/ai/check-duplicate        AIController@checkDuplicate
POST /api/ai/suggest-tags           AIController@suggestTags
GET  /api/ai/summarize/{id}         AIController@summarize
POST /api/ai/generate-answer        AIController@generateAnswer    [auth]
GET  /api/questions/related/{id}    QuestionController@related

// Tag & Category Smart Merge (AJAX — called from question create/edit form)
POST /api/tags/check-duplicate      TagMergeController@checkTag
-- body: { name: string }
-- returns: { exact_match: bool, canonical_tag: {id,name} | null, suggestions: [{id,name,score}], is_new: bool }

POST /api/categories/check-similar  TagMergeController@checkCategory
-- body: { name: string }
-- returns: { exact_match: bool, canonical_category: {id,name} | null, suggestions: [{id,name,score}] }

// Admin: get AI-grouped duplicate tag clusters (called on AI Center page load)
GET  /api/admin/tags/duplicate-groups    Admin\TagMergeController@groups  [admin]
-- returns: [ { tags: [{id,name,usage_count}], similarity_score: float } ]

// Admin: execute tag merge
POST /api/admin/tags/merge               Admin\TagMergeController@merge   [admin]
-- body: { canonical_id: int, merge_ids: [int] }
-- returns: { merged: int, questions_updated: int, canonical_tag: {id,name,usage_count} }

// Admin: get pending category requests from users
GET  /api/admin/categories/requests     Admin\CategoryRequestController@index  [admin]
-- returns: [ { id, requested_name, suggested_existing: {id,name}, created_at } ]

// Admin: action a category request
POST /api/admin/categories/requests/{id}/action  Admin\CategoryRequestController@action [admin]
-- body: { action: 'approve'|'merge'|'dismiss', category_id?: int }
```

---

## Service Layer Design

### AIService.php
```php
class AIService {
    // Summarize top-liked answers for a question
    public function summarizeAnswers(Question $question): string

    // Generate a draft answer for a question
    // Uses question title, description, and up to 3 top answers as context
    // Returns draft text string for user to edit
    public function generateAnswer(Question $question): string

    // Check content for toxicity/spam — returns score + reason
    public function moderateContent(string $content): array

    // Internal Ollama HTTP call
    private function generate(string $prompt, int $maxTokens = 500): string
    // POST http://localhost:11434/api/generate
    // {"model": "llama3.2:3b", "prompt": "...", "stream": false}
    // Gracefully returns '' if Ollama offline
}
```

**generateAnswer prompt template:**
```
You are a helpful assistant for a Q&A community platform.
Based on the following question and existing community answers, 
write a helpful, accurate draft answer.
Keep it concise and informative (2-4 paragraphs).
Do not copy existing answers verbatim.

Question: {title}

Context: {description}

Top existing answers for context:
{top_3_answers}

Write a new helpful answer:
```

**summarizeAnswers prompt template:**
```
Summarize the following community answers to this question in 3-4 sentences.
Focus on the most agreed-upon solution. Be clear and concise.

Question: {title}

Community Answers (sorted by likes):
{top_5_answers}

Summary:
```

### ReputationService.php
```php
class ReputationService {
    public function award(User $user, string $reason, int $points, $reference = null): void
    public function deduct(User $user, string $reason, int $points, $reference = null): void
    private function updateLevel(User $user): void
}
```
```php
// toggle like on question or answer
// if already liked → delete like, decrement like_count, deduct reputation
// if not liked → create like, increment like_count, award reputation, notify owner
// user cannot like own content
// returns: { liked: bool, count: int }
```

### FollowService.php
```php
class FollowService {
    // Toggle follow/unfollow
    public function toggle(User $follower, User $target): array
    // Returns: { following: bool, followers_count: int }

    // Award reputation to target when followed
    // Create notification for target
    // Update followers_count/following_count denormalized fields
    // Check 'Well Followed' badge criteria
}
```

### ModerationService.php
```php
class ModerationService {
    // Layer 1: fast PHP keyword check
    // Layer 2: Ollama toxicity score if Layer 1 > 0.3
    // Returns: { flagged: bool, score: float, reason: string }
    public function checkContent(string $content): array

    // Called by QuestionController and AnswerController on store
    // score > 0.8  → block submission
    // score 0.5-0.8 → allow, set is_flagged=true, ai_flag_score, ai_flag_reason
    // score < 0.5  → allow normally
    public function processResult(array $result, $model): void
}
```

### TagMergeService.php

This service handles all smart deduplication logic for both tags and categories. It runs entirely in PHP — no Ollama call needed (the similarity algorithm is fast enough for this use case).

```php
class TagMergeService {

    /**
     * Check a tag name typed by a user.
     * Returns exact match, near-duplicate suggestions, or 'is new'.
     *
     * Algorithm:
     *   1. Normalize input: lowercase, remove spaces/hyphens/underscores
     *   2. Find exact normalized match → return { exact_match: true, canonical_tag }
     *   3. If no exact match: compute similarity score against all existing tags
     *      using combined levenshtein + token overlap score
     *   4. Return all tags with score >= 0.75, sorted by score desc
     */
    public function checkTag(string $name): array
    // Returns:
    // {
    //   exact_match: bool,
    //   canonical_tag: Tag|null,          // if exact match found
    //   suggestions: [                     // if near-duplicates found
    //     { tag: Tag, score: float }       // score 0.75–1.0
    //   ],
    //   is_new: bool                       // true if no matches at all
    // }

    /**
     * Check a category name typed by a user.
     * Same algorithm as checkTag but against categories table.
     */
    public function checkCategory(string $name): array
    // Returns same structure as checkTag but with Category models

    /**
     * Find all duplicate tag groups across ALL existing tags.
     * Used by admin AI Center weekly review panel.
     * Groups tags where any pair has similarity >= 0.80.
     */
    public function findDuplicateTagGroups(): array
    // Returns: [ { tags: [Tag], max_similarity: float } ]
    // Excludes groups of 1 (no duplicates)
    // Sorted by max_similarity desc

    /**
     * Execute a merge operation.
     * canonical_id: the tag to keep
     * merge_ids: array of tag IDs to merge into canonical
     *
     * Steps:
     *   1. Update question_tag: SET tag_id = canonical_id WHERE tag_id IN merge_ids
     *      (use INSERT IGNORE to skip if canonical already exists on question)
     *   2. Delete duplicate rows (where a question now has both canonical + duplicate)
     *   3. Delete merged tags from tags table
     *   4. Recalculate canonical tag's usage_count
     *   5. Log to audit_logs
     */
    public function mergeTags(int $canonicalId, array $mergeIds, int $adminId): array
    // Returns: { merged_count: int, questions_updated: int, canonical_tag: Tag }

    /**
     * Similarity score between two strings.
     * Returns float 0.0–1.0.
     *
     * Score = max(normalized_levenshtein, token_overlap)
     *
     * normalized_levenshtein:
     *   similar_text(normalize($a), normalize($b), $percent)
     *   return $percent / 100.0
     *
     * token_overlap:
     *   $tokensA = explode('-', str_replace([' ', '_'], '-', strtolower($a)))
     *   $tokensB = explode('-', str_replace([' ', '_'], '-', strtolower($b)))
     *   $intersection = array_intersect($tokensA, $tokensB)
     *   $union = array_unique(array_merge($tokensA, $tokensB))
     *   return count($intersection) / count($union)   // Jaccard similarity
     */
    private function similarityScore(string $a, string $b): float

    /**
     * Normalize a string for exact matching.
     * Removes all non-alphanumeric characters, lowercases.
     * "laravel-framework" → "laravelframework"
     * "Laravel Framework" → "laravelframework"
     */
    private function normalize(string $text): string
}
```

**Performance note:** `findDuplicateTagGroups()` does an O(n²) comparison across all tags. For a typical platform with < 500 tags this runs in < 100ms. It is only called on the admin AI Center page, not on user-facing pages. If tag count grows beyond 1000, cache the result for 24 hours.

**Response time for user-facing `checkTag()` / `checkCategory()`:** < 50ms for up to 500 tags. Called via debounced AJAX (800ms after user stops typing). Target total roundtrip: < 300ms.

### TagExtractionService.php (updated — now works alongside TagMergeService)
```php
class TagExtractionService {
    // Extract keywords from question title + description
    // Match against existing tags using TagMergeService similarity
    // Return top 5 Tag models sorted by relevance score
    // Does NOT suggest new tags — only matches to existing ones
    public function suggestTags(string $title, string $description): array
}
```

### NotificationService.php
```php
class NotificationService {
    public function answerPosted(Question $question, Answer $answer): void
    public function answerAccepted(Answer $answer): void
    public function contentLiked(User $owner, $likeable): void
    public function badgeEarned(User $user, Badge $badge): void
    public function userFollowed(User $followed, User $follower): void
    public function contentRemoved(User $owner, string $type, string $reason): void
    public function userWarned(User $user, string $reason): void
    public function userSuspended(User $user, int $days, string $reason): void
    private function create(User $user, string $type, array $data): void
}
```

### RecommendationService.php
```php
class RecommendationService {
    // "For You" feed — weighted scoring
    public function forYouFeed(User $user, int $limit = 20): Collection

    // "Following" feed — questions by followed users
    public function followingFeed(User $user, int $limit = 20): Collection

    // Trending feed — high engagement last 7 days
    public function trendingFeed(int $limit = 20): Collection

    // Latest feed — simple newest first
    public function latestFeed(int $limit = 20): Collection

    // People to follow suggestions
    public function suggestUsersToFollow(User $user, int $limit = 5): Collection
}
```

---

## UI Design System

### Color Palette

```css
/* Light Mode */
--primary:      #2563eb;   /* Blue 600 */
--primary-dark: #1d4ed8;   /* Blue 700 */
--secondary:    #64748b;   /* Slate 500 */
--success:      #16a34a;   /* Green 600 */
--danger:       #dc2626;   /* Red 600 */
--warning:      #d97706;   /* Amber 600 */
--like-color:   #e11d48;   /* Rose 600 — for heart icons */

--bg-primary:   #ffffff;
--bg-secondary: #f8fafc;
--bg-card:      #ffffff;
--border:       #e2e8f0;

--text-primary:   #0f172a;
--text-secondary: #64748b;
--text-muted:     #94a3b8;

/* Dark Mode */
--bg-primary:   #0f172a;
--bg-secondary: #1e293b;
--bg-card:      #1e293b;
--border:       #334155;
--text-primary: #f1f5f9;
```

### Like Button Pattern (Quora-style)
```html
<!-- Unliked state -->
<button class="like-btn" data-type="answer" data-id="42">
  <i class="far fa-heart"></i>
  <span class="like-count">12</span>
</button>

<!-- Liked state (JS adds .liked class) -->
<button class="like-btn liked" data-type="answer" data-id="42">
  <i class="fas fa-heart" style="color: var(--like-color)"></i>
  <span class="like-count">13</span>
</button>
```

### Follow Button Pattern
```html
<!-- Not following -->
<button class="follow-btn" data-user-id="5">
  + Follow
</button>

<!-- Following (JS adds .following class) -->
<button class="follow-btn following" data-user-id="5">
  ✓ Following
</button>
```

### Category Cards (Image-based)
```html
<!-- Category card with image background -->
<div class="category-card" style="background-image: url('/storage/categories/php.jpg')">
  <div class="category-card-overlay">
    <h3 class="category-card-name">PHP</h3>
    <span class="category-card-count">1,248 questions</span>
  </div>
</div>

<!-- CSS -->
.category-card {
  height: 160px;
  border-radius: 12px;
  background-size: cover;
  background-position: center;
  position: relative;
  overflow: hidden;
}
.category-card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.1) 60%);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 16px;
}
.category-card-name {
  color: white;
  font-weight: 700;
  margin: 0;
}
.category-card-count {
  color: rgba(255,255,255,0.8);
  font-size: 0.85rem;
}
```

### Home Feed Tabs (Quora-style)
```html
<div class="feed-tabs">
  <a href="?feed=for-you"   class="feed-tab active">For You</a>
  <a href="?feed=following" class="feed-tab">Following</a>
  <a href="?feed=trending"  class="feed-tab">Trending</a>
  <a href="?feed=latest"    class="feed-tab">Latest</a>
</div>
```

### Question Card (Updated)
```
┌──────────────────────────────────────────────────────────────┐
│  [Category Image Thumbnail]  PHP > Laravel                   │
│                                                              │
│  How do I use Eloquent relationships in Laravel?             │
│                                                              │
│  I'm trying to define a hasMany relationship...              │
│                                                              │
│  [laravel] [eloquent] [php]                                  │
│                                                              │
│  [Avatar] Parth N.  ·  2h ago  ·  👁 124  ·  💬 8          │
│                                                              │
│  [♡ 24 Likes]   [🔖 Bookmark]   [Share]                     │
└──────────────────────────────────────────────────────────────┘
```

### Answer Card (Updated)
```
┌──────────────────────────────────────────────────────────────┐
│  ✅ ACCEPTED ANSWER                          [AI Generated]  │
│                                                              │
│  [Avatar]  Raj Patel  ·  1h ago  ·  Reputation: 1,248       │
│                                                              │
│  You can use Eloquent's hasMany method...                    │
│  [Full answer content via Quill.js HTML]                     │
│                                                              │
│  [♡ 42 Likes]   [Edit]  [Delete]   [Report]                 │
└──────────────────────────────────────────────────────────────┘
```

### Moderator Dashboard
```
┌─────────────────────────────────────────────────────────────┐
│ 🛡 MODERATOR PANEL              [Parth N. — Moderator]      │
├───────────────┬─────────────────────────────────────────────┤
│ Overview      │ 📊 TODAY'S OVERVIEW                         │
│               │  AI Flags Pending: 18                       │
│ AI Queue  18  │  User Reports: 7                            │
│               │  Actions Today: 12                          │
│ Reports    7  │                                             │
│               ├─────────────────────────────────────────────┤
│ History       │ 🤖 AI MODERATION QUEUE                      │
│               │                                             │
│               │ #1024 Answer — Toxicity 94%                 │
│               │ "This is completely wrong and you're..."    │
│               │ [View Full] [Remove] [Dismiss]              │
│               │                                             │
│               │ #1025 Question — Spam 88%                   │
│               │ "Buy cheap followers at..."                 │
│               │ [View Full] [Remove] [Dismiss]              │
│               │                                             │
│               ├─────────────────────────────────────────────┤
│               │ 🚩 USER REPORTS QUEUE                       │
│               │                                             │
│               │ Reported by: User_123                       │
│               │ Reason: Offensive                           │
│               │ Content: "..."                              │
│               │ [Remove] [Warn User] [Dismiss]              │
└───────────────┴─────────────────────────────────────────────┘
```

---

## Security Architecture

### Authentication Flows

**User Auth:** Laravel standard `Auth::attempt()` against `users` table  
**Admin Auth:** Manual session `$_SESSION['admin_id']` against `admins` table (separate)  
**Moderator Auth:** Same as user auth — moderator is a `users` row with `role='moderator'`

### Moderator Authorization
```php
// ModeratorMiddleware.php
public function handle(Request $request, Closure $next) {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    if (!in_array(Auth::user()->role, ['moderator', 'admin_user'])) {
        abort(403, 'Moderator access required.');
    }
    return $next($request);
}
```

**Note:** Admins use a completely separate `admins` table and session — they do NOT access the moderator routes.  
Moderators are `users` table rows with `role='moderator'` — they access `/moderator/*` routes only.

### Like Authorization
```php
// LikeController.php
public function toggle(Request $request) {
    $user = Auth::user();
    $type = $request->likeable_type; // 'question' or 'answer'
    $id   = $request->likeable_id;

    $model = $type === 'question' ? Question::findOrFail($id) : Answer::findOrFail($id);

    // Cannot like own content
    if ($model->user_id === $user->id) {
        return response()->json(['error' => 'Cannot like own content'], 403);
    }
    // ... toggle logic
}
```

### Category Image Upload
```php
// Admin\CategoryController.php store/update
$request->validate([
    'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
]);
if ($request->hasFile('image')) {
    // Delete old image if exists
    if ($category->image) {
        Storage::delete('public/categories/' . $category->image);
    }
    $filename = uniqid('cat_') . '.' . $request->file('image')->getClientOriginalExtension();
    $request->file('image')->storeAs('public/categories', $filename);
    $category->image = $filename;
}
```

---

## Migration Strategy from v1 to v2

```php
// php artisan migrate:from-v1
// 1. users        → users (set role='user', password_reset_required=true)
// 2. category     → categories (generate slugs, image=NULL)
// 3. questions    → questions (generate slugs, like_count=0)
// 4. answers      → answers (like_count=0)
// 5. answer_likes → likes (likeable_type='answer', likeable_id=answer_id)
// 6. contact_messages → contact_messages
// 7. admins       → admins (new bcrypt password, output to console)
// No vote data exists in v1 (only answer_likes) — map cleanly to likes table
```
