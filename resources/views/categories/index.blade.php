@extends('layouts.app')

@section('title', 'Categories - DiscussHub')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold text-dark mb-1">Explore by Category</h2>
    <p class="text-secondary small">Browse technical discussions categorized by language, stack, and domain.</p>
</div>

<div class="row g-4">
    @foreach($categories as $category)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('categories.show', $category->slug) }}" class="text-decoration-none d-block h-100">
                @if($category->image)
                    {{-- Image-based card --}}
                    <div class="category-image-card h-100" style="background-image: url('{{ asset('categories/' . $category->image) }}');">
                        <div class="category-image-overlay">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-25">
                                    {{ $category->questions_count }} discussions
                                </span>
                            </div>
                            <h5 class="fw-bold text-white mb-1">{{ $category->name }}</h5>
                            <p class="text-white-50 small mb-0" style="font-size:0.8rem;">{{ Str::limit($category->description, 80) }}</p>
                        </div>
                    </div>
                @else
                    {{-- Icon/color-based card (fallback) --}}
                    <div class="dg-card p-4 h-100 interactive d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge p-2 fs-5 rounded" style="background: {{ $category->color }}15; color: {{ $category->color }};">
                                    <i class="{{ $category->icon }}"></i>
                                </span>
                                <span class="badge bg-light text-secondary border">{{ $category->questions_count }} discussions</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">{{ $category->name }}</h5>
                            <p class="text-secondary small mb-0">{{ $category->description ?? 'Discussions, best practices, and troubleshooting for ' . $category->name . '.' }}</p>
                        </div>
                        <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between text-primary small fw-semibold">
                            <span>Browse Topics</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </div>
                @endif
            </a>
        </div>
    @endforeach
</div>
@endsection
