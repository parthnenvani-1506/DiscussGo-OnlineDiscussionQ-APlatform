@extends('layouts.app')

@section('title', 'Contact Support & Feedback - DiscussHub')

@push('styles')
<style>
/* ==========================================================================
   Contact Page - Senior Architecture & Premium Minimalist Design System
   ========================================================================== */
.contact-page-wrapper {
    padding: 1rem 0 3rem;
}

.contact-container {
    max-width: 640px;
    margin: 0 auto;
}

/* Header Section */
.contact-header {
    text-align: center;
    margin-bottom: 2rem;
}

.contact-icon-badge {
    width: 52px;
    height: 52px;
    border-radius: var(--radius-full);
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(79, 70, 229, 0.12));
    border: 1px solid rgba(37, 99, 235, 0.2);
    color: var(--primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 16px rgba(37, 99, 235, 0.1);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.contact-card:hover ~ .contact-header .contact-icon-badge,
.contact-header:hover .contact-icon-badge {
    transform: translateY(-2px) scale(1.05);
    border-color: rgba(37, 99, 235, 0.35);
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.18);
}

.contact-title {
    font-size: clamp(1.75rem, 3vw, 2.15rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    line-height: 1.25;
}

.contact-subtitle {
    color: var(--text-secondary);
    font-size: 0.95rem;
    line-height: 1.55;
    margin: 0 auto;
    max-width: 440px;
}

/* Card Container */
.contact-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease;
}

.contact-card:hover {
    box-shadow: var(--shadow-elevated);
    border-color: var(--border-hover);
}

/* Subtle brand gradient top accent bar */
.contact-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--brand-primary, #2563eb), var(--brand-secondary, #4f46e5), var(--brand-accent-orange, #ff7a00));
}

.contact-card-inner {
    padding: 2.5rem 2.25rem;
}

@media (max-width: 576px) {
    .contact-card-inner {
        padding: 1.75rem 1.25rem;
    }
    .contact-title {
        font-size: 1.65rem;
    }
}

/* Form Controls & Floating Focus States */
.contact-form-group {
    margin-bottom: 1.25rem;
}

.contact-label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    color: var(--text-primary);
    margin-bottom: 0.45rem;
}

.contact-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.contact-input-icon {
    position: absolute;
    left: 1rem;
    color: var(--text-muted);
    font-size: 0.95rem;
    pointer-events: none;
    transition: color 0.2s ease, transform 0.2s ease;
    z-index: 3;
}

