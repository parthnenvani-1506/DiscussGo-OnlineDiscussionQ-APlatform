# DiscussHub — Implementation Tasks

## Overview

This document contains all implementation tasks organized by phase.  
Each task includes acceptance criteria, estimated effort, and dependencies.

**Total Estimated Effort:** ~9 weeks (solo developer)  
**Stack:** Laravel 11 · MySQL 8 · Bootstrap 5 · Vanilla JS · Ollama llama3.2:3b

---

## Phase 1: Foundation & Core Laravel Setup (Week 1–2)

---

### TASK-01: Laravel Project Setup & Configuration
**Priority:** Critical  
**Effort:** 1 day  
**Dependencies:** None

**Steps:**
- [ ] Install Laravel 11 via Composer: `composer create-project laravel/laravel discusshub`
- [ ] Configure `.env` (DB credentials, app name, app URL)
- [ ] Set timezone to `Asia/Kolkata` in `config/app.php`
- [ ] Install required packages:
  - `composer require laravel/ui` (for auth scaffolding base)
  - `composer require intervention/image` (profile image processing)
- [ ] Configure `config/filesystems.php` for local storage
- [ ] Create symbolic link: `php artisan storage:link`
- [ ] Set up `.gitignore` (add `.env`, `/storage/*.key`, `/vendor`)

**Acceptance Criteria:**
- `php artisan serve` starts without errors
- Database connection works (`php artisan migrate` runs)
- Storage symlink is active

---

### TASK-02: Database Migrations
**Priority:** Critical  
**Effort:** 1 day  
**Dependencies:** TASK-01

**Create all migration files in order:**
- [ ] `001_create_users_table` — with all fields from design.md
- [ ] `002_create_categories_table`
- [ ] `003_create_tags_table`
- [ ] `004_create_questions_table` — with FULLTEXT index
- [ ] `005_create_question_tag_table` — pivot table
- [ ] `006_create_answers_table`
- [ ] `007_create_votes_table` — polymorphic
- [ ] `008_create_bookmarks_table`
- [ ] `009_create_notifications_table` — with JSON data column
- [ ] `010_create_badges_table`
- [ ] `011_create_user_badges_table`
- [ ] `012_create_reputation_transactions_table`
- [ ] `013_create_reports_table`
- [ ] `014_create_audit_logs_table`
- [ ] `015_create_ai_requests_table`
- [ ] `016_create_contact_messages_table`
- [ ] `017_create_admins_table`
- [ ] Run `php artisan migrate`

**Acceptance Criteria:**
- All 17 tables created in MySQL with correct columns, indexes, and foreign keys
- `php artisan migrate:status` shows all migrations as "Ran"

---

### TASK-03: Eloquent Models & Relationships
**Priority:** Critical  
**Effort:** 1 day  
**Dependencies:** TASK-02

**Create models with relationships:**
- [ ] `User.php` — fillable, hidden (password), relationships (questions, answers, votes, bookmarks, notifications, badges, reputationTransactions)
- [ ] `Question.php` — fillable, relationships (user, category, answers, tags, votes, bookmarks, acceptedAnswer)
- [ ] `Answer.php` — fillable, relationships (question, user, votes)
- [ ] `Category.php` — fillable, hasMany questions
- [ ] `Tag.php` — fillable, belongsToMany questions
- [ ] `Vote.php` — fillable, morphTo votable
- [ ] `Bookmark.php` — fillable, belongsTo user, question
- [ ] `Notification.php` — fillable, JSON cast for data column, belongsTo user
- [ ] `Badge.php` — fillable, belongsToMany users
- [ ] `UserBadge.php` — pivot model
- [ ] `ReputationTransaction.php` — fillable, belongsTo user
- [ ] `Report.php` — fillable, morphTo reportable
- [ ] `AuditLog.php` — fillable
- [ ] `AiRequest.php` — fillable
- [ ] `ContactMessage.php` — fillable
- [ ] `Admin.php` — fillable, hidden password

**Acceptance Criteria:**
- `php artisan tinker` → `User::with('questions')->first()` works without errors
- All relationships return correct types (hasMany → Collection, belongsTo → Model)

---

### TASK-04: Database Seeders
**Priority:** High  
**Effort:** 4 hours  
**Dependencies:** TASK-03

- [ ] `CategorySeeder` — seed 10 categories (PHP, Laravel, JavaScript, MySQL, Python, Android, CSS, Git, AI/ML, Other)
- [ ] `TagSeeder` — seed 30 tags (laravel, php, mysql, javascript, css, html, python, android, api, authentication, middleware, eloquent, migration, blade, routing, etc.)
- [ ] `BadgeSeeder` — seed all 8 default badges with criteria strings
- [ ] `AdminSeeder` — create default admin with bcrypt password
- [ ] `DatabaseSeeder` — call all seeders in correct order
- [ ] Run `php artisan db:seed`

**Acceptance Criteria:**
- All seeders run without errors
- Categories, tags, badges visible in database
- Admin account exists and password verifies with bcrypt

---

