@extends('layouts.app')

@section('title', 'Topics & Categories - DiscussHub')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1"><i class="bi bi-grid-fill text-primary me-2"></i> Explore Topics &amp; Categories</h2>
        <p class="text-secondary small mb-0">Browse and search curated discussion domains to find specialized expertise.</p>
    </div>

    <!-- Search Topics Realtime Input -->
    <form action="{{ route('categories.index') }}" method="GET" style="max-width: 340px;" class="w-100 dg-realtime-search-form">
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
            <input type="text" 
                   name="q" 
                   class="form-control form-control-dg border-start-0 ps-0 dg-realtime-input" 
                   placeholder="Search topics & categories..." 
                   value="{{ $search }}" 
                   autocomplete="off">
        </div>
    </form>
</div>

<!-- Realtime Categories Results Container -->
<div id="dg-realtime-results-container" class="dg-realtime-results-wrapper">
    <div class="row g-4">
        @forelse($categories as $category)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('categories.show', $category->slug) }}" class="text-decoration-none d-block h-100">
                    <div class="dg-card p-4 h-100 d-flex flex-column justify-content-between interactive">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-primary-subtle text-primary border border-primary p-2 fs-5 rounded">
                                    <i class="bi bi-folder-fill"></i>
                                </span>
                                <span class="badge bg-light text-secondary border">{{ $category->questions_count }} discussions</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">{{ $category->name }}</h5>
                            <p class="text-secondary small mb-0">{{ $category->description ?? 'Discussions, best practices, and guides for ' . $category->name . '.' }}</p>
                        </div>
                        <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between text-primary small fw-semibold">
                            <span>Browse Topics</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-folder-x text-muted display-4"></i>
                <h5 class="mt-3 text-secondary">No topics found matching "{{ $search }}"</h5>
                <p class="small text-muted mb-3">Try checking for typos or search for a broader topic.</p>
                <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">View All Topics</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $categories->links() }}
    </div>
</div>
@endsection
