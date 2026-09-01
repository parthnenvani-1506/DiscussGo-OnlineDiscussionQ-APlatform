---
inclusion: always
---

# DiscussHub — Project Steering

## What This Project Is

DiscussHub is a complete rebuild of the existing legacy PHP Q&A platform, migrated to Laravel 11 MVC with AI features powered by Ollama (local, zero cost).

**Current codebase (v1):** Procedural PHP, MD5 passwords, single-file routing, 7 tables  
**Target (v2):** Laravel 11, bcrypt, MVC, 17 tables, AI features

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Database:** MySQL 8 with Eloquent ORM
- **Frontend:** Bootstrap 5 + custom CSS design system + Vanilla JS
- **Rich Editor:** Quill.js
- **Charts:** Chart.js
- **AI:** Ollama running llama3.2:3b locally (16GB RAM machine — fully supported)
- **No paid APIs. No OpenAI. No Gemini. All AI is local.**

## Critical Decisions

1. **Passwords:** Never use MD5. Always use `Hash::make()` (bcrypt). The v1 migration script sets `password_reset_required = true` for all migrated users.
2. **SQL:** Never write raw queries with string interpolation. Always use Eloquent or `DB::select()` with parameter bindings.
3. **Authorization:** Always use `$this->authorize()` in controllers with registered Policies. Never check `Auth::id() === $resource->user_id` inline in controllers.
4. **Validation:** All input goes through a Form Request class. Never validate in controllers directly.
5. **AI failures:** AI features must fail gracefully. If Ollama is offline, the page loads normally without the AI component. No crashes.

## Folder Conventions

```
app/Http/Controllers/Admin/   → all admin controllers
app/Http/Controllers/         → all public controllers
app/Services/                 → business logic (AI, reputation, notifications, badges)
app/Policies/                 → authorization policies
app/Http/Requests/            → form validation classes
resources/views/admin/        → admin blade views
resources/views/layouts/      → app.blade.php, admin.blade.php, auth.blade.php
```

## AI Feature Rules

- `AIService.php` handles all Ollama HTTP calls
- `SimilarityService.php` handles TF-IDF — pure PHP, no external service
- `TagExtractionService.php` handles keyword extraction — pure PHP
- `TagMergeService.php` handles tag/category deduplication — pure PHP (Levenshtein + Jaccard), no Ollama needed
- `ModerationService.php` handles content moderation — PHP keyword check first, Ollama second
- All AI requests are logged to the `ai_requests` table
- AI summaries are cached in `questions.ai_summary` — only regenerate when answer_count changes

## Tag & Category Deduplication Rules

- Exact normalized match (after stripping spaces/hyphens/underscores, lowercase) → silently map to canonical tag, no UI shown
- Similarity score ≥ 0.75 → show suggestion panel to user: "Similar tag exists — Use X?"
- Similarity score < 0.75 → allow new tag creation
- Admin AI Center shows grouped duplicates (score ≥ 0.80) for weekly bulk merge
- Category requests (when user requests a new category) stored in `category_requests` table for admin review
- All merge actions logged to `audit_logs` with full details (canonical_id, merged_ids, questions_updated)

## Database Rules

- All tables use `bigint unsigned` primary keys
- All foreign keys have `ON DELETE CASCADE` where appropriate
- Polymorphic tables (votes, reports) use `_type` and `_id` columns
- Timestamps: always `created_at` and `updated_at` via `$timestamps = true`
- Slugs: generated from title using `Str::slug()`, must be unique

## UI Rules

- Design system defined in `resources/css/app.css` using CSS custom properties
- Dark mode via `[data-theme="dark"]` on `<html>` element, toggled by JS, persisted in localStorage
- All pages must be responsive (375px mobile minimum)
- Inter font from Google Fonts
- No AdminLTE in the new project — admin panel uses the same custom design system
- Empty states must have helpful messages and CTA buttons
- Flash messages auto-dismiss after 4 seconds

## Spec Files Location

Full spec is in `.kiro/specs/discussgo-ai/`:
- `requirements.md` — all functional and non-functional requirements
- `design.md` — architecture, database schema, service design, routes
- `tasks.md` — 30 implementation tasks with acceptance criteria