### TASK-05: Authentication System
**Priority:** Critical  
**Effort:** 1.5 days  
**Dependencies:** TASK-03

- [ ] Create `LoginController` with:
  - show() — return login view
  - authenticate() — validate via LoginRequest, Auth::attempt, regenerate session
  - Rate limit: 5 attempts per minute (throttle middleware)
- [ ] Create `RegisterController` with:
  - show() — return register view
  - store() — validate via RegisterRequest, hash password with Hash::make, create user, auto-login, redirect
- [ ] Create `LogoutController` — invalidate session, redirect
- [ ] Create `LoginRequest` — validate email (required, email format) and password (required, min 8)
- [ ] Create `RegisterRequest` — validate username (required, min 3, max 50, unique users), email (required, unique users), password (required, min 8, confirmed), city (nullable)
- [ ] Add `AdminMiddleware` — check session 'admin_id', redirect to admin login if missing
- [ ] Add `CheckSuspended` middleware — check user is_suspended, redirect with error if true
- [ ] Register middleware in `bootstrap/app.php`
- [ ] Define auth routes in `web.php`

**Acceptance Criteria:**
- User can register with valid data → account created, logged in, redirected to home
- Registration with duplicate email shows validation error
- Login with correct credentials → session created, redirected
- Login with wrong password → error message, rate limit enforced after 5 attempts
- Logout destroys session
- Visiting protected route while logged out → redirected to login

---

### TASK-06: Question CRUD
**Priority:** Critical  
**Effort:** 2 days  
**Dependencies:** TASK-05

- [ ] Create `QuestionController` with:
  - index() — paginate 15, eager load user/category/tags, filter by category/tag/status
  - create() — pass categories and tags to view
  - store() — validate via StoreQuestionRequest, create question, sync tags, increment tag usage_count, award reputation +5
  - show() — load question with all relationships, increment view_count, load answers sorted by votes
  - edit() — authorize (owner only), return edit view
  - update() — authorize, validate, update question and tags
  - destroy() — authorize, delete question (cascade handles answers/votes), deduct reputation -5
- [ ] Create `StoreQuestionRequest` — title min 15 max 300, description min 30, category_id exists, tags array max 5
- [ ] Create `UpdateQuestionRequest` — same rules
- [ ] Create `QuestionPolicy` — update/delete only by owner
- [ ] Register policy in `AuthServiceProvider`
- [ ] Generate question slug from title (unique, use `Str::slug` + suffix if needed)
- [ ] Define all question routes in `web.php`

**Acceptance Criteria:**
- Logged-out user cannot access create question page → redirected to login
- Submit question with title < 15 chars → validation error shown inline
- Valid question created → appears in listing, tags attached, reputation +5 awarded
- Question detail page shows correct user, category, tags, view count
- Owner can edit question; non-owner gets 403
- Owner can delete question; related answers and votes are deleted (cascade)

---

### TASK-07: Answer CRUD
**Priority:** Critical  
**Effort:** 1 day  
**Dependencies:** TASK-06

- [ ] Create `AnswerController` with:
  - store() — validate via StoreAnswerRequest, create answer, increment question answer_count, send notification to question owner, award +10 reputation
  - edit() — authorize owner
  - update() — authorize owner, validate, update answer
  - destroy() — authorize owner, delete answer, decrement question answer_count
  - accept() — authorize question owner, toggle is_accepted on answer, update question is_answered and accepted_answer_id, award +50 to answerer, notify answerer
- [ ] Create `StoreAnswerRequest` — answer min 30 chars, question_id required exists
- [ ] Create `AnswerPolicy`
- [ ] Prevent question owner from accepting their own answer
- [ ] Prevent user from answering their own question (soft block with message)

**Acceptance Criteria:**
- Post answer → appears on question page, question owner notified
- Post answer with < 30 chars → validation error
- Only question owner can accept an answer
- Accepting answer awards +50 to answerer, marks question as answered
- Owner can edit own answer; non-owner gets 403
- Delete answer decrements question answer_count

---

### TASK-08: Voting System
**Priority:** High  
**Effort:** 1 day  
**Dependencies:** TASK-07

- [ ] Create `VoteController`:
  - vote() — AJAX endpoint, validate user not voting own content, insert/update/remove vote, recalculate vote_score on question/answer, award/deduct reputation, return JSON (new_score, user_vote)
- [ ] Vote toggle logic:
  - No existing vote → create vote, award reputation
  - Same vote again → remove vote (undo), reverse reputation
  - Different vote → update vote, adjust reputation delta
- [ ] Update `vote_score` on questions/answers table (denormalized for query performance)
- [ ] POST `/vote` route (auth required, CSRF)

**Acceptance Criteria:**
- Upvote answer → vote score increases, answerer gets +10 reputation
- Click upvote again → vote removed, score returns to previous, reputation reversed
- Downvote → score decreases, answerer gets -2 reputation, voter gets -1
- Cannot vote on own content → error response
- Not logged in → redirect to login (AJAX returns 401 JSON)
- Vote score displayed updates immediately in UI without page reload

---

