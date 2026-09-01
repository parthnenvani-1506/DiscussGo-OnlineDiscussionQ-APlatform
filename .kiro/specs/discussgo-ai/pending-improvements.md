# DiscussHub — Pending Improvements

## Overview of 7 Changes Required

---

## Change 1: User Can Add Category (with Smart Deduplication)

**Current:** Category is dropdown-only, admin-managed.

**New Behavior:**
- Category field shows **two options**:
  1. **Dropdown** — select from existing categories
  2. **Text box** — type a new category name

- When user types in the text box:
  - Exact match (case-insensitive) → silently uses existing category (no DB insert)
  - Similar match (≥ 70% similarity via TagMergeService) → shows suggestion: "Did you mean [existing]?"
  - Unique new name → creates new category in DB, uses it for the question
  
- UI: Two tabs: `[Select Existing]` `[Add New]`

**Files to change:**
- `resources/views/questions/create.blade.php` — dual-mode category input
- `resources/views/questions/edit.blade.php` — same
- `app/Http/Controllers/QuestionController.php` — store/update handle new_category_name
- `app/Http/Requests/StoreQuestionRequest.php` — category_id nullable, new_category_name nullable
- `app/Http/Requests/UpdateQuestionRequest.php` — same
- `routes/api.php` — already has `/api/categories/check-similar`

---

## Change 2: Tags — Searchable Textbox, Max 5, Smart Dedup

**Current:** Multi-select `<select>` box with hold Ctrl instruction (bad UX).

**New Behavior:**
- Replace the `<select multiple>` with a **tag chip input**:
  - Text box: user types a tag name
  - As they type: live AJAX search against existing tags (shows suggestions dropdown)
  - Press Enter or click suggestion → tag added as a chip
  - Max 5 tags enforced with visual counter
  - Each chip has an × button to remove
  - Exact match → uses existing tag silently
  - Similar match → shows "Similar to [existing] — use it?"
  - Completely new → creates tag in DB when question is saved
  
- Hidden input stores selected tag IDs for form submission

**Files to change:**
- `resources/views/questions/create.blade.php` — new tag chip UI
- `resources/views/questions/edit.blade.php` — same
- `public/js/app.js` — tag chip logic
- `public/css/app.css` — tag chip styles
- `app/Http/Controllers/QuestionController.php` — handle new tag names
- `routes/api.php` — `/api/tags/search` already exists

---

## Change 3: TinyMCE Editor for Description (Optional)

**Current:** Plain `<textarea>` for description, required with min 20 chars.

**New Behavior:**
- Replace textarea with **TinyMCE free editor** (CDN, no API key needed for basic)
- Description becomes **optional** (nullable)
- TinyMCE provides: Bold, Italic, Headings, Lists, Code blocks, Links
- On form submit: TinyMCE writes HTML to hidden textarea

**Files to change:**
- `resources/views/questions/create.blade.php` — add TinyMCE init
- `resources/views/questions/edit.blade.php` — same  
- `app/Http/Requests/StoreQuestionRequest.php` — description nullable
- `app/Http/Requests/UpdateQuestionRequest.php` — same

---

## Change 4: Fix All Forbidden/404 Links

**Current:** Some links give 403 Forbidden or 404.

**Root causes identified:**
1. Server running on `127.0.0.1:8080` via `php -S` — all routes work
2. Apache `.htaccess` doesn't rewrite subdirectory routes correctly
3. Some hardcoded absolute paths in JS (already fixed with `url()` helper)

**Fix:**
- Verify all `@auth` blocks protect links correctly
- Add `@if(auth()->check())` guards on profile-edit links
- Ensure all route helpers use named routes (no hardcoded strings)
- Double-check middleware on protected routes

---

## Change 5: Delete Unnecessary Files

**Files to delete (not used anywhere):**

### Legacy project files (entire folder)
- `legacy/` folder — old PHP project, not needed in new Laravel app

### Unused public assets
- `public/script.js` — old v1 JS file, replaced by `public/js/app.js`
- `public/style.css` — old v1 CSS file, replaced by `public/css/app.css`
- `public/video1.mp4` — not used anywhere in views
- `public/Virat+Kohli.jpg` — not used in any view
- `public/download.jpg` — not used
- `public/img*.jpg/png/webp` (img1-img28) — old v1 images, not referenced in any blade file
- `public/adminlogo.jpeg`, `public/adminlogo2.jpeg`, `public/adminlogo3.jpeg` — old admin logos
- `public/logo.png`, `public/logo2.png`, `public/logo3.png` — not used
- `public/user.jpg` — only referenced in `User::getProfileImageUrlAttribute()` as fallback

### Check before deleting
- Scan all blade files for any `asset('img...')` or `asset('logo...')` references first

---

## Change 6: Single Database (Remove Legacy DB)

**Current:** `.env` has `DB_DATABASE=DiscussHub_db` (single clean database, legacy reference removed).

**Fix:**
- Remove `DB_LEGACY_DATABASE` from `.env`
- The `MigrateV1Command` references the legacy DB — keep it but make it fail gracefully if DB doesn't exist
- Ensure no code accidentally connects to an old database

---

## Change 7: Perfect Structural Format

**Current issues:**
- Questions/show page: description rendered as raw text (markdown not fully parsed in some browsers)
- Category field layout could be cleaner
- Tags input UX is poor (multi-select)
- Form validation errors positioned inconsistently

**Fixes:**
- Consistent card structure across all form pages
- Proper spacing and typography matching the design system
- All form errors shown inline under each field
- Loading states on all AJAX actions

---

## Implementation Priority

| # | Change | Effort | Priority |
|---|--------|--------|----------|
| 1 | User add category + dedup | 2h | High |
| 2 | Tag chip input + dedup | 3h | High |
| 3 | TinyMCE + optional description | 1h | High |
| 4 | Fix forbidden links | 1h | High |
| 5 | Delete unused files | 30min | Medium |
| 6 | Single database | 15min | Medium |
| 7 | Structural format polish | 2h | Medium |

**Total: ~10 hours**
