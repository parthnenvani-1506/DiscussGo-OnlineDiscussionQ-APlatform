@extends('layouts.app')

@section('title', 'Tags - DiscussHub')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Tags Taxonomy</h2>
        <p class="text-secondary small mb-0">Browse and search tags to find specialized technical questions.</p>
    </div>

    <!-- Search Tag Input -->
    <form action="{{ route('tags.index') }}" method="GET" style="max-width: 320px;" class="w-100">
        <div class="input-group">
            <input type="text" name="q" class="form-control form-control-dg" placeholder="Filter by tag name..." value="{{ $search }}">
            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
        </div>
    </form>
</div>

<div class="row g-3">
    @forelse($tags as $tag)
        <div class="col-md-4 col-sm-6">
            <div class="dg-card p-3 h-100 d-flex flex-column justify-content-between interactive">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge text-decoration-none fw-bold">
                            #{{ $tag->name }}
                        </a>
                        <span class="badge bg-light text-secondary border small">{{ $tag->usage_count }} questions</span>
                    </div>
                    <p class="text-secondary small mb-0">{{ $tag->description ?? 'All discussions and solutions tagged with ' . $tag->name }}</p>
                </div>

                <div class="mt-3 pt-2 border-top">
                    <a href="{{ route('tags.show', $tag->slug) }}" class="small text-primary text-decoration-none fw-semibold">
                        View Questions <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-tags text-muted display-4"></i>
            <h5 class="mt-3 text-secondary">No tags found matching "{{ $search }}"</h5>
        </div>
    @endforelse
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $tags->links() }}
</div>
@endsection
