@extends('layouts.app')

@section('title', $question->title . ' - DiscussHub')
@section('meta_description', Str::limit(strip_tags($question->description), 150))

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
/* Quill in the inline answer form */
.ql-toolbar.ql-snow {
    border: 1px solid var(--border); border-bottom: none;
    border-radius: var(--radius-md) var(--radius-md) 0 0;
    background: var(--bg-secondary); padding: 0.5rem 0.75rem;
}
.ql-container.ql-snow {
    border: 1px solid var(--border);
    border-radius: 0 0 var(--radius-md) var(--radius-md);
    background: var(--bg-primary); font-size: 0.9375rem; min-height: 180px;
}
.ql-container.ql-snow:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
.ql-editor { min-height: 180px; color: var(--text-primary); line-height: 1.7; padding: 1rem; }
.ql-editor.ql-blank::before { color: var(--text-muted, #9ca3af); font-style: normal; left: 1rem; }
.ql-editor pre.ql-syntax { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: var(--radius-sm); color: #e06c75; font-family: var(--font-code); font-size: 0.85rem; padding: 0.85rem 1rem; overflow-x: auto; }
.ql-snow .ql-stroke { stroke: var(--text-secondary); }
.ql-snow .ql-fill  { fill:   var(--text-secondary); }
.ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow .ql-toolbar button.ql-active .ql-stroke { stroke: var(--primary); }
[data-theme="dark"] .ql-toolbar.ql-snow  { background: var(--bg-tertiary, #1e2330); border-color: var(--border); }
[data-theme="dark"] .ql-container.ql-snow { background: var(--bg-primary); border-color: var(--border); }
[data-theme="dark"] .ql-editor { color: var(--text-primary); }
[data-theme="dark"] .ql-snow .ql-stroke { stroke: #9ca3af; }
[data-theme="dark"] .ql-snow .ql-fill  { fill:   #9ca3af; }
[data-theme="dark"] .ql-editor pre.ql-syntax { background: #1a1d2e; color: #e06c75; }
</style>
@endpush

@section('content')
<div class="row g-4">
    <!-- Main Question & Answers Section -->
    <div class="col-lg-8">
        <!-- Question Detail Card -->
        <div class="dg-card p-4 mb-4">
            <!-- Header Meta -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('categories.show', $question->category->slug) }}" class="category-badge">
                        <i class="{{ $question->category->icon ?? 'bi bi-folder' }} text-primary"></i> {{ $question->category->name }}
                    </a>
                    @if($question->is_answered)
                        <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-circle-fill me-1"></i> Solved</span>
                    @endif
                    @if($question->is_pinned)
                        <span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-pin-angle-fill me-1"></i> Pinned</span>
                    @endif
                </div>

                <div class="small text-secondary d-flex align-items-center gap-3">
                    <span><i class="bi bi-clock me-1"></i> Asked {{ $question->created_at->diffForHumans() }}</span>
                    <span><i class="bi bi-eye me-1"></i> {{ $question->view_count }} views</span>
                </div>
            </div>

            <!-- Question Title -->
            <h1 class="h3 fw-bold text-dark mb-3">{{ $question->title }}</h1>

            <!-- Question Content + Left Vote Control -->
            <div class="d-flex gap-3 mb-4">
                <!-- Vote & Bookmark controls -->
                <div class="vote-widget vote-control flex-shrink-0">
                    @auth
                        @php
                            $userLiked = $question->votes->where('user_id', auth()->id())->count() > 0;
                        @endphp
                        <button class="btn-like {{ $userLiked ? 'liked' : '' }}"
                            data-type="question" data-id="{{ $question->id }}" title="{{ $userLiked ? 'Unlike' : 'Like this question' }}">
                            <i class="bi {{ $userLiked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                            <span class="like-count">{{ $question->vote_score }}</span>
                        </button>

                        <button class="btn btn-sm btn-outline-secondary rounded-circle mt-2 btn-bookmark-toggle {{ $question->bookmarks->where('user_id', auth()->id())->count() ? 'bookmarked' : '' }}" data-question-id="{{ $question->id }}" title="Bookmark">
                            <i class="bi {{ $question->bookmarks->where('user_id', auth()->id())->count() ? 'bi-bookmark-fill text-primary' : 'bi-bookmark' }}"></i>
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="btn-like" title="Sign in to like">
                            <i class="bi bi-heart"></i>
                            <span class="like-count">{{ $question->vote_score }}</span>
                        </a>
                    @endauth
                </div>

                <!-- Description Body -->
                <div class="flex-grow-1 min-w-0">
                    <div class="content-body render-markdown mb-4" data-raw="{{ $question->description }}">{{ $question->description }}</div>

                    <!-- Tags -->
                    <div class="d-flex flex-wrap gap-1 mb-4">
                        @foreach($question->tags as $tag)
                            <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </div>

                    <!-- Bottom Bar: Actions & Author Info -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 pt-3 border-top">
                        <!-- Action links (Edit/Delete/Report) -->
                        <div class="d-flex align-items-center gap-2 small">
                            @can('update', $question)
                                <a href="{{ route('questions.edit', $question) }}" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            @endcan
                            @can('delete', $question)
                                <form action="{{ route('questions.destroy', $question) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            @endcan

                            @auth
                                @if(auth()->id() !== $question->user_id)
                                    <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none p-0" data-bs-toggle="modal" data-bs-target="#reportModal" data-type="question" data-id="{{ $question->id }}">
                                        <i class="bi bi-flag"></i> Report
                                    </button>
                                @endif
                            @endauth

                            <!-- Share button -->
                            <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none p-0" id="btn-share-question"
                                data-url="{{ route('questions.show', [$question->id, $question->slug]) }}"
                                title="Copy link to clipboard">
                                <i class="bi bi-share"></i> Share
                            </button>
                        </div>

                        <!-- Author Card snippet -->
                        <div class="d-flex align-items-center gap-2 p-2 rounded bg-light border">
                            @if($question->user->profile_image && $question->user->profile_image !== 'default_profile.png')
                                <img src="{{ asset('profiles/' . $question->user->profile_image) }}" class="rounded-circle object-fit-cover" width="36" height="36" alt="avatar">
                            @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                    {{ strtoupper(substr($question->user->user_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('users.show', $question->user->id) }}" class="fw-semibold text-dark text-decoration-none small d-block">
                                    {{ $question->user->user_name }}
                                </a>
                                <span class="reputation-badge py-0 px-2" style="font-size: 0.65rem;">
                                    <i class="bi bi-stars"></i> {{ $question->user->reputation }} · {{ ucfirst($question->user->level ?? 'newcomer') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Answer Summarizer Panel — shown only when question has an accepted answer -->
        @if($question->is_answered && $question->accepted_answer_id)
            <div class="ai-assistant-card mb-4" id="ai-summary-box">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white"><i class="bi bi-cpu me-1"></i> Solution Synthesis</span>
                        <span class="small text-secondary">AI summary of the accepted solution</span>
                    </div>
                    <button type="button" id="btn-generate-ai-summary"
                        class="btn btn-sm btn-outline-primary rounded-pill py-1 px-3"
                        data-question-id="{{ $question->id }}">
                        <span id="ai-summary-spinner" class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                        <i class="bi bi-arrow-repeat me-1" id="ai-summary-icon"></i>
                        {{ $question->ai_summary ? 'Refresh Synthesis' : 'Synthesize Solution' }}
                    </button>
                </div>
                <div id="ai-summary-content" class="small lh-base">
                    @if($question->ai_summary)
                        <div class="p-3 bg-light rounded border text-dark mb-2">{{ $question->ai_summary }}</div>
                        <span class="text-muted" style="font-size: 0.7rem;">
                            <i class="bi bi-clock me-1"></i> Synthesized {{ $question->ai_summary_at?->diffForHumans() }}
                        </span>
                    @else
                        <p class="mb-0 text-secondary fst-italic">
                            <i class="bi bi-lightbulb text-warning me-1"></i>
                            Click "Synthesize Solution" to generate an AI summary of the accepted answer.
                        </p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Answers Header -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="fw-bold text-dark mb-0">{{ $answers->count() }} Answers</h4>
        </div>

        <!-- Answers List -->
        <div class="d-flex flex-column gap-3 mb-4">
            @forelse($answers as $answer)
                <div class="dg-card p-4 {{ $answer->is_accepted ? 'accepted-answer-card' : '' }}">
                    @if($answer->is_accepted)
                        <div class="accepted-badge-ribbon">
                            <i class="bi bi-check-circle-fill"></i> Accepted Solution
                        </div>
                    @endif

                    <div class="d-flex gap-3">
                        <!-- Answer Left Like Widget -->
                        <div class="vote-widget vote-control flex-shrink-0">
                            @auth
                                @php
                                    $userLikedAnswer = $answer->votes->where('user_id', auth()->id())->count() > 0;
                                @endphp
                                <button class="btn-like {{ $userLikedAnswer ? 'liked' : '' }}"
                                    data-type="answer" data-id="{{ $answer->id }}"
                                    title="{{ $userLikedAnswer ? 'Unlike' : 'Like this answer' }}">
                                    <i class="bi {{ $userLikedAnswer ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                    <span class="like-count">{{ $answer->vote_score }}</span>
                                </button>

                                <!-- Accepted Answer (Question owner only) -->
                                @if(auth()->id() === $question->user_id)
                                    <form action="{{ route('answers.accept', $answer) }}" method="POST" class="mt-2">
                                        @csrf
                                        <button type="submit" class="btn btn-sm rounded-circle {{ $answer->is_accepted ? 'btn-success' : 'btn-outline-secondary' }}"
                                            title="{{ $answer->is_accepted ? 'Unmark solution' : 'Mark as accepted solution' }}">
                                            <i class="bi bi-check-lg fs-5"></i>
                                        </button>
                                    </form>
                                @endif
                            @else
                                <a href="{{ route('login') }}" class="btn-like" title="Sign in to like">
                                    <i class="bi bi-heart"></i>
                                    <span class="like-count">{{ $answer->vote_score }}</span>
                                </a>
                            @endauth
                        </div>

                        <!-- Answer Body & Meta -->
                        <div class="flex-grow-1 min-w-0">
                            <div class="content-body render-markdown mb-4" data-raw="{{ $answer->answer }}">{{ $answer->answer }}</div>

                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top">
                                <div class="d-flex align-items-center gap-2 small">
                                    @can('update', $answer)
                                        <a href="{{ route('answers.edit', $answer) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </a>
                                    @endcan
                                    @can('delete', $answer)
                                        <form action="{{ route('answers.destroy', $answer) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this answer?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    @endcan
                                    @auth
                                        @if(auth()->id() !== $answer->user_id)
                                            <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none p-0 ms-2" data-bs-toggle="modal" data-bs-target="#reportModal" data-type="answer" data-id="{{ $answer->id }}">
                                                <i class="bi bi-flag"></i> Report
                                            </button>
                                        @endif
                                    @endauth
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <span class="small text-muted">Answered {{ $answer->created_at->diffForHumans() }} by</span>
                                    <a href="{{ route('users.show', $answer->user->id) }}" class="d-flex align-items-center gap-1 text-dark text-decoration-none fw-semibold small">
                                        @if($answer->user->profile_image && $answer->user->profile_image !== 'default_profile.png')
                                            <img src="{{ asset('profiles/' . $answer->user->profile_image) }}" class="rounded-circle object-fit-cover" width="24" height="24" alt="avatar">
                                        @else
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 0.65rem;">
                                                {{ strtoupper(substr($answer->user->user_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        {{ $answer->user->user_name }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="dg-card p-4 text-center">
                    <p class="text-secondary mb-0">No answers yet. Be the first to provide a technical solution!</p>
                </div>
            @endforelse
        </div>

        <!-- Post an Answer Form -->
        <div class="dg-card p-4">
            <h5 class="fw-bold text-dark mb-3">Your Answer</h5>
            @auth
                @php $userAlreadyAnswered = $question->answers->where('user_id', auth()->id())->count() > 0; @endphp

                {{-- AI Generate Answer — always shown to all logged-in users --}}
                <div class="ai-assistant-card mb-3" id="ai-generate-box">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white"><i class="bi bi-stars me-1"></i> AI Draft</span>
                            <span class="small text-secondary">Let AI draft a starting point — review before submitting</span>
                        </div>
                        <button type="button" id="btn-generate-answer"
                            class="btn btn-sm btn-outline-primary rounded-pill px-3"
                            data-question-id="{{ $question->id }}"
                            data-url="{{ route('ai.generate-answer', $question->id) }}">
                            <span id="generate-spinner" class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                            <i class="bi bi-magic me-1"></i> Generate AI Draft
                        </button>
                    </div>
                    <div id="ai-generate-disclaimer" class="small text-muted mt-2 d-none">
                        <i class="bi bi-info-circle me-1"></i> AI-generated draft. Always review and edit before submitting.
                    </div>
                </div>
                <form action="{{ route('answers.store') }}" method="POST" id="post-answer-form">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->id }}">

                    <div class="mb-3">
                        {{-- Hidden textarea holds HTML for submission --}}
                        <textarea name="answer" id="answer-textarea" class="d-none @error('answer') is-invalid @enderror" required>{{ old('answer') }}</textarea>
                        {{-- Quill mounts here --}}
                        <div id="quill-answer-form"></div>
                        @error('answer')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                            <i class="bi bi-send-check me-1"></i> Post Your Answer
                        </button>
                        <span class="small text-secondary">
                            <i class="bi bi-stars text-warning me-1"></i> Contributing earns <strong>+10 reputation</strong>.
                        </span>
                    </div>
                </form>
            @else
                <div class="p-4 bg-light text-center rounded border">
                    <p class="text-secondary mb-3">You must be signed in to post an answer and earn reputation.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-4">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary rounded-pill px-4">Sign Up</a>
                    </div>
                </div>
            @endauth
        </div>
    </div>

    <!-- Right Sidebar (Related Questions + Author Card) -->
    <div class="col-lg-4">
        <!-- Question Stats Card -->
        <div class="dg-card p-3 mb-4">
            <h6 class="fw-bold mb-3 text-dark">Discussion Overview</h6>
            <div class="d-flex flex-column gap-2 small text-secondary">
                <div class="d-flex justify-content-between">
                    <span>Category:</span>
                    <strong class="text-dark">{{ $question->category->name }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Likes:</span>
                    <strong class="text-dark"><i class="bi bi-heart-fill text-danger"></i> {{ $question->vote_score }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Answers:</span>
                    <strong class="text-dark">{{ $question->answer_count }} answers</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Saved by:</span>
                    <strong class="text-dark">{{ $question->bookmark_count }} users</strong>
                </div>
            </div>
        </div>

        <!-- Semantic Related Questions Widget -->
        <div class="dg-card p-3 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-diagram-3 text-primary me-1"></i> Related Discussions
                </h6>
                <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.65rem;">Vector Match</span>
            </div>

            @if($similarQuestions->isNotEmpty())
                <div class="d-flex flex-column gap-3">
                    @foreach($similarQuestions as $similar)
                        <div class="border-bottom pb-2">
                            <a href="{{ route('questions.show', [$similar->id, $similar->slug]) }}" class="small fw-semibold text-dark text-decoration-none d-block mb-1">
                                {{ $similar->title }}
                            </a>
                            <div class="d-flex align-items-center gap-2 small text-muted" style="font-size: 0.75rem;">
                                <span><i class="bi bi-heart-fill text-danger"></i> {{ $similar->vote_score }}</span>
                                <span>·</span>
                                <span><i class="bi bi-chat-left-text"></i> {{ $similar->answer_count }}</span>
                                @if(isset($similar->similarity_score))
                                    <span class="badge bg-light text-secondary border ms-auto">{{ $similar->similarity_score }}% similarity</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="small text-muted mb-0">No topically related discussions found yet.</p>
            @endif
        </div>
    </div>
</div>

<!-- Report Content Modal -->
@auth
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('reports.store') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <input type="hidden" name="reportable_type" id="modal-report-type" value="question">
            <input type="hidden" name="reportable_id" id="modal-report-id" value="{{ $question->id }}">

            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-flag-fill text-danger me-2"></i> Report Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-secondary mb-3">Help us keep the community clean and constructive by reporting guidelines violations.</p>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Reason for Report</label>
                    <select name="reason" class="form-select form-control-dg" required>
                        <option value="spam">Spam or Advertising</option>
                        <option value="offensive">Offensive Language / Toxicity</option>
                        <option value="duplicate">Exact Duplicate</option>
                        <option value="misleading">Misleading or False Information</option>
                        <option value="other">Other Violation</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Additional Details (Optional)</label>
                    <textarea name="details" rows="3" class="form-control form-control-dg" placeholder="Explain why this content violates community standards..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm px-3">Submit Report</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
    // ── Quill answer editor (inline post-answer form) ─────────
    @auth
    const quillAnswerForm = new Quill('#quill-answer-form', {
        theme: 'snow',
        placeholder: 'Write your technical explanation, steps to solve, or code snippets...',
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

    // Pre-fill if validation failed and returned old value
    @if(old('answer'))
        quillAnswerForm.clipboard.dangerouslyPasteHTML({!! json_encode(old('answer')) !!});
    @endif

    // Sync Quill HTML to hidden textarea on submit
    const postAnswerForm = document.getElementById('post-answer-form');
    if (postAnswerForm) {
        postAnswerForm.addEventListener('submit', function() {
            const html = quillAnswerForm.root.innerHTML;
            document.getElementById('answer-textarea').value = (html === '<p><br></p>') ? '' : html;
        });
    }

    // ── AI Draft → inject into Quill editor ──────────────────
    const btnGenerateAnswer = document.getElementById('btn-generate-answer');
    if (btnGenerateAnswer) {
        btnGenerateAnswer.addEventListener('click', async function() {
            const spinner    = document.getElementById('generate-spinner');
            const icon       = this.querySelector('.bi-magic');
            const disclaimer = document.getElementById('ai-generate-disclaimer');
            const btn        = this;

            // Loading state
            btn.disabled = true;
            spinner && spinner.classList.remove('d-none');
            icon    && icon.classList.add('d-none');

            // 100s client-side timeout (backend allows 120s)
            const controller   = new AbortController();
            const clientTimeout = setTimeout(() => controller.abort(), 100000);

            try {
                const res = await fetch(this.dataset.url, {
                    method: 'POST',
                    signal: controller.signal,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                clearTimeout(clientTimeout);

                // Parse JSON safely — a 500 can return HTML not JSON
                let data = {};
                try { data = await res.json(); } catch(_) {}

                const content = data.draft || data.answer || null;
                if (content) {
                    const html = content
                        .split(/\n\n+/)
                        .map(p => `<p>${p.replace(/\n/g, '<br>')}</p>`)
                        .join('');
                    quillAnswerForm.clipboard.dangerouslyPasteHTML(html);
                    disclaimer && disclaimer.classList.remove('d-none');
                    document.getElementById('quill-answer-form')
                        .scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } catch(e) { /* silent fail — Quill stays empty, user can type manually */ }
            finally {
                clearTimeout(clientTimeout);
                spinner && spinner.classList.add('d-none');
                icon    && icon.classList.remove('d-none');
                btn.disabled = false;
            }
        });
    }
    @endauth

    // ── AI Summary button ─────────────────────────────────────
    const btnSummary = document.getElementById('btn-generate-ai-summary');
    if (btnSummary) {
        btnSummary.addEventListener('click', async function() {
            const spinner   = document.getElementById('ai-summary-spinner');
            const icon      = document.getElementById('ai-summary-icon');
            const container = document.getElementById('ai-summary-content');
            const btn       = this;

            // Loading state — identical pattern to generate answer
            btn.disabled = true;
            spinner && spinner.classList.remove('d-none');
            icon    && icon.classList.add('d-none');
            container.innerHTML = `
                <div class="d-flex align-items-center gap-2 text-secondary small py-2">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <span>Generating synthesis… may take a moment on first run.</span>
                </div>`;

            // 100s client-side timeout
            const controller    = new AbortController();
            const clientTimeout = setTimeout(() => controller.abort(), 100000);

            try {
                const qid = this.dataset.questionId;
                const res = await fetch(`/api/ai/summarize/${qid}?force=1`, {
                    signal: controller.signal,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                clearTimeout(clientTimeout);

                // Parse JSON safely — a 500 can return HTML not JSON
                let data = {};
                try { data = await res.json(); } catch(_) {}

                if (data.success && data.summary) {
                    container.innerHTML = `
                        <div class="p-3 bg-light rounded border text-dark mb-2">${data.summary}</div>
                        <span class="text-muted" style="font-size:0.7rem;">
                            <i class="bi bi-clock me-1"></i> Synthesized just now
                            ${data.cached ? '(cached)' : ''}
                        </span>`;
                    btn.innerHTML = `
                        <span id="ai-summary-spinner" class="spinner-border spinner-border-sm d-none me-1" role="status"></span>
                        <i class="bi bi-arrow-repeat me-1" id="ai-summary-icon"></i> Refresh Synthesis`;
                } else {
                    container.innerHTML = `
                        <p class="text-secondary small mb-0 fst-italic">
                            <i class="bi bi-lightbulb text-warning me-1"></i>
                            ${data.message || 'Could not generate summary. Please try again.'}
                        </p>`;
                }
            } catch(e) {
                clearTimeout(clientTimeout);
                // AbortError = timed out on client side
                // Any other error = network/server issue
                // Either way — show a soft retry message, never a hard "unavailable"
                container.innerHTML = `
                    <p class="text-secondary small mb-0 fst-italic">
                        <i class="bi bi-arrow-repeat text-primary me-1"></i>
                        Taking longer than expected. Please click the button again to retry.
                    </p>`;
            } finally {
                btn.disabled = false;
                document.getElementById('ai-summary-spinner')?.classList.add('d-none');
                document.getElementById('ai-summary-icon')?.classList.remove('d-none');
            }
        });
    }

    // ── Report modal hydration ────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        const reportModal = document.getElementById('reportModal');
        if (reportModal) {
            reportModal.addEventListener('show.bs.modal', (e) => {
                const btn = e.relatedTarget;
                if (btn) {
                    document.getElementById('modal-report-type').value = btn.dataset.type || 'question';
                    document.getElementById('modal-report-id').value = btn.dataset.id || '{{ $question->id }}';
                }
            });
        }
    });
</script>
@endpush
@endauth
@endsection
