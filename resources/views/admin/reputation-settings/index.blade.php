@extends('layouts.admin')
@section('title', 'Reputation Point Settings - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">
            <i class="bi bi-stars text-warning me-2"></i> Reputation Point Settings
        </h2>
        <p class="text-secondary small mb-0">
            One value controls both the earn and the matching deduction.
            <strong>Only future actions are affected — past records are never changed.</strong>
        </p>
    </div>
    <button type="submit" form="rep-settings-form" class="btn btn-primary rounded-pill px-4 fw-semibold">
        <i class="bi bi-check-circle me-1"></i> Save Changes
    </button>
</div>

<form id="rep-settings-form" action="{{ route('admin.reputation-settings.update') }}" method="POST">
    @csrf

    <div class="dg-card p-0 mb-4 overflow-hidden">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-4 py-3" style="width: 28%;">Action</th>
                    <th class="py-3" style="width: 35%;">Earn (+)</th>
                    <th class="py-3" style="width: 25%;">Lose (−)</th>
                    <th class="py-3 text-center" style="width: 12%;">Points</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pairs as $pair)
                <tr>
                    {{-- Action name --}}
                    <td class="px-4 fw-semibold small">{{ $pair['label'] }}</td>

                    {{-- Earn description --}}
                    <td>
                        <span class="badge bg-success-subtle text-success border border-success me-1">+</span>
                        <span class="text-secondary small">{{ $pair['earn_label'] }}</span>
                    </td>

                    {{-- Lose description --}}
                    <td>
                        <span class="badge bg-danger-subtle text-danger border border-danger me-1">−</span>
                        <span class="text-secondary small">{{ $pair['lose_label'] }}</span>
                    </td>

                    {{-- Single points input --}}
                    <td class="text-center">
                        <input
                            type="number"
                            name="points[{{ $pair['earn_key'] }}]"
                            value="{{ $pair['points'] }}"
                            min="0"
                            max="999"
                            class="form-control form-control-sm text-center fw-bold @error('points.'.$pair['earn_key']) is-invalid @enderror"
                            style="width: 75px; margin: 0 auto;"
                            required>
                        @error('points.'.$pair['earn_key'])
                            <div class="text-danger mt-1" style="font-size: 0.72rem;">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Info notice --}}
    <div class="dg-card p-3 mb-4 border-start border-primary border-3">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-info-circle-fill text-primary mt-1 flex-shrink-0"></i>
            <p class="small text-secondary mb-0">
                <strong class="text-dark">How it works:</strong>
                Setting <strong>Ask / Delete a question</strong> to <code>5</code> means asking earns
                <span class="text-success fw-bold">+5</span> and deleting deducts
                <span class="text-danger fw-bold">−5</span> automatically.
                All existing reputation records and users' current scores are <strong>never recalculated</strong>.
            </p>
        </div>
    </div>

    <div class="d-flex gap-3">
        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-semibold">
            <i class="bi bi-check-circle me-1"></i> Save Changes
        </button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2">
            Cancel
        </a>
    </div>
</form>
@endsection
