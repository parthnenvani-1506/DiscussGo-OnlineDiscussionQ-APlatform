# DiscussHub — Requirements Document

## Project Overview

**Project Name:** DiscussHub  
**Type:** AI-Powered Community Knowledge Sharing & Discussion Platform  
**Inspiration:** Quora-style knowledge community with AI moderation and generation  
**Stack:** Laravel 11, MySQL 8, Bootstrap 5, Vanilla JS, Ollama (llama3.2:3b)  
**Developer:** Solo MCA Student Project  
**Machine:** Intel i5-12450H, 16GB RAM, 6GB GPU — Ollama fully supported  
**Budget:** ₹0 — No paid APIs, all AI runs locally  

---

## Current State (v1 Legacy)

The existing project is a procedural PHP Q&A platform with:
- Session-based auth using **MD5 passwords** (critical security flaw)
- Single-file routing via GET params (`?q-id=`, `?c-id=`)
- All business logic in one `server/request.php` file
- 7 database tables: users, questions, answers, category, answer_likes, contact_messages, admins
- Bootstrap 5 frontend + AdminLTE admin panel
- SQL injection vulnerabilities in admin panel (raw string interpolation)
- No CSRF protection, no input validation layer, no authorization policies

---

## Target State (DiscussHub v2)

A Quora-inspired knowledge platform rebuilt in Laravel 11 MVC with:
- Three-tier role system: User → Moderator → Admin
- AI-assisted moderation with human final decision
- Like-based engagement (not upvote/downvote)
- Follower/following social graph
- Category images instead of color badges
- AI answer generation and best-answer summarization
- Secure authentication (bcrypt via Laravel Hash)
- Reputation, badges, notifications, bookmarks
- Redesigned modern UI (custom design system, dark mode)

---

## Role Hierarchy

```
SUPER ADMIN
    └── Full system access, manage moderators, manage admins

ADMIN
    └── Everything Moderator can do, plus:
        └── Manage categories, tags, badges, system settings
        └── View analytics, AI center, audit logs
        └── Appoint/remove moderators

MODERATOR
    └── Review AI-flagged content queue
    └── Remove questions / answers (with reason)
    └── Warn users (record warning)
    └── Suspend users temporarily
    └── Dismiss false AI flags
    └── View moderation history and own action log
    └── Cannot access admin analytics, settings, or badge management

USER
    └── Ask questions, post answers, like content
    └── Follow/unfollow users
    └── Bookmark questions
    └── Report content
    └── Earn reputation and badges
```

---

## Functional Requirements

### FR-01: Authentication & User Management

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-01-01 | User registration with username, email, password, city | Must Have |
| FR-01-02 | User login with email and password | Must Have |
| FR-01-03 | Secure password hashing using bcrypt (Laravel Hash::make) | Must Have |
| FR-01-04 | Session-based authentication with Laravel Auth | Must Have |
| FR-01-05 | User logout with session destruction | Must Have |
| FR-01-06 | Remember me functionality | Should Have |
| FR-01-07 | Password reset via email (Laravel Mail + Mailtrap for dev) | Should Have |
| FR-01-08 | Profile update: username, city, bio, avatar | Must Have |
| FR-01-09 | Profile avatar upload with validation (JPG/PNG, max 5MB) | Must Have |
| FR-01-10 | Public user profile page showing stats, contributions, followers | Must Have |
| FR-01-11 | Account deletion with cascading data removal | Should Have |
| FR-01-12 | Role field on users table: 'user', 'moderator' | Must Have |

---

### FR-02: Questions

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-02-01 | Ask a question with title, rich description (Quill.js), category, tags | Must Have |
| FR-02-02 | Edit own question (within 30 minutes of posting) | Must Have |
| FR-02-03 | Delete own question (with cascading answers/likes deletion) | Must Have |
| FR-02-04 | Question detail page showing full content, answers, sidebar | Must Have |
| FR-02-05 | Questions listing with pagination (15 per page) | Must Have |
| FR-02-06 | Filter questions by category, tags, status (answered/unanswered) | Must Have |
| FR-02-07 | Sort questions by: newest, most liked, most answered, trending | Must Have |
| FR-02-08 | Question view count (increment on each unique visit) | Must Have |
| FR-02-09 | Question bookmark/unbookmark toggle | Must Have |
| FR-02-10 | Question share link (copy to clipboard) | Should Have |
| FR-02-11 | Question status: Open, Answered (auto when accepted answer exists) | Must Have |
| FR-02-12 | AI duplicate warning when posting (similarity ≥ 70%) | Must Have |
| FR-02-13 | AI auto-suggest tags based on title/description | Must Have |
| FR-02-14 | Question analytics (views, likes, answers, bookmarks chart) | Should Have |

