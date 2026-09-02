@extends('layouts.admin')

@section('title', 'Add Category - DiscussHub Admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Create Category</h2>
                <p class="text-secondary small mb-0">Add a new discussion classification</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="dg-card p-4">
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Name --}}
                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">
                        Category Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" id="name"
                        class="form-control form-control-dg @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required
                        placeholder="e.g. Artificial Intelligence">
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Slug --}}
                <div class="mb-3">
                    <label for="slug" class="form-label small fw-semibold">Slug</label>
                    <input type="text" name="slug" id="slug"
                        class="form-control form-control-dg @error('slug') is-invalid @enderror"
                        value="{{ old('slug') }}"
                        placeholder="e.g. artificial-intelligence">
                    <div class="form-text small text-muted">Leave blank to auto-generate from name.</div>
                    @error('slug')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label for="description" class="form-label small fw-semibold">Short Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="form-control form-control-dg"
                        placeholder="Brief summary of topics covered in this category...">{{ old('description') }}</textarea>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold">
                        <i class="bi bi-check-lg me-1"></i> Save Category
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