.contact-input {
    width: 100%;
    height: 46px;
    padding: 0.65rem 1rem 0.65rem 2.65rem;
    font-size: 0.9rem;
    font-family: inherit;
    color: var(--text-primary);
    background-color: var(--bg-primary);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.contact-input::placeholder {
    color: var(--text-muted);
    opacity: 0.85;
    font-size: 0.875rem;
}

.contact-input:focus {
    outline: none;
    border-color: var(--brand-primary, #2563eb);
    background-color: var(--bg-primary);
    box-shadow: 0 0 0 3.5px var(--primary-light);
}

.contact-input-wrapper:focus-within .contact-input-icon {
    color: var(--brand-primary, #2563eb);
    transform: scale(1.1);
}

/* Textarea styling */
.contact-textarea-wrapper {
    position: relative;
}

.contact-textarea-icon {
    position: absolute;
    top: 0.85rem;
    left: 1rem;
    color: var(--text-muted);
    font-size: 0.95rem;
    pointer-events: none;
    transition: color 0.2s ease, transform 0.2s ease;
    z-index: 3;
}

.contact-textarea {
    width: 100%;
    min-height: 140px;
    padding: 0.75rem 1rem 0.75rem 2.65rem;
    font-size: 0.9rem;
    font-family: inherit;
    color: var(--text-primary);
    background-color: var(--bg-primary);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    line-height: 1.6;
    resize: vertical;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.contact-textarea::placeholder {
    color: var(--text-muted);
    opacity: 0.85;
    font-size: 0.875rem;
}

.contact-textarea:focus {
    outline: none;
    border-color: var(--brand-primary, #2563eb);
    background-color: var(--bg-primary);
    box-shadow: 0 0 0 3.5px var(--primary-light);
}

.contact-textarea-wrapper:focus-within .contact-textarea-icon {
    color: var(--brand-primary, #2563eb);
    transform: scale(1.1);
}

/* Validation States */
.contact-input.is-invalid,
.contact-textarea.is-invalid {
    border-color: var(--danger) !important;
    box-shadow: 0 0 0 3px var(--danger-light) !important;
}

.contact-input.is-invalid ~ .contact-input-icon,
.contact-textarea.is-invalid ~ .contact-textarea-icon {
    color: var(--danger) !important;
}

.contact-error-msg {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    color: var(--danger);
    font-size: 0.785rem;
    font-weight: 500;
    margin-top: 0.35rem;
}

/* Submit Button - 3D Gradient CTA */
.contact-submit-btn {
    width: 100%;
    height: 48px;
    border: none;
    border-radius: var(--radius-full);
    background: linear-gradient(135deg, var(--brand-primary, #2563eb) 0%, var(--brand-secondary, #4f46e5) 100%);
    color: #ffffff;
    font-weight: 600;
    font-size: 0.9375rem;
    letter-spacing: -0.01em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28), inset 0 1px 1px rgba(255, 255, 255, 0.3);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.contact-submit-btn::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -75%;
    width: 50%;
    height: 200%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.35), transparent);
    transform: rotate(25deg);
    transition: left 0.75s ease;
}

.contact-submit-btn:hover {
    transform: translateY(-1.5px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.38), inset 0 1px 1px rgba(255, 255, 255, 0.45);
    color: #ffffff;
}

.contact-submit-btn:hover::before {
    left: 130%;
}

.contact-submit-btn:hover .contact-btn-icon {
    transform: translateX(3px) rotate(8deg);
}

.contact-submit-btn:active {
    transform: translateY(1px);
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
}

.contact-btn-icon {
    font-size: 0.95rem;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Dark Mode Harmonization */
[data-bs-theme="dark"] .contact-card,
[data-theme="dark"] .contact-card {
    background: var(--bg-card);
    border-color: var(--border);
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
}

[data-bs-theme="dark"] .contact-input,
[data-theme="dark"] .contact-input,
[data-bs-theme="dark"] .contact-textarea,
[data-theme="dark"] .contact-textarea {
    background-color: #0b0f19;
    border-color: var(--border);
    color: var(--text-primary);
}

[data-bs-theme="dark"] .contact-input:focus,
[data-theme="dark"] .contact-input:focus,
[data-bs-theme="dark"] .contact-textarea:focus,
[data-theme="dark"] .contact-textarea:focus {
    background-color: #0b0f19;
    border-color: var(--brand-primary, #3b82f6);
    box-shadow: 0 0 0 3.5px rgba(59, 130, 246, 0.18);
}

[data-bs-theme="dark"] .contact-icon-badge,
[data-theme="dark"] .contact-icon-badge {
    background: rgba(37, 99, 235, 0.18);
    border-color: rgba(59, 130, 246, 0.3);
    color: #60a5fa;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}
</style>
@endpush

@section('content')
<div class="contact-page-wrapper">
    <div class="contact-container">
        <!-- Header -->
        <div class="contact-header">
            <div class="contact-icon-badge">
                <i class="bi bi-chat-heart-fill"></i>
            </div>
            <h1 class="contact-title">Get in Touch</h1>
            <p class="contact-subtitle">Have a suggestion, question, or need assistance? Send us a message.</p>
        </div>

        <!-- Form Card -->
        <div class="contact-card">
            <div class="contact-card-inner">
                <form action="{{ route('contact.store') }}" method="POST">
                    @csrf

                    <!-- Name & Email Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="contact-label">Your Name</label>
                            <div class="contact-input-wrapper">
                                <i class="bi bi-person contact-input-icon"></i>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       class="contact-input @error('name') is-invalid @enderror"
                                       value="{{ old('name', auth()->user()?->user_name) }}"
                                       required
                                       placeholder="John Doe"
                                       autocomplete="name">
                            </div>
                            @error('name')
                                <div class="contact-error-msg">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="contact-label">Your Email</label>
                            <div class="contact-input-wrapper">
                                <i class="bi bi-envelope contact-input-icon"></i>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       class="contact-input @error('email') is-invalid @enderror"
                                       value="{{ old('email', auth()->user()?->email) }}"
                                       required
                                       placeholder="name@example.com"
                                       autocomplete="email">
                            </div>
                            @error('email')
                                <div class="contact-error-msg">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Subject Field -->
                    <div class="contact-form-group mb-3">
                        <label for="subject" class="contact-label">Subject</label>
                        <div class="contact-input-wrapper">
                            <i class="bi bi-chat-square-text contact-input-icon"></i>
                            <input type="text"
                                   name="subject"
                                   id="subject"
                                   class="contact-input @error('subject') is-invalid @enderror"
                                   value="{{ old('subject') }}"
                                   required
                                   placeholder="e.g. Feedback on AI Summarization feature">
                        </div>
                        @error('subject')
                            <div class="contact-error-msg">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Message Field -->
                    <div class="contact-form-group mb-4">
                        <label for="message" class="contact-label">Message</label>
                        <div class="contact-textarea-wrapper">
                            <i class="bi bi-pencil-square contact-textarea-icon"></i>
                            <textarea name="message"
                                      id="message"
                                      rows="5"
                                      class="contact-textarea @error('message') is-invalid @enderror"
                                      required
                                      placeholder="Write your inquiry or feedback in detail...">{{ old('message') }}</textarea>
                        </div>
                        @error('message')
                            <div class="contact-error-msg">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>

                    <!-- Submit Action Button -->
                    <button type="submit" class="contact-submit-btn">
                        <i class="bi bi-send-fill contact-btn-icon"></i>
                        <span>Send Message</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