---

### FR-03: Answers

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-03-01 | Post answer to a question using rich text editor | Must Have |
| FR-03-02 | Edit own answer | Must Have |
| FR-03-03 | Delete own answer | Must Have |
| FR-03-04 | Like answers (single like per user, not upvote/downvote) | Must Have |
| FR-03-05 | Question owner marks one answer as "Accepted" | Must Have |
| FR-03-06 | Accepted answer displayed at top, visually highlighted | Must Have |
| FR-03-07 | Answer count shown on question listing | Must Have |
| FR-03-08 | AI summary of best answer when question has 5+ answers | Must Have |
| FR-03-09 | AI "Generate Answer" button — Ollama drafts an answer for user | Must Have |
| FR-03-10 | Sort answers by: most liked (default), newest, oldest | Must Have |
| FR-03-11 | Like count shown on each answer | Must Have |

---

### FR-04: Like System

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-04-01 | Like / unlike a question (toggle, one like per user) | Must Have |
| FR-04-02 | Like / unlike an answer (toggle, one like per user) | Must Have |
| FR-04-03 | Like count displayed on questions and answers | Must Have |
| FR-04-04 | Like action via AJAX (no page reload) | Must Have |
| FR-04-05 | Like button visually changes state when liked (filled heart icon) | Must Have |
| FR-04-06 | Notification to content owner when their content is liked | Should Have |
| FR-04-07 | Reputation reward: receive +5 per like on answer, +3 per like on question | Must Have |
| FR-04-08 | No downvote / no dislike — likes only (Quora-style) | Must Have |
| FR-04-09 | User cannot like their own content | Must Have |
| FR-04-10 | Liked content visible on user profile "Liked" tab | Could Have |

---

### FR-05: Tags & Categories

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-05-01 | Tags system: question can have 1–5 tags | Must Have |
| FR-05-02 | Tags autocomplete when creating/editing a question | Must Have |
| FR-05-03 | Tag detail page: questions filtered by tag, tag stats | Must Have |
| FR-05-04 | Categories: admin-managed, each question has one category | Must Have |
| FR-05-05 | Category has: name, slug, description, **image** (uploaded file) | Must Have |
| FR-05-06 | Category image displayed as cover/banner on category card and detail page | Must Have |
| FR-05-07 | Category detail page with image header and filtered question listing | Must Have |
| FR-05-08 | Popular tags listed on sidebar/explore page | Must Have |
| FR-05-09 | Admin can create, rename, merge, delete tags | Must Have |
| FR-05-10 | Admin can upload/replace category image (JPG/PNG, max 2MB) | Must Have |
| FR-05-11 | Category cards on explore page show image as background with name overlay | Must Have |

---

### FR-05-AI: Smart Tag & Category Deduplication (AI Merge)

This is an AI-assisted feature that prevents duplicate tags and categories from being created by users or admins. It operates at two levels: real-time (while typing) and background (admin bulk review).

#### How It Works — Tags

**Level 1 — Exact match (instant, no AI):**
When a user types a tag in the tag input field and it exactly matches an existing tag (case-insensitive), the system silently maps it to the existing tag. No duplicate is created. The user sees the canonical tag name auto-corrected.

Example: User types `"Laravel"` → system finds existing `"laravel"` → uses existing tag.

**Level 2 — Near-duplicate detection (AI/similarity, real-time):**
When a user types a new tag that does NOT exactly match but is similar to an existing tag (similarity score ≥ 0.75), a suggestion panel appears below the input:

