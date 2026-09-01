@extends('layouts.app')

@section('title', 'Contact Support & Feedback - DiscussHub')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4 text-center">
            <h2 class="fw-bold text-dark mb-1">Get in Touch</h2>
            <p class="text-secondary small">Have a suggestion, question, or need assistance? Send us a message.</p>
        </div>

        <div class="dg-card p-4 p-md-5">
            <form action="{{ route('contact.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label small fw-semibold">Your Name</label>
                        <input type="text" name="name" id="name" class="form-control form-control-dg @error('name') is-invalid @enderror" value="{{ old('name', auth()->user()?->user_name) }}" required placeholder="John Doe">
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label small fw-semibold">Your Email</label>
                        <input type="email" name="email" id="email" class="form-control form-control-dg @error('email') is-invalid @enderror" value="{{ old('email', auth()->user()?->email) }}" required placeholder="name@example.com">
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="subject" class="form-label small fw-semibold">Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control form-control-dg @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required placeholder="e.g. Feedback on AI Summarization feature">
                    @error('subject')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="message" class="form-label small fw-semibold">Message</label>
                    <textarea name="message" id="message" rows="6" class="form-control form-control-dg @error('message') is-invalid @enderror" required placeholder="Write your inquiry or feedback in detail...">{{ old('message') }}</textarea>
                    @error('message')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-primary-dg w-100 py-2 justify-content-center">
                    <i class="bi bi-send-fill me-1"></i> Send Message
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
