@extends('layouts.admin')

@section('title', 'Read Message - DiscussHub Admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Message from {{ $contact->name }}</h2>
                <p class="text-secondary small mb-0">{{ $contact->email }} · {{ $contact->created_at->format('M d, Y H:i') }}</p>
            </div>
            <a href="{{ route('admin.contact.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="dg-card p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3">Subject: {{ $contact->subject }}</h5>
            <div class="p-3 rounded bg-light border mb-4 lh-base text-dark">
                {!! nl2br(e($contact->message)) !!}
            </div>

            <div class="d-flex align-items-center justify-content-between border-top pt-3">
                <a href="mailto:{{ $contact->email }}?subject=Re: {{ urlencode($contact->subject) }}" class="btn btn-primary btn-sm rounded-pill px-4">
                    <i class="bi bi-reply-fill me-1"></i> Reply via Email
                </a>

                <form action="{{ route('admin.contact.destroy', $contact) }}" method="POST" onsubmit="return confirm('Delete this message?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