### TASK-09: Basic UI Layout & Design System
**Priority:** Critical  
**Effort:** 2 days  
**Dependencies:** TASK-05

- [ ] Create `resources/css/app.css` with full design system:
  - CSS custom properties (colors, spacing, typography)
  - Light mode and dark mode variables
  - Dark mode: `[data-theme="dark"]` selector
- [ ] Create `layouts/app.blade.php`:
  - Sticky navbar with logo, nav links, search bar, notification bell, user dropdown
  - Main content area with optional sidebar slot
  - Footer
  - Dark mode toggle button (JS in app.js persists to localStorage)
- [ ] Create `layouts/auth.blade.php` — centered card layout
- [ ] Create `layouts/admin.blade.php` — sidebar + top bar (custom, not AdminLTE)
- [ ] Create all partial components (navbar, footer, flash messages, pagination)
- [ ] Add Inter font via Google Fonts
- [ ] Ensure fully responsive (mobile hamburger menu working)

**Acceptance Criteria:**
- Home page loads with new design, no broken styles
- Navbar collapses correctly on mobile
- Dark mode toggle switches theme, preference persists on page reload
- Flash messages appear and auto-dismiss after 4 seconds

---

## Phase 2: Enhanced Features (Week 3–5)

---

### TASK-10: Bookmarks
**Priority:** High  
**Effort:** 4 hours  
**Dependencies:** TASK-06

- [ ] `BookmarkController`:
  - toggle() — AJAX, create or delete bookmark, update question bookmark_count, return JSON (bookmarked: bool, count: int)
  - index() — show all bookmarked questions for auth user, paginated
- [ ] Bookmark button on question cards and question detail page
- [ ] Bookmarks page at `/bookmarks`

**Acceptance Criteria:**
- Click bookmark → question saved, button state changes visually
- Click again → bookmark removed
- /bookmarks page shows all saved questions
- Empty bookmarks page shows friendly empty state with CTA

---

### TASK-11: Notifications
**Priority:** High  
**Effort:** 1 day  
**Dependencies:** TASK-07

- [ ] `NotificationService` — create all notification types (see design.md)
- [ ] Trigger notifications from AnswerController (new answer, accepted answer)
- [ ] Trigger from VoteController (upvote received)
- [ ] Trigger from BadgeService (badge earned)
- [ ] `NotificationController`:
  - index() — paginated list of all notifications, mark all as read on page load
  - read() — AJAX, mark single notification as read
  - readAll() — AJAX, mark all as read
- [ ] Navbar bell icon: fetch unread count via meta tag (no real-time polling)
- [ ] Notification dropdown: last 10 notifications with icons and links
- [ ] Notification page: full paginated list

**Acceptance Criteria:**
- Post answer → question owner sees bell badge increment
- Answer accepted → answerer gets notification
- Notification dropdown shows correct info with correct links
- Read all → bell count resets to 0
- Notifications page shows all notifications with read/unread styling

---

### TASK-12: Reputation & Badge System
**Priority:** High  
**Effort:** 1.5 days  
**Dependencies:** TASK-07, TASK-08, TASK-11

- [ ] `ReputationService`:
  - award() — add points, record transaction, update user.reputation, call updateLevel()
  - deduct() — subtract points, record transaction, update user.reputation
  - updateLevel() — recalculate user.level based on total reputation
- [ ] Wire ReputationService into all controllers that should award/deduct points
- [ ] `BadgeService`:
  - checkAndAward() — called after any reputation change
  - One private method per badge type, checks criteria, calls award if not already earned
  - Award triggers notification via NotificationService
- [ ] Reputation transaction history on profile page (last 20)
- [ ] Reputation level badge shown on user profile and question cards

**Acceptance Criteria:**
- Ask question → +5 reputation appears in transactions
- Answer accepted → +50 reputation, "Accepted Answer" badge awarded if first
- Profile shows correct reputation number and level badge
- Badge shows on profile once earned, not duplicated
- Reputation cannot go below 0

---

### TASK-13: Search
**Priority:** High  
**Effort:** 1 day  
**Dependencies:** TASK-06

- [ ] `SearchController@index`:
  - Accept `q`, `category`, `tag`, `status`, `sort` query params
  - Use MySQL FULLTEXT search: `MATCH(title, description) AGAINST(? IN BOOLEAN MODE)`
  - Fallback to LIKE if FULLTEXT score is 0
  - Apply filters: category_id, tag slug, is_answered
  - Sort by: relevance (default), newest, votes
  - Paginate results (15 per page), preserve query params in pagination links
- [ ] Search bar in navbar submits GET to `/search?q=...`
- [ ] Results page shows question count, active filters, sort options, question list
- [ ] Filter sidebar with category list, tag cloud, status (all/answered/unanswered)
- [ ] Highlight search query terms in result titles

**Acceptance Criteria:**
- Search "laravel authentication" returns relevant results
- Apply category filter → results narrowed correctly
- Sort by votes → highest scored questions first
- Searching with empty query → show all questions (or prompt to enter query)
- Search with no results → friendly empty state with suggestions

---

