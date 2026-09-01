@extends('layouts.admin')

@section('title', 'Edit Category - DiscussHub Admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">Edit Category: {{ $category->name }}</h2>
            <p class="text-secondary small">Modify category details, icon, and colors</p>
        </div>

        <div class="dg-card p-4">
            <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">Category Name</label>
                    <input type="text" name="name" id="name" class="form-control form-control-dg @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="slug" class="form-label small fw-semibold">Slug</label>
                    <input type="text" name="slug" id="slug" class="form-control form-control-dg @error('slug') is-invalid @enderror" value="{{ old('slug', $category->slug) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Category Image</label>
                    @if($category->image)
                        <div class="mb-2">
                            <img src="{{ asset('categories/' . $category->image) }}" alt="Current image" class="rounded" style="height:80px;object-fit:cover;">
                            <div class="text-muted small mt-1">Current image. Upload new to replace.</div>
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control form-control-dg @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text small">JPG, PNG, WEBP — Max 2MB.</div>
                    @error('image')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label for="icon" class="form-label small fw-semibold">Bootstrap Icon Class</label>
                        <input type="text" name="icon" id="icon" class="form-control form-control-dg" value="{{ old('icon', $category->icon) }}">
                    </div>
                    <div class="col-sm-6">
                        <label for="color" class="form-label small fw-semibold">Brand Color (Hex)</label>
                        <input type="text" name="color" id="color" class="form-control form-control-dg" value="{{ old('color', $category->color) }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label small fw-semibold">Short Description</label>
                    <textarea name="description" id="description" rows="3" class="form-control form-control-dg">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-check-lg me-1"></i> Update Category
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
