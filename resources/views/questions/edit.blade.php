@extends('layouts.app')
@section('title', 'Edit Question - DiscussHub')

@push('styles')
{{-- Quill.js rich editor (free, no API key required) --}}
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
/* ── Quill Editor Overrides ───────────────────────────────── */
.ql-toolbar.ql-snow {
    border: 1px solid var(--border); border-bottom: none;
    border-radius: var(--radius-md) var(--radius-md) 0 0;
    background: var(--bg-secondary); padding: 0.5rem 0.75rem;
}
.ql-container.ql-snow {
    border: 1px solid var(--border);
    border-radius: 0 0 var(--radius-md) var(--radius-md);
    background: var(--bg-primary); font-family: var(--font-base); font-size: 0.9375rem; min-height: 200px;
}
.ql-container.ql-snow:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
.ql-editor { min-height: 200px; color: var(--text-primary); line-height: 1.7; padding: 1rem; }
.ql-editor.ql-blank::before { color: var(--text-muted, #9ca3af); font-style: normal; left: 1rem; }
.ql-editor pre.ql-syntax { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: var(--radius-sm); color: #e06c75; font-family: var(--font-code); font-size: 0.85rem; padding: 0.85rem 1rem; overflow-x: auto; }
.ql-editor blockquote { border-left: 4px solid var(--primary); padding-left: 1rem; color: var(--text-secondary); margin: 0.75rem 0; }
.ql-snow .ql-stroke { stroke: var(--text-secondary); }
.ql-snow .ql-fill  { fill:   var(--text-secondary); }
.ql-snow .ql-picker-label { color: var(--text-secondary); }
.ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow .ql-toolbar button.ql-active .ql-stroke { stroke: var(--primary); }
.ql-snow.ql-toolbar button:hover .ql-fill,   .ql-snow .ql-toolbar button.ql-active .ql-fill  { fill:   var(--primary); }
[data-theme="dark"] .ql-toolbar.ql-snow  { background: var(--bg-tertiary, #1e2330); border-color: var(--border); }
[data-theme="dark"] .ql-container.ql-snow { background: var(--bg-primary); border-color: var(--border); }
[data-theme="dark"] .ql-editor { color: var(--text-primary); }
[data-theme="dark"] .ql-snow .ql-stroke { stroke: #9ca3af; }
[data-theme="dark"] .ql-snow .ql-fill  { fill:   #9ca3af; }
[data-theme="dark"] .ql-snow .ql-picker-label { color: #9ca3af; }
[data-theme="dark"] .ql-snow .ql-picker-options { background: var(--bg-secondary); }
[data-theme="dark"] .ql-editor pre.ql-syntax { background: #1a1d2e; color: #e06c75; }
.tag-chip-container {
    display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0.5rem;
    border: 1px solid var(--border); border-radius: var(--radius-md);
    min-height: 46px; background: var(--bg-primary); cursor: text;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.tag-chip-container:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
.tag-chip {
    display: inline-flex; align-items: center; gap: 0.3rem;
    background: var(--primary-light); color: var(--primary);
    border: 1px solid rgba(37,99,235,0.2); border-radius: var(--radius-full);
    padding: 0.2rem 0.6rem; font-size: 0.8rem; font-weight: 500; font-family: var(--font-code);
}
.tag-chip .remove-chip { cursor: pointer; font-size: 0.9rem; line-height: 1; color: var(--primary); border: none; background: none; padding: 0; opacity: 0.7; }
.tag-chip .remove-chip:hover { opacity: 1; }
.tag-chip-input { border: none; outline: none; background: transparent; color: var(--text-primary); font-size: 0.875rem; min-width: 140px; flex: 1; }
.tag-suggestions {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
    background: var(--bg-primary); border: 1px solid var(--border);
    border-radius: var(--radius-md); box-shadow: var(--shadow-elevated);
    max-height: 220px; overflow-y: auto; margin-top: 4px;
}
.tag-suggestion-item {
    padding: 0.55rem 0.85rem; cursor: pointer; font-size: 0.875rem;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid var(--border);
}
.tag-suggestion-item:last-child { border-bottom: none; }
.tag-suggestion-item:hover { background: var(--primary-light); color: var(--primary); }
.category-mode-tabs { display: flex; gap: 0.5rem; margin-bottom: 0.75rem; }
.category-mode-tab {
    padding: 0.35rem 1rem; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 600;
    cursor: pointer; border: 1px solid var(--border); background: var(--bg-secondary); color: var(--text-secondary); transition: all 0.15s;
}
.category-mode-tab.active { background: var(--primary); color: white; border-color: var(--primary); }
</style>
@endpush

@section('content')
<div class="row justify-content-center g-4">
    <div class="col-lg-8">
        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">Edit Question</h2>
            <p class="text-secondary small">Update your question details, category, and tags.</p>
        </div>

        <form action="{{ route('questions.update', $question) }}" method="POST" id="edit-question-form">
            @csrf
            @method('PUT')

            {{-- 1. TITLE --}}
            <div class="dg-card p-4 mb-4">
                <label for="title" class="form-label fw-bold text-dark">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title"
                    class="form-control form-control-dg @error('title') is-invalid @enderror"
                    value="{{ old('title', $question->title) }}"
                    required minlength="15" maxlength="300">
                @error('title')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- 2. CATEGORY --}}
            <div class="dg-card p-4 mb-4">
                <label class="form-label fw-bold text-dark d-block mb-2">Category <span class="text-danger">*</span></label>
                <div class="category-mode-tabs">
                    <button type="button" class="category-mode-tab active" id="tab-select-cat" onclick="switchCategoryMode('select')">
                        <i class="bi bi-list-ul me-1"></i> Select Existing
                    </button>
                    <button type="button" class="category-mode-tab" id="tab-new-cat" onclick="switchCategoryMode('new')">
                        <i class="bi bi-plus-circle me-1"></i> Create New
                    </button>
                </div>
                <div id="cat-select-mode">
                    <select name="category_id" id="category_id" class="form-select form-control-dg">
                        <option value="">-- Select a Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category_id', $question->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="cat-new-mode" class="d-none">
                    <input type="text" name="new_category_name" id="new_category_name"
                        class="form-control form-control-dg"
                        placeholder="Type a new category name..."
                        value="{{ old('new_category_name') }}"
                        maxlength="100">
                    <div id="cat-similarity-hint" class="mt-2 d-none"></div>
                    <div class="form-text small text-muted mt-1">
                        Similar existing categories will be used automatically.
                    </div>
                </div>
                @error('category_id')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- 3. DESCRIPTION —  Quill rich editor, optional --}}
            <div class="dg-card p-4 mb-4">
                <label for="description" class="form-label fw-bold text-dark">
                    Description <span class="text-secondary small fw-normal">(optional)</span>
                </label>
                {{-- Hidden textarea holds the HTML value for form submission --}}
                <textarea name="description" id="description" class="d-none @error('description') is-invalid @enderror"></textarea>
                {{-- Quill editor mounts here --}}
                <div id="quill-description"></div>
                @error('description')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- 4. TAGS --}}
            <div class="dg-card p-4 mb-4">
                <label class="form-label fw-bold text-dark d-flex align-items-center justify-content-between">
                    <span>Tags</span>
                    <span class="small text-secondary fw-normal" id="tag-counter">0 / 5 tags</span>
                </label>
                <p class="text-secondary small mb-3">Search existing or type a new tag. Max 5.</p>
                <div id="tag-hidden-inputs"></div>
                <div class="position-relative">
                    <div class="tag-chip-container" id="tag-chip-container">
                        <input type="text" class="tag-chip-input" id="tag-text-input"
                            placeholder="Search or type a tag..." autocomplete="off" maxlength="50">
                    </div>
                    <div class="tag-suggestions d-none" id="tag-suggestions-dropdown"></div>
                </div>
                <div id="tag-dedup-hint" class="mt-2 d-none"></div>
            </div>

            <div class="d-flex align-items-center gap-3 mb-5">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-semibold">
                    <i class="bi bi-check-circle me-1"></i> Save Changes
                </button>
                <a href="{{ route('questions.show', [$question->id, $question->slug]) }}"
                    class="btn btn-outline-secondary rounded-pill px-4 py-2">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
{{-- Quill.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
// ── Quill Rich Editor Init ────────────────────────────────────
const quillDescription = new Quill('#quill-description', {
    theme: 'snow',
    placeholder: 'Describe your question in detail (optional)...',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link'],
            ['clean']
        ]
    }
});

// Pre-fill with existing question description
@if($question->description)
    quillDescription.clipboard.dangerouslyPasteHTML({!! json_encode(old('description', $question->description)) !!});
@endif

// Sync Quill HTML to hidden textarea before form submit
document.getElementById('edit-question-form').addEventListener('submit', function() {
    document.getElementById('description').value = quillDescription.root.innerHTML;
});

function switchCategoryMode(mode) {
    const selectMode = document.getElementById('cat-select-mode');
    const newMode    = document.getElementById('cat-new-mode');
    const tabSelect  = document.getElementById('tab-select-cat');
    const tabNew     = document.getElementById('tab-new-cat');
    const catSelect  = document.getElementById('category_id');
    if (mode === 'select') {
        selectMode.classList.remove('d-none'); newMode.classList.add('d-none');
        tabSelect.classList.add('active'); tabNew.classList.remove('active');
        catSelect.required = true;
    } else {
        selectMode.classList.add('d-none'); newMode.classList.remove('d-none');
        tabSelect.classList.remove('active'); tabNew.classList.add('active');
        catSelect.required = false; catSelect.value = '';
        document.getElementById('new_category_name').focus();
    }
}

const catInput = document.getElementById('new_category_name');
const catHint  = document.getElementById('cat-similarity-hint');
if (catInput) {
    let catTimer;
    catInput.addEventListener('input', () => {
        clearTimeout(catTimer);
        const val = catInput.value.trim();
        if (val.length < 3) { catHint.classList.add('d-none'); return; }
        catTimer = setTimeout(async () => {
            try {
                const res  = await fetch(url('/api/categories/check-similar'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ name: val })
                });
                const data = await res.json();
                if (data.exact_match && data.canonical_category) {
                    catHint.innerHTML = `<div class="p-2 rounded bg-success-subtle border border-success small">
                        <i class="bi bi-check-circle text-success me-1"></i>
                        Category <strong>"${data.canonical_category.name}"</strong> already exists and will be used.</div>`;
                } else if (data.suggestions && data.suggestions.length > 0) {
                    const s = data.suggestions[0];
                    catHint.innerHTML = `<div class="p-2 rounded bg-warning-subtle border border-warning small">
                        <i class="bi bi-lightbulb text-warning me-1"></i>
                        Similar: <strong>"${s.category.name}"</strong> (${s.score}% match) — will be used automatically.</div>`;
                } else {
                    catHint.innerHTML = `<div class="p-2 rounded bg-primary-subtle border border-primary small">
                        <i class="bi bi-plus-circle text-primary me-1"></i>
                        New category <strong>"${val}"</strong> will be created.</div>`;
                }
                catHint.classList.remove('d-none');
            } catch(e) { catHint.classList.add('d-none'); }
        }, 600);
    });
}

// Tag chip logic (same as create)
const MAX_TAGS = 5;
const selectedTags = [];
const container    = document.getElementById('tag-chip-container');
const tagInput     = document.getElementById('tag-text-input');
const dropdown     = document.getElementById('tag-suggestions-dropdown');
const hiddenInputs = document.getElementById('tag-hidden-inputs');
const counter      = document.getElementById('tag-counter');
const dedupHint    = document.getElementById('tag-dedup-hint');

function renderChips() {
    container.querySelectorAll('.tag-chip').forEach(c => c.remove());
    hiddenInputs.innerHTML = '';
    counter.textContent = `${selectedTags.length} / ${MAX_TAGS} tags`;
    selectedTags.forEach((t, i) => {
        const chip = document.createElement('span');
        chip.className = 'tag-chip';
        chip.innerHTML = `#${t.name} <button type="button" class="remove-chip" data-idx="${i}">×</button>`;
        container.insertBefore(chip, tagInput);
        if (t.id) {
            hiddenInputs.insertAdjacentHTML('beforeend', `<input type="hidden" name="tag_ids[]" value="${t.id}">`);
        } else {
            hiddenInputs.insertAdjacentHTML('beforeend', `<input type="hidden" name="new_tags[]" value="${t.name}">`);
        }
    });
    container.querySelectorAll('.remove-chip').forEach(btn => {
        btn.addEventListener('click', () => { selectedTags.splice(parseInt(btn.dataset.idx), 1); renderChips(); });
    });
    tagInput.style.display = selectedTags.length >= MAX_TAGS ? 'none' : '';
}

function addTag(id, name, isNew = false) {
    if (selectedTags.length >= MAX_TAGS) { showToast('Maximum 5 tags.', 'warning'); return; }
    if (selectedTags.some(t => (id && t.id === id) || t.name.toLowerCase() === name.toLowerCase())) { showToast('Tag already added.', 'info'); return; }
    selectedTags.push({ id, name, isNew });
    tagInput.value = ''; dropdown.classList.add('d-none'); dedupHint.classList.add('d-none');
    renderChips();
}

let tagTimer;
tagInput.addEventListener('input', () => {
    clearTimeout(tagTimer);
    const q = tagInput.value.trim();
    if (!q) { dropdown.classList.add('d-none'); return; }
    tagTimer = setTimeout(async () => {
        const res = await fetch(url(`/api/tags/search?q=${encodeURIComponent(q)}`), { headers: { 'Accept': 'application/json' } });
        const tags = await res.json();
        const dedupRes = await fetch(url('/api/tags/check-duplicate'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ name: q })
        });
        const dedupData = await dedupRes.json();
        if (dedupData.exact_match && dedupData.canonical_tag) {
            dedupHint.classList.remove('d-none');
            dedupHint.innerHTML = `<div class="p-2 rounded bg-success-subtle border border-success small">
                <i class="bi bi-check-circle text-success me-1"></i> Tag <strong>"${dedupData.canonical_tag.name}"</strong> exists.
                <button type="button" class="btn btn-sm btn-success py-0 px-2 ms-2" onclick="addTag(${dedupData.canonical_tag.id}, '${dedupData.canonical_tag.name}', false)">+ Use it</button>
            </div>`;
        } else if (dedupData.suggestions && dedupData.suggestions.length > 0) {
            const s = dedupData.suggestions[0];
            dedupHint.classList.remove('d-none');
            dedupHint.innerHTML = `<div class="p-2 rounded bg-warning-subtle border border-warning small">
                <i class="bi bi-lightbulb text-warning me-1"></i> Similar: <strong>"${s.tag.name}"</strong> (${s.score}%)
                <button type="button" class="btn btn-sm btn-warning py-0 px-2 ms-2" onclick="addTag(${s.tag.id}, '${s.tag.name}', false)">Use it</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 ms-1" onclick="dedupHint.classList.add('d-none')">Keep mine</button>
            </div>`;
        } else { dedupHint.classList.add('d-none'); }
        const items = tags.map(t => `<div class="tag-suggestion-item" onclick="addTag(${t.id}, '${t.name}', false)">
            <span class="tag-badge">#${t.name}</span><span class="text-muted small">${t.usage_count}</span></div>`).join('');
        dropdown.innerHTML = items + `<div class="tag-suggestion-item" onclick="addTag(null, '${q}', true)">
            <span class="text-primary"><i class="bi bi-plus-circle me-1"></i> Create "#${q}"</span></div>`;
        dropdown.classList.remove('d-none');
    }, 300);
});

tagInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); const v = tagInput.value.trim(); if (v) addTag(null, v, true); }
    if (e.key === 'Backspace' && !tagInput.value && selectedTags.length > 0) { selectedTags.pop(); renderChips(); }
});
document.addEventListener('click', (e) => { if (!container.contains(e.target)) dropdown.classList.add('d-none'); });
container.addEventListener('click', () => tagInput.focus());

// Pre-fill existing question tags
@foreach($question->tags as $tag)
    addTag({{ $tag->id }}, '{{ $tag->name }}', false);
@endforeach
</script>
@endpush