```
┌─────────────────────────────────────────────┐
│ 💡 Similar tag already exists               │
│                                             │
│   You typed:      "laravel-framework"       │
│   Existing tag:   "laravel"  (94% match)   │
│                                             │
│   [Use "laravel"]   [Keep my tag anyway]    │
└─────────────────────────────────────────────┘
```

- User clicks "Use existing" → their input replaced with canonical tag
- User clicks "Keep anyway" → new tag created (admin will see it in AI merge review)
- If user is creating a truly new concept tag → no suggestion shown

**Level 3 — Admin AI Merge Review (background, weekly):**
Admin AI Center shows a "Duplicate Tag Groups" panel — tags the AI has grouped as likely duplicates:

```
┌─────────────────────────────────────────────────┐
│ 🤖 AI-Detected Duplicate Tag Groups             │
├─────────────────────────────────────────────────┤
│ Group 1 (91% similarity)                        │
│   javascript  •  Java-Script  •  JS             │
│   [Merge all into "javascript"] [Dismiss]       │
├─────────────────────────────────────────────────┤
│ Group 2 (88% similarity)                        │
│   mysql  •  my-sql  •  MySQL-DB                 │
│   [Merge all into "mysql"] [Dismiss]            │
└─────────────────────────────────────────────────┘
```

When admin clicks "Merge all into X":
1. All `question_tag` rows pointing to the duplicate tags are updated to the canonical tag
2. Duplicate tags are deleted
3. Canonical tag's `usage_count` is recalculated
4. Action is logged in `audit_logs`

#### How It Works — Categories

