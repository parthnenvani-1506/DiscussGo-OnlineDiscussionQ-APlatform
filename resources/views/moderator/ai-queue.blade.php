@extends('layouts.moderator')
@section('title', 'AI Flagged Content - DiscussHub Moderator')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">AI Flagged Content</h2>
        <p class="text-secondary small mb-0">Content automatically flagged by the AI moderation system. Review each item and take action or dismiss.</p>
    </div>
</div>

<!-- Flagged Questions -->
<div class="dg-card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-question-circle text-warning me-2"></i> Flagged Questions</h5>
        <span class="badge bg-danger rounded-pill">{{ $flaggedQuestions->total() }}</span>
    </div>

    @forelse($flaggedQuestions as $question)
        <div class="dg-card p-3 mb-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning border border-warning">Question</span>
                    <span class="badge bg-light text-secondary border">{{ $question->category->name ?? 'Unknown' }}</span>
                    <span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-robot me-1"></i>AI Flagged</span>
                </div>
                <div class="small text-muted">
                    By <strong>{{ $question->user->user_name }}</strong> · {{ $question->created_at->diffForHumans() }}
                </div>
            </div>

            <h6 class="fw-bold text-dark mb-1">{{ $question->title }}</h6>
            <p class="text-secondary small mb-3">{{ Str::limit(strip_tags($question->description), 200) }}</p>

            <div class="d-flex align-items-center gap-2 pt-2 border-top flex-wrap">
                <button type="button" class="btn btn-sm btn-danger"
                    data-bs-toggle="modal" data-bs-target="#removeQuestionModal"
                    data-id="{{ $question->id }}"
                    data-title="{{ Str::limit($question->title, 50) }}">
                    <i class="bi bi-trash me-1"></i> Remove Question
                </button>

                <form action="{{ route('moderator.dismiss-flag', ['type' => 'question', 'id' => $question->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Dismiss Flag
                    </button>
                </form>

                <a href="{{ route('questions.show', [$question->id, $question->slug]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right me-1"></i> View on Site
                </a>
            </div>
        </div>
    @empty
        <div class="text-center py-4 text-muted small">
            <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
            No flagged questions in queue.
        </div>
    @endforelse
    <div class="mt-3">{{ $flaggedQuestions->links() }}</div>
</div>

<!-- Flagged Answers -->
<div class="dg-card p-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-chat-dots text-danger me-2"></i> Flagged Answers</h5>
        <span class="badge bg-danger rounded-pill">{{ $flaggedAnswers->total() }}</span>
    </div>

    @forelse($flaggedAnswers as $answer)
        <div class="dg-card p-3 mb-3 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger-subtle text-danger border border-danger">Answer</span>
                    <span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-robot me-1"></i>AI Flagged</span>
                </div>
                <div class="small text-muted">
                    By <strong>{{ $answer->user->user_name }}</strong> · {{ $answer->created_at->diffForHumans() }}
                </div>
            </div>

            @if($answer->question)
                <div class="small text-muted mb-2">
                    <i class="bi bi-question-circle me-1"></i>
                    On: <a href="{{ route('questions.show', [$answer->question->id, $answer->question->slug]) }}" target="_blank" class="text-primary text-decoration-none fw-semibold">{{ Str::limit($answer->question->title, 70) }}</a>
                </div>
            @endif

            <div class="p-2 rounded bg-light border small text-secondary mb-3">
                {{ Str::limit(strip_tags($answer->answer), 300) }}
            </div>

            <div class="d-flex align-items-center gap-2 pt-2 border-top flex-wrap">
                <button type="button" class="btn btn-sm btn-danger"
                    data-bs-toggle="modal" data-bs-target="#removeAnswerModal"
                    data-id="{{ $answer->id }}">
                    <i class="bi bi-trash me-1"></i> Remove Answer
                </button>

                <form action="{{ route('moderator.dismiss-flag', ['type' => 'answer', 'id' => $answer->id]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Dismiss Flag
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="text-center py-4 text-muted small">
            <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
            No flagged answers in queue.
        </div>
    @endforelse
    <div class="mt-3">{{ $flaggedAnswers->links() }}</div>
</div>

<!-- Remove Question Modal -->
<div class="modal fade" id="removeQuestionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="removeQuestionForm" class="modal-content border-0 shadow">
            @csrf
            <input type="hidden" name="ai_flag_source" value="1">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-trash text-danger me-2"></i> Remove Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-secondary mb-3">The question author will be notified with your reason. This action is logged.</p>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Reason for Removal <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control form-control-dg" rows="3" required placeholder="e.g. Contains offensive language violating community guidelines..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">
                    <i class="bi bi-trash me-1"></i> Remove Question
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Remove Answer Modal -->
<div class="modal fade" id="removeAnswerModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="removeAnswerForm" class="modal-content border-0 shadow">
            @csrf
            <input type="hidden" name="ai_flag_source" value="1">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-trash text-danger me-2"></i> Remove Answer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-secondary mb-3">The answer author will be notified with your reason. This action is logged.</p>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Reason for Removal <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control form-control-dg" rows="3" required placeholder="e.g. Spam content / violates community standards..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">
                    <i class="bi bi-trash me-1"></i> Remove Answer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const qModal = document.getElementById('removeQuestionModal');
    if (qModal) {
        qModal.addEventListener('show.bs.modal', e => {
            const btn = e.relatedTarget;
            document.getElementById('removeQuestionForm').action = `/moderator/remove-question/${btn.dataset.id}`;
        });
    }
    const aModal = document.getElementById('removeAnswerModal');
    if (aModal) {
        aModal.addEventListener('show.bs.modal', e => {
            const btn = e.relatedTarget;
            document.getElementById('removeAnswerForm').action = `/moderator/remove-answer/${btn.dataset.id}`;
        });
    }
});
</script>
@endpush
@endsection
