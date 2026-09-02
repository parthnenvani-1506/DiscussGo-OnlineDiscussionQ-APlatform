@extends('layouts.app')

@section('title', 'Categories - DiscussHub')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold text-dark mb-1">Explore by Category</h2>
    <p class="text-secondary small">Browse discussions categorized by topic and domain.</p>
</div>

<div class="row g-4">
    @foreach($categories as $category)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('categories.show', $category->slug) }}" class="text-decoration-none d-block h-100">
                <div class="dg-card p-4 h-100 d-flex flex-column justify-content-between">
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
    @endforeach
</div>
@endsection