Categories are admin-managed (users don't create new categories freely), but users suggest a category when asking a question. If a user types a custom category name that is similar to an existing one, AI suggests the correct existing category:

```
┌─────────────────────────────────────────────┐
│ 💡 Similar category found                   │
│                                             │
│   You typed:      "PHP Programming"         │
│   Existing:       "PHP"  (87% match)        │
│                                             │
│   [Use "PHP"]   [Request new category]      │
└─────────────────────────────────────────────┘
```

- "Use existing" → selects the existing category
- "Request new category" → question is tagged with the suggested closest existing category, but a "new category request" is logged for admin review

**Admin New Category Requests panel:**
Admin sees a list of user-requested new categories with the AI-computed closest existing match. Admin can: Approve (create new category), Merge (map to existing), or Dismiss.

#### Similarity Algorithm for Tags

```
Tag similarity uses three layers, combined:

1. Normalized string equality
   - Lowercase, remove hyphens/underscores/spaces
   - "laravel-framework" → "laravelframework"
   - Exact match after normalization → 100% → auto-merge silently

2. Levenshtein distance ratio
   - similar_text($a, $b, $percent)
   - Score: percent / 100.0

3. Token overlap (for multi-word tags)
   - Split by space/hyphen, compare token sets
   - Score: matching_tokens / total_unique_tokens

Final score = max(levenshtein_score, token_overlap_score)
Threshold for suggestion: 0.75
Threshold for silent auto-merge: 1.0 (exact normalized match only)
```

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-05-AI-01 | Exact tag match (case-insensitive) auto-maps to existing canonical tag | Must Have |
| FR-05-AI-02 | Near-duplicate tag suggestion panel shown when similarity ≥ 0.75 | Must Have |
| FR-05-AI-03 | User can accept suggestion (use existing tag) or keep their new tag | Must Have |
| FR-05-AI-04 | Admin AI Center shows grouped duplicate tag clusters weekly | Must Have |
| FR-05-AI-05 | Admin one-click merge: all questions retagged, duplicates deleted, count updated | Must Have |
| FR-05-AI-06 | Merge action logged in audit_logs with full details | Must Have |
| FR-05-AI-07 | Category similarity suggestion shown when user types custom category name | Must Have |
| FR-05-AI-08 | User can "Request new category" — logged for admin review | Must Have |
| FR-05-AI-09 | Admin sees pending category requests with AI-suggested closest match | Must Have |
| FR-05-AI-10 | Admin can: Approve new category, Merge to existing, or Dismiss request | Must Have |
| FR-05-AI-11 | Similarity check runs client-side (AJAX) — response within 300ms | Must Have |
| FR-05-AI-12 | Silent auto-merge never surprises the user — always shown as suggestion for ≥ 0.75 | Must Have |

---

### FR-06: Reputation & Badges

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-06-01 | Users earn reputation points for actions | Must Have |
| FR-06-02 | Reputation transaction log (what earned/lost what) | Must Have |
| FR-06-03 | Reputation levels: Newcomer → Contributor → Experienced → Expert → Mentor | Must Have |
| FR-06-04 | Automatic badge award based on defined criteria | Must Have |
| FR-06-05 | Badges displayed on user profile | Must Have |
| FR-06-06 | Admin can create and manage badges | Should Have |

**Reputation Point Rules (Like-based, no downvote):**
```
Ask a question                → +5
Answer a question             → +10
Answer accepted               → +50
Receive like on an answer     → +5
Receive like on a question    → +3
Gain a new follower           → +2
Daily login                   → +2
```

**Reputation Levels:**
```
0–99       → Newcomer
100–499    → Contributor
500–999    → Experienced
1000–4999  → Expert
5000+      → Mentor
```

**Default Badges:**
```
🥇 First Question      → Ask your first question
🥇 First Answer        → Post your first answer
🥈 Helpful             → Receive 10 total likes on answers
🥈 Popular Question    → Question reaches 100 views
🥇 Accepted Answer     → First accepted answer
🥈 Veteran             → 30 days since joining
🥇 Expert              → Reach 1000 reputation
🥇 Top Contributor     → 50 answers posted
🥈 Well Followed       → Gain 10 followers
🥇 Trending            → Question reaches 50 likes
```

---

### FR-07: Followers System

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-07-01 | User can follow / unfollow another user | Must Have |
| FR-07-02 | Follow button on public profile page | Must Have |
| FR-07-03 | Follow toggle via AJAX (no page reload) | Must Have |
| FR-07-04 | Followers count and Following count on profile | Must Have |
| FR-07-05 | Followers list and Following list pages | Must Have |
| FR-07-06 | Notification to user when someone follows them | Must Have |
| FR-07-07 | Reputation +2 for gaining a new follower | Must Have |
| FR-07-08 | Home feed prioritizes questions from users you follow | Must Have |
| FR-07-09 | "Following" tab on home feed showing only content from followed users | Must Have |
| FR-07-10 | User cannot follow themselves | Must Have |
| FR-07-11 | Badge: "Well Followed" → awarded at 10 followers | Must Have |

---

### FR-08: Notifications

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-08-01 | Notification when someone answers your question | Must Have |
| FR-08-02 | Notification when your answer is accepted | Must Have |
| FR-08-03 | Notification when someone likes your content | Should Have |
| FR-08-04 | Notification when you earn a badge | Must Have |
| FR-08-05 | Notification when someone follows you | Must Have |
| FR-08-06 | Notification when a moderator removes your content (with reason) | Must Have |
| FR-08-07 | Notification when you receive a moderator warning | Must Have |
| FR-08-08 | Notification bell in navbar with unread count badge | Must Have |
| FR-08-09 | Mark individual notifications as read | Must Have |
| FR-08-10 | Mark all notifications as read | Must Have |
| FR-08-11 | Notification dropdown (latest 10) + full page view | Must Have |

---

### FR-09: Search

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-09-01 | Search questions by title and description keywords | Must Have |
| FR-09-02 | Search results with filter sidebar (category, tags, status) | Must Have |
| FR-09-03 | Search result count and sorting options | Must Have |
| FR-09-04 | Tag and category search alongside questions | Should Have |
| FR-09-05 | Search history (stored in session, last 5 queries) | Could Have |
| FR-09-06 | AI semantic search (TF-IDF similarity ranking) | Must Have |

---

### FR-10: Personalized Feed & Recommendations

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-10-01 | Home feed: "For You" tab — personalized questions | Must Have |
| FR-10-02 | Home feed: "Following" tab — questions by followed users | Must Have |
| FR-10-03 | Home feed: "Trending" tab — high engagement last 7 days | Must Have |
| FR-10-04 | Home feed: "Latest" tab — newest questions | Must Have |
| FR-10-05 | Personalization based on user's past question tags and categories | Must Have |
| FR-10-06 | Related questions shown on question detail page sidebar | Must Have |
| FR-10-07 | "People to follow" suggestions on sidebar | Should Have |

---

### FR-11: Bookmarks

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-11-01 | Bookmark/unbookmark any question | Must Have |
| FR-11-02 | Bookmarks page showing all saved questions | Must Have |
| FR-11-03 | Bookmark count shown on question card | Should Have |

---

### FR-12: AI Features (All Local, Zero Cost)

| ID | Requirement | Priority | Implementation |
|----|-------------|----------|----------------|
| FR-12-01 | Duplicate question detection (TF-IDF similarity) | Must Have | PHP, no external service |
| FR-12-02 | Auto tag recommendation from question title/body | Must Have | PHP keyword extraction |
| FR-12-03 | AI best-answer summary for questions with 5+ answers | Must Have | Ollama llama3.2:3b |
| FR-12-04 | AI "Generate Answer" — drafts answer from question context | Must Have | Ollama llama3.2:3b |
| FR-12-05 | AI content moderation (toxicity/spam scoring) | Must Have | PHP rules + Ollama |
| FR-12-06 | AI sends flagged content to Moderator queue | Must Have | ModerationService |
| FR-12-07 | Personalized recommendations (weighted scoring) | Must Have | Pure MySQL + PHP |
| FR-12-08 | Related questions (TF-IDF similarity) | Must Have | PHP, no external service |
| FR-12-09 | AI question quality tips while typing | Should Have | PHP heuristics + JS |

**FR-12-04 — AI Generate Answer Detail:**
- Button "✨ Generate AI Answer" on the question detail page
- Only visible to logged-in users who have not yet answered the question
- On click: sends question title + description + top 3 existing answers (context) to Ollama
- Prompt ensures Ollama generates a relevant, helpful answer draft
- Draft appears in the answer Quill.js editor — user can edit before posting
- Disclaimer shown: "This is an AI-generated draft. Review and edit before submitting."
- The generated text is just a starting point — user must click "Post Answer" manually
- Action logged to ai_requests table (feature: 'answer_generation')

**FR-12-03 — AI Best Answer Summary Detail:**
- Shown above the answers section when question has ≥ 5 answers
- Summarizes the top 5 most-liked answers into 3–4 sentences
- Focuses on the most agreed-upon solution
- Cached in `questions.ai_summary` — regenerated only when answer_count or top liked answers change
- "Refresh" button available to regenerate
- Disclaimer: "AI-generated summary based on community answers."

---

### FR-13: Moderator Role

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-13-01 | Dedicated Moderator role (separate from Admin) | Must Have |
| FR-13-02 | Moderators appointed and removed by Admin only | Must Have |
| FR-13-03 | Moderator dashboard at `/moderator` | Must Have |
| FR-13-04 | AI Moderation Queue — content flagged by AI with confidence score | Must Have |
| FR-13-05 | User Report Queue — content reported by users | Must Have |
| FR-13-06 | Moderator can: Remove question (with mandatory reason) | Must Have |
| FR-13-07 | Moderator can: Remove answer (with mandatory reason) | Must Have |
| FR-13-08 | Moderator can: Warn user (reason recorded, user notified) | Must Have |
| FR-13-09 | Moderator can: Suspend user temporarily (1/3/7/30 days) | Must Have |
| FR-13-10 | Moderator can: Dismiss AI flag (mark as false positive) | Must Have |
| FR-13-11 | Moderator can: Escalate to Admin for permanent ban | Must Have |
| FR-13-12 | Moderator cannot: Access analytics, settings, badge management | Must Have |
| FR-13-13 | Moderator cannot: Delete users permanently (Admin only) | Must Have |
| FR-13-14 | All moderator actions logged in moderation_actions table | Must Have |
| FR-13-15 | Moderator sees own action history | Must Have |
| FR-13-16 | Content owner notified when content is removed, with reason | Must Have |
| FR-13-17 | User warned 3 times before auto-escalation suggestion to admin | Should Have |

**Moderator Dashboard Sections:**
```
┌─────────────────────────────────────────────┐
│ MODERATOR PANEL                             │
├─────────────────────────────────────────────┤
│ 📊 Overview                                 │
│    AI Flags (pending)    18                 │
│    User Reports (pending) 7                 │
│    Actions today          12                │
├─────────────────────────────────────────────┤
│ 🤖 AI Moderation Queue                      │
│    Content flagged by AI sorted by score    │
│    Each item: content preview, AI score,    │
│    flag reason, [Remove] [Dismiss] buttons  │
├─────────────────────────────────────────────┤
│ 🚩 User Reports Queue                       │
│    Reports submitted by users               │
│    Each item: reported content, reporter,   │
│    reason, [Remove] [Dismiss] [Warn] btns   │
├─────────────────────────────────────────────┤
│ 📋 My Action History                        │
│    Log of own moderation actions            │
└─────────────────────────────────────────────┘
```

---

### FR-14: User Reporting

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-14-01 | Report a question or answer with reason | Must Have |
| FR-14-02 | Report reasons: Spam, Offensive, Duplicate, Misleading, Other | Must Have |
| FR-14-03 | Reports go to Moderator queue (not Admin by default) | Must Have |
| FR-14-04 | Moderator can action or dismiss reports | Must Have |
| FR-14-05 | Cannot report own content | Must Have |
| FR-14-06 | Cannot report same content twice | Must Have |

---

### FR-15: Admin Panel

| ID | Requirement | Priority |
|----|-------------|----------|
| FR-15-01 | Admin dashboard with KPI cards and time-series charts | Must Have |
| FR-15-02 | User management: list, search, suspend, delete, assign moderator role | Must Have |
| FR-15-03 | Question management: list, filter, delete, pin/feature | Must Have |
| FR-15-04 | Answer management: list, filter, delete | Must Have |
| FR-15-05 | Category management: full CRUD with image upload | Must Have |
| FR-15-06 | Tag management: full CRUD, merge tags | Must Have |
| FR-15-07 | Badge management: create, edit, assign criteria | Should Have |
| FR-15-08 | Contact messages inbox | Must Have |
| FR-15-09 | AI center: request logs, feature usage, moderation stats | Should Have |
| FR-15-10 | Audit logs: every admin/moderator action recorded | Must Have |
| FR-15-11 | Analytics: user growth, content growth, engagement charts | Must Have |
| FR-15-12 | Moderator management: appoint, view actions, remove role | Must Have |
| FR-15-13 | Permanent user ban (escalated from moderator) | Must Have |

---

## Non-Functional Requirements

### NFR-01: Security
- All passwords hashed with bcrypt (Laravel `Hash::make()`)
- CSRF protection on all forms (`@csrf` blade directive)
- SQL injection prevention via Eloquent ORM + query bindings
- XSS prevention via `{{ }}` blade escaping
- Rate limiting on login (5 attempts per minute)
- Rate limiting on ask question (3 per hour for new users)
- File upload validation (type, size, extension whitelist) — for avatars AND category images
- Admin and Moderator routes protected by separate middleware
- Role-based authorization using Policies and Middleware
- Prepared statements used for any raw queries

### NFR-02: Performance
- Database queries use proper indexes (user_id, question_id, created_at)
- Eager loading to prevent N+1 query problems
- Pagination on all listing pages (15 items default)
- AI summarization cached per question (recomputed only when answer_count changes)
- Category images served from storage (not base64)
- Static assets from `public/` directory

### NFR-03: Usability
- Fully responsive (mobile, tablet, desktop)
- Dark mode toggle with localStorage preference persistence
- Loading states on AJAX actions (like, bookmark, follow)
- Flash messages for all user actions (success, error, warning)
- Empty states with helpful CTAs
- Form validation with inline error messages (client + server side)
- Like buttons use heart icon (filled/unfilled) — Quora-style

### NFR-04: Code Quality
- Laravel MVC architecture strictly followed
- Service layer for all complex business logic
- Form Request classes for all input validation
- Eloquent Policies for authorization
- Resource controllers where applicable
- Consistent naming conventions
- No business logic in Blade views

---

## Out of Scope

- Real-time features (WebSockets, Pusher)
- Mobile app (Android/iOS)
- OAuth login (Google, GitHub)
- Full-text search with Elasticsearch
- Paid AI APIs
- Email notifications (database notifications only)
- Payment / subscription features
- Communities / sub-forums
- Knowledge graph
- Dislike / downvote system (likes only, Quora-style)
