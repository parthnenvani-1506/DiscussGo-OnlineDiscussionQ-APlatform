@extends('layouts.admin')

@section('title', 'Edit Category - DiscussHub Admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Edit Category</h2>
                <p class="text-secondary small mb-0">{{ $category->name }}</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="dg-card p-4">
            <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">
                        Category Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" id="name"
                        class="form-control form-control-dg @error('name') is-invalid @enderror"
                        value="{{ old('name', $category->name) }}" required>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Slug --}}
                <div class="mb-3">
                    <label for="slug" class="form-label small fw-semibold">Slug</label>
                    <input type="text" name="slug" id="slug"
                        class="form-control form-control-dg @error('slug') is-invalid @enderror"
                        value="{{ old('slug', $category->slug) }}">
                    <div class="form-text small text-muted">Leave blank to auto-generate from name.</div>
                    @error('slug')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label for="description" class="form-label small fw-semibold">Short Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="form-control form-control-dg">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold">
                        <i class="bi bi-check-lg me-1"></i> Update Category
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Danger zone --}}
        @if($category->questions_count === 0)
            <div class="dg-card p-4 mt-4 border border-danger-subtle">
                <h6 class="fw-bold text-danger mb-1"><i class="bi bi-exclamation-triangle me-1"></i> Danger Zone</h6>
                <p class="small text-secondary mb-3">Deleting a category is permanent and cannot be undone.</p>
                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                    onsubmit="return confirm('Permanently delete \'{{ $category->name }}\'?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4">
                        <i class="bi bi-trash me-1"></i> Delete This Category
                    </button>
                </form>
            </div>
        @else
            <div class="dg-card p-4 mt-4 border border-warning-subtle">
                <p class="small text-secondary mb-0">
                    <i class="bi bi-info-circle text-warning me-1"></i>
                    This category has <strong>{{ $category->questions_count }}</strong> discussions and cannot be deleted.
                    Reassign all questions first.
                </p>
            </div>
        @endif

    </div>
</div>
@endsection