### TASK-14: User Profile Pages
**Priority:** High  
**Effort:** 1 day  
**Dependencies:** TASK-12

- [ ] `ProfileController`:
  - show() — own profile (with edit CTA)
  - showPublic() — public profile for any user `/users/{id}`
  - edit() — edit form
  - update() — validate via UpdateProfileRequest, handle avatar upload, update session
- [ ] Profile page sections:
  - Header: avatar, name, city, bio, reputation, level badge, join date
  - Stats row: questions count, answers count, accepted answers count, badges count
  - Badges section: earned badges displayed as grid
  - Tabs: Questions | Answers | Reputation History
- [ ] Avatar upload: validate image type/size, store in storage/app/public/profiles/
- [ ] UpdateProfileRequest: username required min 3, bio nullable max 500, city nullable

**Acceptance Criteria:**
- Profile shows correct stats and badges
- Edit profile updates username/city/bio/avatar correctly
- Invalid image type → validation error shown
- Reputation history tab shows last 20 transactions with reasons
- Public profile accessible to all (even not logged in)

---

### TASK-15: Tags & Categories Pages
**Priority:** Medium  
**Effort:** 4 hours  
**Dependencies:** TASK-06

- [ ] `TagController@show` — show tag info + paginated questions with that tag
- [ ] `CategoryController@show` — show category info + paginated questions in category
- [ ] Sidebar on question listing and home: popular tags cloud (top 20 by usage_count)
- [ ] Category list sidebar with question counts

**Acceptance Criteria:**
- Tag page shows correct tag name, description, usage count, and filtered questions
- Category page shows correct category with filtered questions
- Tag usage counts update when questions are posted/deleted

---

### TASK-16: Reporting System
**Priority:** Medium  
**Effort:** 4 hours  
**Dependencies:** TASK-06, TASK-07

