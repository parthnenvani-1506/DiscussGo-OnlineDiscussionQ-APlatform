@extends('layouts.app')

@section('title', 'Saved Bookmarks - DiscussHub')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1"><i class="bi bi-bookmark-fill text-primary me-2"></i> Saved Discussions</h2>
                <p class="text-secondary small mb-0">{{ $bookmarks->total() }} discussions saved to your personal reading list</p>
            </div>
            <a href="{{ route('questions.index') }}" class="btn-secondary-dg py-1 px-3 small">
                <i class="bi bi-compass"></i> Explore More
            </a>
        </div>

        <div class="dg-card overflow-hidden">
            @forelse($bookmarks as $bookmark)
                @php $question = $bookmark->question; @endphp
                @if($question)
                    <div class="question-card">
                        <div class="vote-box">
                            <span class="vote-count">{{ $question->vote_score }}</span>
                            <span class="vote-label">likes</span>
                        </div>

                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-primary-subtle text-primary border border-primary">
                                    {{ $question->category->name }}
                                </span>
                                @if($question->is_answered)
                                    <span class="badge bg-success small"><i class="bi bi-check-circle-fill"></i> Solved</span>
                                @endif
                            </div>

                            <h5 class="fw-bold mb-2">
                                <a href="{{ route('questions.show', [$question->id, $question->slug]) }}" class="text-decoration-none text-dark">
                                    {{ $question->title }}
                                </a>
                            </h5>

                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($question->tags as $tag)
                                        <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge text-decoration-none">
                                            #{{ $tag->name }}
                                        </a>
                                    @endforeach
                                </div>

                                <div class="d-flex align-items-center gap-3">
                                    <span class="small text-muted">Saved {{ $bookmark->created_at->diffForHumans() }}</span>
                                    <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-bookmark-toggle" data-question-id="{{ $question->id }}" title="Remove bookmark">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-bookmark text-muted display-4"></i>
                    <h5 class="mt-3 text-secondary">No saved discussions yet</h5>
                    <p class="small text-muted mb-3">Click the bookmark icon on any question to save it for quick reference.</p>
                    <a href="{{ route('questions.index') }}" class="btn-primary-dg">
                        <i class="bi bi-search"></i> Browse Discussions
                    </a>
                </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $bookmarks->links() }}
        </div>
    </div>
</div>
@endsection
