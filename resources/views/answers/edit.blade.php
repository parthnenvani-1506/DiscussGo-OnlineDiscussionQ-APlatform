@extends('layouts.app')

@section('title', 'Edit Answer - DiscussHub')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
.ql-toolbar.ql-snow {
    border: 1px solid var(--border); border-bottom: none;
    border-radius: var(--radius-md) var(--radius-md) 0 0;
    background: var(--bg-secondary); padding: 0.5rem 0.75rem;
}
.ql-container.ql-snow {
    border: 1px solid var(--border);
    border-radius: 0 0 var(--radius-md) var(--radius-md);
    background: var(--bg-primary); font-size: 0.9375rem; min-height: 220px;
}
.ql-container.ql-snow:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
.ql-editor { min-height: 220px; color: var(--text-primary); line-height: 1.7; padding: 1rem; }
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
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">Edit Your Answer</h2>
            <p class="text-secondary small">
                Answering on: <a href="{{ route('questions.show', [$answer->question->id, $answer->question->slug]) }}" class="text-primary fw-semibold">{{ $answer->question->title }}</a>
            </p>
        </div>

        <form action="{{ route('answers.update', $answer) }}" method="POST" id="edit-answer-form">
            @csrf
            @method('PUT')

            <div class="dg-card p-4 mb-4">
                <label class="form-label fw-bold text-dark">Your Technical Answer</label>
                {{-- Hidden textarea holds HTML for form submission --}}
                <textarea name="answer" id="answer" class="d-none @error('answer') is-invalid @enderror" required></textarea>
                {{-- Quill editor mounts here --}}
                <div id="quill-answer"></div>
                @error('answer')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex align-items-center gap-3">
                <button type="submit" class="btn-primary-dg px-4 py-2">
                    <i class="bi bi-check-circle me-1"></i> Update Answer
                </button>
                <a href="{{ route('questions.show', [$answer->question->id, $answer->question->slug]) }}" class="btn-secondary-dg px-3 py-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
const quillAnswer = new Quill('#quill-answer', {
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

// Pre-fill existing answer content
@if($answer->answer)
    quillAnswer.clipboard.dangerouslyPasteHTML({!! json_encode(old('answer', $answer->answer)) !!});
@endif

// Sync Quill HTML to hidden textarea on submit
document.getElementById('edit-answer-form').addEventListener('submit', function() {
    const html = quillAnswer.root.innerHTML;
    // Don't submit empty editor (just the <p><br></p> placeholder)
    document.getElementById('answer').value = (html === '<p><br></p>') ? '' : html;
});
</script>
@endpush