- [ ] Report button on question and answer cards (visible to logged-in users who don't own content)
- [ ] Report modal: select reason (dropdown), optional details text
- [ ] `ReportController@store` — validate, prevent duplicate reports (one per user per content), create report
- [ ] Admin report queue: list pending reports, show content preview, action buttons (dismiss / delete content / warn user)

**Acceptance Criteria:**
- Can report any question/answer with a reason
- Cannot report own content
- Cannot report same content twice (duplicate check)
- Admin sees report in queue with content preview
- Admin actions (dismiss, delete content) work correctly

---

### TASK-17: Admin Panel — Dashboard & Analytics
**Priority:** High  
**Effort:** 1.5 days  
**Dependencies:** TASK-06

- [ ] Admin login/logout (separate from user auth, using `admins` table)
- [ ] `AdminMiddleware` protecting all `/admin/*` routes
- [ ] `Admin\DashboardController@index`:
  - Total users, questions, answers, categories (KPI cards)
  - New users last 7 days (Chart.js line chart)
  - New questions last 7 days (Chart.js line chart)
  - Answers per day last 7 days (Chart.js bar chart)
  - Top 5 categories by question count (pie/doughnut chart)
  - Recent contact messages (last 5)
  - Pending moderation reports count
- [ ] `Admin\AnalyticsController@index` — detailed analytics with date range picker

**Acceptance Criteria:**
- Admin dashboard loads with correct real data from database
- Charts render correctly using Chart.js
- Accessing `/admin` without admin session → redirect to admin login
- Non-admin user session cannot access admin routes

---

### TASK-18: Admin Panel — Content Management
**Priority:** High  
**Effort:** 1.5 days  
**Dependencies:** TASK-17

- [ ] `Admin\UserController` — list users, search by name/email, suspend/unsuspend, delete, view profile
- [ ] `Admin\QuestionController` — list questions with filters, view, pin/unpin, delete
- [ ] `Admin\AnswerController` — list answers, view parent question, delete
- [ ] `Admin\CategoryController` — full CRUD for categories (name, slug, icon, color, description)
- [ ] `Admin\TagController` — full CRUD, merge tags feature (reassign question_tag rows from tag A to tag B, delete tag A)
- [ ] `Admin\ContactController` — list contact messages, mark as read
- [ ] `Admin\BadgeController` — list badges, create new badge with criteria
- [ ] Log all admin actions to `audit_logs` table
- [ ] `Admin\AuditLogController` — paginated audit log with filters by admin/action/date

**Acceptance Criteria:**
- Admin can suspend user → user gets "account suspended" message on login
- Admin can delete question → cascades to answers, votes, bookmarks
- Merge tags — all questions previously tagged with tag A now have tag B
- All admin actions appear in audit log with timestamp and admin username
- Audit log filterable by action type and date range

---

## Phase 3: AI Features (Week 6–8)

---

### TASK-19: Ollama Setup & AIService
**Priority:** Critical (for AI features)  
**Effort:** 4 hours  
**Dependencies:** TASK-01

- [ ] Install Ollama: download from https://ollama.com
- [ ] Pull model: `ollama pull llama3.2:3b` (~2GB download)
- [ ] Verify: `ollama run llama3.2:3b "hello"` responds correctly
- [ ] Create `AIService.php`:
  - `generate(string $prompt, int $maxTokens = 500): string`
  - Uses `file_get_contents` or Guzzle HTTP to POST to `http://localhost:11434/api/generate`
  - Payload: `{"model": "llama3.2:3b", "prompt": "...", "stream": false}`
  - Error handling: catch connection errors, log them, return empty string
  - Log every request to `ai_requests` table (feature, response_time, success)
- [ ] Add `OLLAMA_URL=http://localhost:11434` to `.env`
- [ ] Add `OLLAMA_MODEL=llama3.2:3b` to `.env`

**Acceptance Criteria:**
- `php artisan tinker` → `app(AIService::class)->generate('Say hello in one word')` returns a response
- Failed Ollama connection does not crash the application (graceful fallback)
- AI request logged to ai_requests table

---

### TASK-20: Duplicate Question Detection (TF-IDF)
**Priority:** Critical  
**Effort:** 1.5 days  
**Dependencies:** TASK-03, TASK-19

- [ ] Create `SimilarityService.php`:
  - `tokenize(string $text): array` — lowercase, remove stopwords, stem words
  - `computeTFIDF(string $text, array $corpus): array` — standard TF-IDF calculation
  - `cosineSimilarity(array $vec1, array $vec2): float` — dot product / (|v1| * |v2|)
  - `findSimilarQuestions(string $title, string $description, int $limit = 5): Collection` — compare against all questions, return top N sorted by score
  - `isDuplicate(string $newTitle, string $existingTitle, float $threshold = 0.7): bool`
- [ ] Create `POST /api/ai/check-duplicate` API endpoint:
  - Accept title and description
  - Return JSON: `{ similar: [{ id, title, slug, score }], is_duplicate: bool }`
  - Log to ai_requests table
- [ ] Integrate into question creation form:
  - After user finishes typing title (debounced 800ms), send AJAX request
  - Display results panel below title input: "3 similar questions found"
  - Show similar questions as clickable links
  - If is_duplicate: true, show warning banner: "A very similar question already exists"
  - User can dismiss warning and still post

**Acceptance Criteria:**
- Typing "how to connect laravel to database" → shows similar existing questions
- Similarity score > 0.7 triggers warning message
- Warning is dismissible and does not block posting
- Performance: check completes within 2 seconds for databases with 1000 questions

---

### TASK-21: Auto Tag Suggestion
**Priority:** High  
**Effort:** 1 day  
**Dependencies:** TASK-04

- [ ] Create `TagExtractionService.php`:
  - `extractKeywords(string $text): array` — remove stopwords, extract significant nouns/terms, score by frequency
  - `matchKeywordsToTags(array $keywords): array` — match extracted keywords against all tags in database using `TagMergeService::similarityScore()`, score and sort
  - `suggestTags(string $title, string $description): array` — combine both, return top 5 Tag models
- [ ] Create `POST /api/ai/suggest-tags` API endpoint:
  - Accept title and description
  - Return JSON: `{ tags: [{ id, name, slug }] }`
- [ ] Integrate into question creation form:
  - After title + description filled, show "Suggested Tags" panel
  - Each tag has a "+ Add" button → adds it to the selected tags input
  - User can also search tags manually (autocomplete from all tags)

**Acceptance Criteria:**
- Typing "How do I use Eloquent relationships in Laravel?" → suggests tags: [laravel, eloquent, php, mysql]
- Tags are clickable to add to question form
- Suggestions are distinct from already-selected tags
- Manual tag search autocomplete works independently

---

### TASK-21B: Smart Tag & Category Deduplication (AI Merge)
**Priority:** High  
**Effort:** 1.5 days  
**Dependencies:** TASK-04, TASK-17, TASK-18

This implements the full smart deduplication feature for tags and categories as specified in FR-05-AI.

**Backend:**
- [ ] Create `TagMergeService.php` with all methods from design.md:
  - `normalize(string $text): string` — lowercase, strip non-alphanumeric
  - `similarityScore(string $a, string $b): float` — levenshtein + Jaccard token overlap, return max
  - `checkTag(string $name): array` — exact match → canonical; near-duplicate (≥0.75) → suggestions; else is_new
  - `checkCategory(string $name): array` — same algorithm against categories table
  - `findDuplicateTagGroups(): array` — O(n²) pairwise comparison, group by ≥0.80 similarity
  - `mergeTags(int $canonicalId, array $mergeIds, int $adminId): array` — bulk retag, delete duplicates, recalculate usage_count, log to audit_logs
- [ ] Create `category_requests` migration and model
- [ ] Create `TagMergeController` (public API routes, no auth needed):
  - `checkTag(Request $request)` — POST `/api/tags/check-duplicate`
  - `checkCategory(Request $request)` — POST `/api/categories/check-similar`
- [ ] Create `Admin\TagMergeController` (admin routes):
  - `groups()` — GET `/api/admin/tags/duplicate-groups`
  - `merge()` — POST `/api/admin/tags/merge`
- [ ] Create `Admin\CategoryRequestController` (admin routes):
  - `index()` — GET `/api/admin/categories/requests`
  - `action()` — POST `/api/admin/categories/requests/{id}/action`

**Frontend — Question Create/Edit Form:**
- [ ] Tag input: after user types a tag name and presses space/comma/tab, fire AJAX to `/api/tags/check-duplicate`
  - If `exact_match: true` → silently replace input with canonical tag name, add to selected tags
  - If `suggestions` non-empty (score ≥ 0.75) → show suggestion pill below input:
    ```
    💡 "laravel-framework" is similar to existing tag "laravel" (94%)
       [Use "laravel"]  [Keep anyway]
    ```
  - If `is_new: true` → allow, create new tag on question save (if admin has "allow user tag creation" setting on)
- [ ] Category input (when user can type a custom category request):
  - Fire AJAX to `/api/categories/check-similar` after 800ms debounce
  - If `exact_match` or high-similarity suggestion → show suggestion panel (same UI as tags)
  - "Request new category" button → POST to save a `category_requests` row
  - Fallback: use the closest suggested existing category as the question's category

**Frontend — Admin AI Center:**
- [ ] Add "Duplicate Tag Groups" section to Admin AI Center page:
  - On page load, fetch from `/api/admin/tags/duplicate-groups`
  - Render each group as a card:
    ```
    Group — 91% similarity
    [javascript] [Java-Script] [JS]   ← clickable pills showing usage count
    Merge all into: [javascript ▼]    ← dropdown to pick canonical
    [Merge Group]  [Dismiss]
    ```
  - "Merge Group" → POST `/api/admin/tags/merge` with canonical + merge_ids
  - On success: show toast, remove group card from UI, update tag list elsewhere
- [ ] Add "Category Requests" section to Admin AI Center page:
  - Table of pending requests: requested name, AI-suggested closest match, similarity %, date
  - Action buttons: [Approve as New Category] [Merge into Suggested] [Dismiss]
  - Each action calls `/api/admin/categories/requests/{id}/action`

**Acceptance Criteria:**
- User types `"LaravelFramework"` in tag input → suggestion shown: "Similar to 'laravel' (96%)" with "Use 'laravel'" button
- User clicks "Use 'laravel'" → input replaced with "laravel" canonical tag
- User types `"mysql-database"` → suggestion shown: "Similar to 'mysql' (89%)"
- User types a completely unique tag like `"quill-editor"` → no suggestion shown, tag created
- User types category `"PHP Programming"` → suggestion shown: "Similar to 'PHP' (87%)"
- Admin AI Center shows duplicate tag groups with correct similarity scores
- Admin clicks "Merge Group" → all questions retagged, duplicate tags deleted, usage_count updated, audit log entry created
- Admin sees pending category requests table with AI-suggested matches
- Admin approves category request → new category created, user's question updated
- AJAX responses return within 300ms for up to 500 tags
- `findDuplicateTagGroups()` with 200 tags completes within 200ms

---

### TASK-22: AI Answer Summarization
**Priority:** Critical  
**Effort:** 1.5 days  
**Dependencies:** TASK-19, TASK-07

- [ ] `AIService::summarizeAnswers(Question $question): string`:
  - Collect top-voted answers (up to 10)
  - Build prompt: "Summarize the community answers to the following question. Be concise (3-4 sentences). Focus on the recommended solution.\n\nQuestion: {title}\n\nAnswers:\n{answers_text}"
  - Return summary string
  - Cache result in `questions.ai_summary`, set `ai_summary_at`
  - Re-generate only when a new answer is posted (check answer_count changed since last summary)
- [ ] Create `GET /api/ai/summarize/{question_id}` endpoint:
  - Check if cached summary is still valid (same answer_count)
  - Return JSON: `{ summary: string, generated_at: timestamp, answer_count: int }`
- [ ] Integrate into question detail page:
  - Show "AI Summary" panel above answers when question has ≥ 5 answers
  - Panel shows: generated summary text + "Powered by Ollama llama3.2:3b" + "Refresh" button
  - On first load, if no cached summary, show "Generating summary..." spinner, call API, render result
  - "Refresh" button regenerates summary

**Acceptance Criteria:**
- Question with 5+ answers shows AI summary panel
- Summary is relevant to the question and answers (not hallucinated)
- Summary loads within 10 seconds (llama3.2:3b is fast on 16GB RAM)
- Cached summary shown instantly; refresh regenerates from Ollama
- Questions with < 5 answers do not show summary panel

---

### TASK-23: AI Content Moderation
**Priority:** Medium  
**Effort:** 1 day  
**Dependencies:** TASK-19

- [ ] `ModerationService.php`:
  - `checkContent(string $text): array` — returns `[flagged: bool, score: float, reason: string]`
  - Layer 1 (fast): PHP keyword-based check against banned words list (instant)
  - Layer 2 (AI): If Layer 1 score > 0.3, send to Ollama: "Rate this content for toxicity from 0 to 1. Reply with only a number.\n\nContent: {text}"
  - Returns combined score
- [ ] Wire ModerationService into `AnswerController@store` and `QuestionController@store`:
  - If score > 0.8: block submission, show error "Content violates community guidelines"
  - If score 0.5–0.8: allow but auto-set `is_flagged = true`, add to admin moderation queue
  - If score < 0.5: allow normally
- [ ] Admin moderation queue shows AI-flagged content with score

**Acceptance Criteria:**
- Obviously offensive content → blocked with error message
- Borderline content → flagged and queued for admin review
- Normal content → no impact on submission flow
- Admin can see AI confidence score in moderation queue
- Layer 1 (keyword check) runs in < 10ms so clean content is not slowed

---

### TASK-24: Personalized Recommendation Feed
**Priority:** High  
**Effort:** 1 day  
**Dependencies:** TASK-06, TASK-12

- [ ] `RecommendationService.php`:
  - `personalizedFeed(User $user, int $limit = 20): Collection`
  - Algorithm:
    ```
    For each question:
      score = 0
      if question has tag user has used before: score += 3 per matching tag
      if question has category user has posted in: score += 5
      recency_bonus = max(0, 10 - days_since_posted)
      score += recency_bonus
      if question has votes > 10: score += 2
    Sort by score DESC, return top $limit
    ```
  - Exclude questions user has already answered or posted
  - Exclude questions older than 30 days (keep feed fresh)
  - Fall back to latest questions for new users with no activity
- [ ] `HomeController@index`:
  - Logged-in: use personalizedFeed()
  - Logged-out: show trending questions (last 7 days, sorted by vote_score + answer_count)
- [ ] Related questions on question detail page:
  - `SimilarityService::findSimilarQuestions()` — show top 4 in sidebar

**Acceptance Criteria:**
- Logged-in user who has asked Laravel questions → feed shows more Laravel-related content
- New user (no activity) → sees latest/trending questions
- Logged-out home page shows trending questions
- Related questions on detail page are actually topically similar

---

### TASK-25: Admin AI Center
**Priority:** Medium  
**Effort:** 4 hours  
**Dependencies:** TASK-19, TASK-22, TASK-23

- [ ] `Admin\AICenterController@index`:
  - Total AI requests today, this week, this month
  - Breakdown by feature (bar chart): summarization, duplicate_check, tag_suggestion, moderation
  - Average response time per feature
  - Success rate percentage
  - AI moderation queue (content flagged by AI, pending admin action)
  - Recent AI requests table (last 50 rows from ai_requests)
- [ ] Admin can dismiss AI moderation flags or take action (delete content)

**Acceptance Criteria:**
- AI center dashboard shows real data from ai_requests table
- Charts render with correct counts per feature type
- AI moderation queue shows flagged content with AI confidence score
- Admin can clear AI flags from the queue

---

## Phase 4: Polish, Security, Testing (Week 9)

---

### TASK-26: V1 Data Migration Script
**Priority:** High  
**Effort:** 4 hours  
**Dependencies:** All above tasks

- [ ] Create artisan command: `php artisan migrate:from-v1`
- [ ] Script reads old database and inserts into new schema:
  - users: copy all fields, set `password_reset_required = true` (add this flag column)
  - category → categories (generate slugs)
  - questions: copy all, generate slugs, set default category
  - answers: copy all
  - answer_likes → votes (votable_type='answer', value=1)
  - contact_messages: copy all
  - admins: copy, bcrypt new random password, output to console
- [ ] Print migration summary: X users migrated, Y questions migrated, Z answers migrated

**Acceptance Criteria:**
- Command runs without errors on existing v1 database
- All data visible in new application
- Old password flag set (users are prompted to reset password on next login)

---

### TASK-27: Security Hardening
**Priority:** Critical  
**Effort:** 1 day  
**Dependencies:** All above tasks

- [ ] Verify all forms have `@csrf` directive
- [ ] Verify all routes that should be `auth` are protected
- [ ] Verify `QuestionPolicy` and `AnswerPolicy` are enforced in controllers
- [ ] Add rate limiting to vote endpoint (10 votes per minute per user)
- [ ] Add rate limiting to ask question (3 per hour for users with reputation < 100)
- [ ] Validate all file uploads: mime type check, file size, extension whitelist
- [ ] Ensure no raw user input reaches the database without Eloquent/binding
- [ ] Add `X-Content-Type-Options`, `X-Frame-Options` headers via middleware
- [ ] Ensure `.env` file is not accessible from web (Apache/Nginx config)
- [ ] Test all endpoints for unauthorized access (non-owner edit/delete)

**Acceptance Criteria:**
- CSRF token missing → 419 error (not a silent bypass)
- Non-owner accessing edit endpoint → 403 Forbidden
- Upload of `.php` file disguised as image → rejected
- Excessive voting → rate limit response (429)
- All SQL is either Eloquent or uses parameter bindings

---

### TASK-28: UI Polish & Dark Mode
**Priority:** High  
**Effort:** 1.5 days  
**Dependencies:** TASK-09

- [ ] Complete dark mode for all pages (check every component has dark mode variables)
- [ ] Empty state designs for:
  - No questions in listing
  - No bookmarks
  - No notifications
  - No search results
  - No answers on a question
- [ ] Loading skeleton for question cards (CSS skeleton animation while AJAX loads)
- [ ] Toast notifications for AJAX actions (vote, bookmark, follow)
- [ ] Responsive testing:
  - Mobile (375px): navbar, question cards, question detail, profile
  - Tablet (768px): sidebar collapses, layout adjusts
  - Desktop (1280px+): full layout
- [ ] Quill.js rich text editor styles consistent with design system
- [ ] Add meta tags for SEO (title, description, og:tags on question pages)
- [ ] Favicon set to project logo

**Acceptance Criteria:**
- Every page looks correct and usable at 375px mobile width
- Dark mode applied consistently with no unthemed elements
- Empty states have helpful messages and CTA buttons
- Vote/bookmark AJAX shows toast confirmation
- Page titles are descriptive (not just "DiscussHub" on every page)

---

### TASK-29: Performance Optimization
**Priority:** Medium  
**Effort:** 4 hours  
**Dependencies:** All above tasks

- [ ] Audit all controllers for N+1 queries using Laravel Debugbar (dev only)
- [ ] Add `with()` eager loading wherever relationships are accessed in loops
- [ ] Add database index for all commonly filtered/sorted columns (see design.md)
- [ ] Cache popular tags and category list (Laravel `Cache::remember` for 10 minutes)
- [ ] Cache AI summaries in database (already in design, verify it's implemented)
- [ ] Add pagination to all admin listing pages (25 per page)

**Acceptance Criteria:**
- Question listing page makes ≤ 5 database queries (verify with Debugbar)
- Home page loads within 1 second on local machine
- No repeated queries for same data within a single request

---

### TASK-30: Final Testing & README
**Priority:** High  
**Effort:** 1 day  
**Dependencies:** All above tasks

- [ ] Test all critical user flows end-to-end:
  - Register → ask question → receive answer → accept answer → check reputation
  - Search for question → vote on answer → bookmark question → view bookmarks
  - Report content → admin reviews → admin takes action
  - AI duplicate check → AI tag suggestion → post question
  - Admin login → view dashboard → manage users → check audit log
- [ ] Fix any bugs discovered during testing
- [ ] Write updated `README.md`:
  - Project description and key features
  - Tech stack
  - Setup instructions (Laravel, Ollama)
  - Environment variables reference
  - Database migration instructions (fresh + from v1)
  - Screenshots (add after UI is complete)
- [ ] Final git commit with clean commit message

**Acceptance Criteria:**
- All 5 test flows complete without errors
- README has complete setup instructions
- Project runs on a fresh machine following README steps

---

## Feature Summary Table

| Task | Feature | Phase | Status |
|------|---------|-------|--------|
| TASK-01 | Laravel Setup | 1 | ⬜ Todo |
| TASK-02 | Database Migrations | 1 | ⬜ Todo |
| TASK-03 | Eloquent Models | 1 | ⬜ Todo |
| TASK-04 | Database Seeders | 1 | ⬜ Todo |
| TASK-05 | Authentication | 1 | ⬜ Todo |
| TASK-06 | Question CRUD | 1 | ⬜ Todo |
| TASK-07 | Answer CRUD | 1 | ⬜ Todo |
| TASK-08 | Voting System | 1 | ⬜ Todo |
| TASK-09 | UI Layout & Design | 1 | ⬜ Todo |
| TASK-10 | Bookmarks | 2 | ⬜ Todo |
| TASK-11 | Notifications | 2 | ⬜ Todo |
| TASK-12 | Reputation & Badges | 2 | ⬜ Todo |
| TASK-13 | Search | 2 | ⬜ Todo |
| TASK-14 | User Profiles | 2 | ⬜ Todo |
| TASK-15 | Tags & Categories Pages | 2 | ⬜ Todo |
| TASK-16 | Reporting System | 2 | ⬜ Todo |
| TASK-17 | Admin Dashboard | 2 | ⬜ Todo |
| TASK-18 | Admin Content Management | 2 | ⬜ Todo |
| TASK-19 | Ollama + AIService | 3 | ⬜ Todo |
| TASK-20 | Duplicate Detection (TF-IDF) | 3 | ⬜ Todo |
| TASK-21 | Auto Tag Suggestion | 3 | ⬜ Todo |
| TASK-21B | Smart Tag & Category Deduplication (AI Merge) | 3 | ⬜ Todo |
| TASK-22 | Answer Summarization | 3 | ⬜ Todo |
| TASK-23 | Content Moderation | 3 | ⬜ Todo |
| TASK-24 | Personalized Feed | 3 | ⬜ Todo |
| TASK-25 | Admin AI Center | 3 | ⬜ Todo |
| TASK-26 | V1 Data Migration | 4 | ⬜ Todo |
| TASK-27 | Security Hardening | 4 | ⬜ Todo |
| TASK-28 | UI Polish & Dark Mode | 4 | ⬜ Todo |
| TASK-29 | Performance | 4 | ⬜ Todo |
| TASK-30 | Testing & README | 4 | ⬜ Todo |
